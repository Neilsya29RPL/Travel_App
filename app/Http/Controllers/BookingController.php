<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Destination;
use App\Models\Accommodation;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $holiday = DB::table('users_holiday')
            ->where('user_id', $userId)
            ->orderByDesc('holiday_id')
            ->first();

        $recommendation = null;
        if ($holiday) {
            $recommendation = DB::table('ai_recommendations')
                ->where('user_id', $userId)
                ->where('holiday_id', $holiday->holiday_id)
                ->orderByDesc('created_at')
                ->first();
        }

        $lastBooking = null;
        if ($holiday) {
            $lastBooking = DB::table('bookings')
                ->where('user_id', $userId)
                ->where('holiday_id', $holiday->holiday_id)
                ->orderByDesc('created_at')
                ->first();
        }

        // Tambahkan daftar semua booking untuk holiday saat ini
        $bookings = collect();
        if ($holiday) {
            $bookings = DB::table('bookings')
                ->where('user_id', $userId)
                ->where('holiday_id', $holiday->holiday_id)
                ->leftJoin('destinations', 'bookings.destination_id', '=', 'destinations.destination_id')
                ->leftJoin('accommodations', 'bookings.accommodation_id', '=', 'accommodations.accommodation_id')
                ->orderByDesc('bookings.created_at')
                ->select('bookings.*', 'destinations.name as destination_name', 'accommodations.name as accommodation_name')
                ->get();
        }

        return view('booking.index', compact('holiday', 'recommendation', 'lastBooking', 'bookings'));
    }

    public function create(Request $request)
    {
        $userId = Auth::id();
        $holiday = DB::table('users_holiday')
            ->where('user_id', $userId)
            ->orderByDesc('holiday_id')
            ->first();

        if (!$holiday) {
            return redirect()->route('preferences.index')->with('error', 'Mohon isi preferensi terlebih dahulu.');
        }

        $data = $request->validate([
            'destination_index' => ['required', 'integer', 'min:0'],
            'accommodation_index' => ['required', 'integer', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'passengers' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:200'],
        ]);

        $recRow = DB::table('ai_recommendations')
            ->where('user_id', $userId)
            ->where('holiday_id', $holiday->holiday_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$recRow) {
            return redirect()->route('recommendations.index')->with('error', 'Mohon jalankan rekomendasi AI terlebih dahulu.');
        }

        $rec = json_decode($recRow->response_json, true);
        $dest = $rec['destinations'][$data['destination_index']] ?? null;
        $acc = $rec['accommodations'][$data['accommodation_index']] ?? null;

        // Hitung jumlah malam secara inklusif: selisih hari + 1
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $nights = max(1, $start->diffInDays($end) + 1);

        if (!$dest || !$acc) {
            return redirect()->route('booking.index')->with('error', 'Pilihan destinasi/akomodasi tidak valid.');
        }

        // Persist destination & accommodation, then use their IDs in booking
        $destination = Destination::firstOrCreate(
            ['name' => $dest['name'] ?? 'Unknown'],
            [
                'description' => isset($dest['activities']) ? implode(', ', (array) ($dest['activities'] ?? [])) : null,
                'average_cost' => $dest['est_budget'] ?? null,
            ]
        );

        $accommodation = Accommodation::firstOrCreate(
            [
                'destination_id' => $destination->destination_id,
                'name' => $acc['name'] ?? 'Unknown',
            ],
            [
                'type' => $acc['type'] ?? null,
                'price_per_night' => $acc['price_per_night'] ?? null,
                'rating' => $acc['rating'] ?? null,
                'provider' => $acc['provider'] ?? 'local',
                'external_id' => $acc['external_id'] ?? null,
            ]
        );

        $baseTransport = 300; // ribuan
        $activities = 200;    // ribuan
        $priceNight = $accommodation->price_per_night ?? ($acc['price_per_night'] ?? 80);
        $accommodationCost = $priceNight * $nights;
        $total = $baseTransport + $activities + $accommodationCost;

        DB::table('bookings')->insert([
            'user_id' => $userId,
            'holiday_id' => $holiday->holiday_id,
            'destination_id' => $destination->destination_id,
            'accommodation_id' => $accommodation->accommodation_id,
            'total_amount' => $total,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'booking_date' => now(),
            'created_at' => now(),
            'currency' => 'Rupiah',
            'updated_at' => now(),
        ]);

        return redirect()->route('booking.index')->with('status', 'Booking dibuat. Total sementara: Rp ' . number_format($total * 1000, 0, ',', '.'));
    }

    public function pay(Request $request)
    {
        $userId = Auth::id();
        $holiday = DB::table('users_holiday')
            ->where('user_id', $userId)
            ->orderByDesc('holiday_id')
            ->first();

        $data = $request->validate([
            'method' => ['required', 'string'],
            'booking_id' => ['nullable'],
        ]);

        // Pilih booking berdasarkan booking_id jika dikirim, jika tidak ambil terakhir
        $booking = null;
        if (!empty($data['booking_id'])) {
            $booking = DB::table('bookings')
                ->where('user_id', $userId)
                ->where('booking_id', $data['booking_id'])
                ->first();
        }

        if (!$booking) {
            $booking = DB::table('bookings')
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->first();
        }

        if (!$booking) {
            return redirect()->route('booking.index')->with('error', 'Tidak ada booking yang ditemukan.');
        }

        $transactionId = Str::uuid()->toString();

        DB::table('payments')->insert([
            'booking_id' => $booking->booking_id,
            'amount' => $booking->total_amount,
            'method' => $data['method'],
            'provider' => 'MockPay',
            'status' => 'success',
            'transaction_id' => $transactionId,
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update status booking spesifik
        $q = DB::table('bookings')->where('user_id', $userId);
        if (!empty($data['booking_id'])) {
            $q = $q->where('booking_id', $data['booking_id']);
        } else {
            $q = $q->where('created_at', $booking->created_at);
        }

        $q->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('status', 'Pembayaran berhasil. Transaksi: ' . $transactionId);
    }

    public function list()
    {
        $userId = Auth::id();

        $bookings = DB::table('bookings')
            ->where('user_id', $userId)
            ->leftJoin('destinations', 'bookings.destination_id', '=', 'destinations.destination_id')
            ->leftJoin('accommodations', 'bookings.accommodation_id', '=', 'accommodations.accommodation_id')
            ->orderByDesc('bookings.created_at')
            ->select('bookings.*', 'destinations.name as destination_name', 'accommodations.name as accommodation_name')
            ->get();

        return view('booking.list', compact('bookings'));
    }
}