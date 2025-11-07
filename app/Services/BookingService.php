<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingService
{
    private $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    public function getBookingData($userId)
    {
        $holiday = DB::table('users_holiday')
            ->where('user_id', $userId)
            ->orderByDesc('holiday_id')
            ->first();

        $recommendation = $holiday ? DB::table('ai_recommendations')
            ->where('user_id', $userId)
            ->where('holiday_id', $holiday->holiday_id)
            ->orderByDesc('created_at')
            ->first() : null;

        $lastBooking = $holiday ? DB::table('bookings')
            ->where('user_id', $userId)
            ->where('holiday_id', $holiday->holiday_id)
            ->orderByDesc('created_at')
            ->first() : null;

        $bookings = $holiday ? DB::table('bookings')
            ->where('user_id', $userId)
            ->where('holiday_id', $holiday->holiday_id)
            ->leftJoin('destinations', 'bookings.destination_id', '=', 'destinations.destination_id')
            ->leftJoin('accommodations', 'bookings.accommodation_id', '=', 'accommodations.accommodation_id')
            ->orderByDesc('bookings.created_at')
            ->select('bookings.*', 'destinations.name as destination_name', 'accommodations.name as accommodation_name')
            ->get() : collect();

        return compact('holiday', 'recommendation', 'lastBooking', 'bookings');
    }

    public function createBooking($userId, $data)
    {
        $holiday = DB::table('users_holiday')
            ->where('user_id', $userId)
            ->orderByDesc('holiday_id')
            ->first();

        if (!$holiday) {
            return ['error' => true, 'redirect' => 'preferences.index', 'message' => 'Mohon isi preferensi terlebih dahulu.'];
        }

        $recRow = DB::table('ai_recommendations')
            ->where('user_id', $userId)
            ->where('holiday_id', $holiday->holiday_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$recRow) {
            return ['error' => true, 'redirect' => 'recommendations.index', 'message' => 'Mohon jalankan rekomendasi AI terlebih dahulu.'];
        }

        $rec = json_decode($recRow->response_json, true);
        $dest = $rec['destinations'][$data['destination_index']] ?? null;
        $acc = $rec['accommodations'][$data['accommodation_index']] ?? null;

        if (!$dest || !$acc) {
            return ['error' => true, 'redirect' => 'booking.index', 'message' => 'Pilihan destinasi/akomodasi tidak valid.'];
        }

        $nights = $this->budgetService->calculateNights($data['start_date'], $data['end_date']);
        $total = $this->budgetService->calculateTotal($dest, $acc, $nights);

        DB::transaction(function () use ($userId, $holiday, $dest, $acc, $total) {
            $destination = DB::table('destinations')->insertGetId([
                'name' => $dest['name'],
                'description' => implode(', ', $dest['activities'] ?? []),
                'average_cost' => $dest['est_budget'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $accommodation = DB::table('accommodations')->insertGetId([
                'destination_id' => $destination,
                'name' => $acc['name'],
                'type' => $acc['type'],
                'price_per_night' => $acc['price_per_night'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('bookings')->insert([
                'user_id' => $userId,
                'holiday_id' => $holiday->holiday_id,
                'destination_id' => $destination,
                'accommodation_id' => $accommodation,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'booking_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return ['error' => false, 'message' => 'Booking berhasil dibuat. Total sementara: Rp ' . number_format($total * 1000, 0, ',', '.')];
    }

    public function processPayment($userId, $data)
    {
        // ...existing payment logic...
    }

    public function listBookings($userId)
    {
        return DB::table('bookings')
            ->where('user_id', $userId)
            ->leftJoin('destinations', 'bookings.destination_id', '=', 'destinations.destination_id')
            ->leftJoin('accommodations', 'bookings.accommodation_id', '=', 'accommodations.accommodation_id')
            ->orderByDesc('bookings.created_at')
            ->select('bookings.*', 'destinations.name as destination_name', 'accommodations.name as accommodation_name')
            ->get();
    }

    public function getBookingDetails($userId, $bookingId)
    {
        $booking = DB::table('bookings')
            ->where('user_id', $userId)
            ->where('booking_id', $bookingId)
            ->leftJoin('destinations', 'bookings.destination_id', '=', 'destinations.destination_id')
            ->leftJoin('accommodations', 'bookings.accommodation_id', '=', 'accommodations.accommodation_id')
            ->select('bookings.*', 'destinations.name as destination_name', 'accommodations.name as accommodation_name')
            ->first();

        $payments = DB::table('payments')
            ->where('booking_id', $bookingId)
            ->orderByDesc('created_at')
            ->get();

        return compact('booking', 'payments');
    }

    public function getCheckoutData($userId, $bookingId)
    {
        $booking = DB::table('bookings')
            ->where('user_id', $userId)
            ->where('booking_id', $bookingId)
            ->leftJoin('destinations', 'bookings.destination_id', '=', 'destinations.destination_id')
            ->leftJoin('accommodations', 'bookings.accommodation_id', '=', 'accommodations.accommodation_id')
            ->select('bookings.*', 'destinations.name as destination_name', 'accommodations.name as accommodation_name')
            ->first();

        return compact('booking');
    }
}
