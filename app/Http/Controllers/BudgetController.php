<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $holiday = DB::table('users_holiday')->where('user_id', $userId)->orderByDesc('holiday_id')->first();
        $recommendation = null;
        $estimate = null;
        $breakdown = [];
        $accOptions = [];
        $accIndex = 0;
        $destIndex = 0;
        // Gunakan satu sumber nilai durasi hari untuk tampilan dan perhitungan
        $days = 3;
        if ($holiday) {
            $days = (int) ($holiday->duration_days ?? 3);
        }

        if ($holiday) {
            $recommendation = DB::table('ai_recommendations')
                ->where('user_id', $userId)
                ->where('holiday_id', $holiday->holiday_id)
                ->orderByDesc('created_at')
                ->first();

            if ($recommendation) {
                $rec = json_decode($recommendation->response_json, true);
                // Transport dihapus dari perhitungan total pada halaman budget
                $baseTransport = 0;
                $activities = 200;    // default, akan diganti oleh pilihan destinasi bila tersedia

                // Siapkan opsi akomodasi untuk dropdown di view
                $accOptions = array_map(function ($a) {
                    return $a['name'] ?? 'Unknown';
                }, (array) ($rec['accommodations'] ?? []));

                // Baca pilihan akomodasi/destinasi dari parameter atau session (fallback ke booking)
                $accCount = count($rec['accommodations'] ?? []);
                $destCount = count($rec['destinations'] ?? []);

                // Prioritas 1: parameter request
                if ($request->has('acc')) {
                    $reqAcc = (int) $request->input('acc');
                    if ($reqAcc >= 0 && $reqAcc < $accCount) { $accIndex = $reqAcc; }
                    session(['selected_acc' => $accIndex]);
                } elseif (session()->has('selected_acc')) {
                    // Prioritas 2: session
                    $sessAcc = (int) session('selected_acc');
                    if ($sessAcc >= 0 && $sessAcc < $accCount) { $accIndex = $sessAcc; }
                } else {
                    $accIndex = 0;
                }

                if ($request->has('dest')) {
                    $reqDest = (int) $request->input('dest');
                    if ($reqDest >= 0 && $reqDest < $destCount) { $destIndex = $reqDest; }
                    session(['selected_dest' => $destIndex]);
                } elseif (session()->has('selected_dest')) {
                    $sessDest = (int) session('selected_dest');
                    if ($sessDest >= 0 && $sessDest < $destCount) { $destIndex = $sessDest; }
                } else {
                    $destIndex = 0;
                }

                // Fallback: jika tidak ada parameter dan tidak ada session, gunakan pilihan terakhir dari booking (jika ada)
                if ((!$request->has('acc') && !session()->has('selected_acc')) || (!$request->has('dest') && !session()->has('selected_dest'))) {
                    $lastBooking = DB::table('bookings')
                        ->where('user_id', $userId)
                        ->where('holiday_id', $holiday->holiday_id)
                        ->orderByDesc('created_at')
                        ->first();
                    if ($lastBooking) {
                        // Kembalikan akomodasi yang sama berdasarkan nama
                        $bookedAccName = DB::table('accommodations')
                            ->where('accommodation_id', $lastBooking->accommodation_id)
                            ->value('name');
                        if ($bookedAccName && $accCount > 0) {
                            $accNames = array_map(fn($a) => $a['name'] ?? null, (array) ($rec['accommodations'] ?? []));
                            $matchIdx = array_search($bookedAccName, $accNames, true);
                            if ($matchIdx !== false && is_int($matchIdx)) {
                                $accIndex = $matchIdx;
                            }
                        }

                        // Kembalikan destinasi yang sama berdasarkan nama
                        $bookedDestName = DB::table('destinations')
                            ->where('destination_id', $lastBooking->destination_id)
                            ->value('name');
                        if ($bookedDestName && $destCount > 0) {
                            $destNames = array_map(fn($d) => $d['name'] ?? null, (array) ($rec['destinations'] ?? []));
                            $matchDestIdx = array_search($bookedDestName, $destNames, true);
                            if ($matchDestIdx !== false && is_int($matchDestIdx)) {
                                $destIndex = $matchDestIdx;
                            }
                        }
                    }
                }

                // Gunakan estimasi budget dari destinasi yang dipilih sebagai komponen aktivitas,
                // dikalikan dengan jumlah hari agar sesuai durasi
                $destBudget = (float) ($rec['destinations'][$destIndex]['est_budget'] ?? 0);
                if ($destBudget > 0) {
                    $activities = $destBudget * max($days, 1); // ribuan
                }

                $accPrice = (float) ($rec['accommodations'][$accIndex]['price_per_night'] ?? ($rec['accommodations'][0]['price_per_night'] ?? 0));

                // Estimasi tanpa transport untuk halaman budget
                $estimate = $activities + ($accPrice * $days);
                $breakdown = [
                    'accommodation' => $accPrice * $days,
                    'activities' => $activities,
                ];

                // Persist pilihan destinasi & akomodasi ke holiday_choices
                $destName = $rec['destinations'][$destIndex]['name'] ?? null;
                $destEst = $rec['destinations'][$destIndex]['est_budget'] ?? null;
                $accName = $rec['accommodations'][$accIndex]['name'] ?? null;
                $accNight = $rec['accommodations'][$accIndex]['price_per_night'] ?? null;

                DB::table('holiday_choices')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'holiday_id' => $holiday->holiday_id,
                    ],
                    [
                        'destination_name' => $destName,
                        'dest_est_budget' => $destEst,
                        'accommodation_name' => $accName,
                        'acc_price_per_night' => $accNight,
                        'duration_days' => $days,
                        'total_estimate' => $estimate,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            if (!$estimate) {
                // Fallback jika belum ada rekomendasi
                $accFb = $days * 50;
                $actFb = 150 * max($days, 1);
                $estimate = $accFb + $actFb; // tanpa transport
                $breakdown = [
                    'accommodation' => $accFb,
                    'activities' => $actFb,
                ];
            }
        }

        return view('budget.index', compact('holiday', 'estimate', 'breakdown', 'recommendation', 'accOptions', 'accIndex', 'destIndex', 'days'));
    }
}
