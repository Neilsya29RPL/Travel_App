<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class RecommendationController extends Controller
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

        return view('recommendations.index', compact('holiday', 'recommendation'));
    }

    public function run(Request $request)
    {
        $userId = Auth::id();
        $holiday = DB::table('users_holiday')
            ->where('user_id', $userId)
            ->orderByDesc('holiday_id')
            ->first();

        if (!$holiday) {
            return redirect()->route('preferences.index')
                ->with('error', 'Mohon isi preferensi terlebih dahulu.');
        }

        $payload = [
            'category_id'   => $holiday->category_id,
            'country_id'    => $holiday->country_id,
            'mood_id'       => $holiday->mood_id,
            'duration_days' => $holiday->duration_days ?? null,
            'budget_min'    => $holiday->budget_min,
            'budget_max'    => $holiday->budget_max,
        ];

        $endpoint = config('services.ai.endpoint');
        $apiKey   = config('services.ai.api_key');
        $timeout  = (int) config('services.ai.timeout', 10);

        $result = null;
        $source = 'local';

        try {
            if ($endpoint && $apiKey) {
                $resp = Http::timeout($timeout)
                    ->accept('application/json')
                    ->asJson()
                    ->withToken($apiKey)
                    ->post($endpoint, $payload);

                if ($resp->successful()) {
                    $apiResult = $resp->json();
                    $result = $this->normalizeResult((array) $apiResult) ?: (array) $apiResult;
                    $source = 'api';
                }
            }
        } catch (\Throwable $e) {
            // fallback di bawah
        }

        if (!$result) {
            $days = (int) ($holiday->duration_days ?? 3);
            $baseTransport = 300; // ribuan
            $activities    = 200; // ribuan

            $destinations = [
                [
                    'name' => 'Bali, Indonesia',
                    'country' => 'Indonesia',
                    'mood' => 'Relax & Beach',
                    'activities' => ['Pantai Kuta', 'Uluwatu Temple', 'Spa'],
                    'est_budget' => $baseTransport + $activities,
                ],
                [
                    'name' => 'Kyoto, Japan',
                    'country' => 'Japan',
                    'mood' => 'Culture & Nature',
                    'activities' => ['Fushimi Inari', 'Arashiyama', 'Tea Ceremony'],
                    'est_budget' => $baseTransport + $activities,
                ],
            ];

            $accommodations = [
                ['name' => 'Bali Seaside Resort', 'type' => 'Hotel', 'price_per_night' => 150],
                ['name' => 'Kyoto Ryokan', 'type' => 'Ryokan', 'price_per_night' => 100],
            ];

            $defaultIndex = 1; // agar total ~ 800k untuk 3 malam
            $priceNight = $accommodations[$defaultIndex]['price_per_night'];
            $totalEstimate = $baseTransport + $activities + ($priceNight * $days);

            $result = [
                'destinations' => $destinations,
                'accommodations' => $accommodations,
                'total_estimate' => $totalEstimate,
                'currency' => 'Rupiah',
            ];
        }

        DB::table('ai_recommendations')->insert([
            'user_id' => $userId,
            'holiday_id' => $holiday->holiday_id,
            'payload' => json_encode($payload),
            'response_json' => json_encode($result),
            'source' => $source,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('recommendations.index')
            ->with('status', 'Rekomendasi AI berhasil dibuat.');
    }

    private function normalizeResult(array $data): ?array
    {
        $destinations = [];
        foreach (($data['destinations'] ?? []) as $d) {
            $destinations[] = [
                'name' => $d['name'] ?? ($d['title'] ?? null),
                'country' => $d['country'] ?? null,
                'mood' => $d['mood'] ?? null,
                'activities' => $d['activities'] ?? [],
                'est_budget' => $d['est_budget'] ?? null,
            ];
        }

        $accommodations = [];
        foreach (($data['accommodations'] ?? []) as $acc) {
            $accommodations[] = [
                'name' => $acc['name'] ?? null,
                'type' => $acc['type'] ?? null,
                'price_per_night' => $acc['price_per_night'] ?? ($acc['nightly'] ?? null),
            ];
        }

        $total = $data['total_estimate'] ?? null;
        $currency = $data['currency'] ?? 'Rupiah';

        if (!$destinations && !$accommodations && !$total) {
            return null;
        }

        return [
            'destinations' => $destinations,
            'accommodations' => $accommodations,
            'total_estimate' => $total,
            'currency' => $currency,
        ];
    }
}