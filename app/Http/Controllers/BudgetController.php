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

        if ($holiday) {
            $recommendation = DB::table('ai_recommendations')
                ->where('user_id', $userId)
                ->where('holiday_id', $holiday->holiday_id)
                ->orderByDesc('created_at')
                ->first();

            if ($recommendation) {
                $rec = json_decode($recommendation->response_json, true);

                $days = (int) ($holiday->duration_days ?? 3);
                $baseTransport = 300; // ribuan
                $activities = 200;    // ribuan

                // Siapkan opsi akomodasi untuk dropdown di view
                $accOptions = array_map(function ($a) {
                    return $a['name'] ?? 'Unknown';
                }, (array) ($rec['accommodations'] ?? []));

                // Baca pilihan akomodasi dari query string (default: index 0)
                $requestedIndex = (int) $request->input('acc', 0);
                $accCount = count($rec['accommodations'] ?? []);
                if ($requestedIndex < 0 || $requestedIndex >= $accCount) {
                    $accIndex = 0;
                } else {
                    $accIndex = $requestedIndex;
                }

                $accPrice = (float) ($rec['accommodations'][$accIndex]['price_per_night'] ?? ($rec['accommodations'][0]['price_per_night'] ?? 0));

                // Estimasi dan breakdown konsisten dengan rumus booking
                $estimate = $baseTransport + $activities + ($accPrice * $days);
                $breakdown = [
                    'accommodation' => $accPrice * $days,
                    'activities' => $activities,
                    'transport' => $baseTransport,
                ];
            }

            if (!$estimate) {
                // Fallback jika belum ada rekomendasi
                $avgBudget = (($holiday->budget_min ?? 0) + ($holiday->budget_max ?? 0)) / 2;
                $days = (int) ($holiday->duration_days ?? 3);
                $estimate = max($avgBudget, 500) + ($days * 50);
                $breakdown = [
                    'accommodation' => $days * 50,
                    'activities' => 150,
                    'transport' => 200,
                ];
            }
        }

        return view('budget.index', compact('holiday', 'estimate', 'breakdown', 'recommendation', 'accOptions', 'accIndex'));
    }
}