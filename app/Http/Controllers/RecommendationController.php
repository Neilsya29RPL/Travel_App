<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class RecommendationController extends Controller
{
    // public function index()
    // {
    //     $userId = Auth::id();
    //     $holiday = DB::table('users_holiday')
    //         ->where('user_id', $userId)
    //         ->orderByDesc('holiday_id')
    //         ->first();

    //     $recommendation = null;
    //     if ($holiday) {
    //         $recommendation = DB::table('ai_recommendations')
    //             ->where('user_id', $userId)
    //             ->where('holiday_id', $holiday->holiday_id)
    //             ->orderByDesc('created_at')
    //             ->first();
    //     }

    //     return view('recommendations.index', compact('holiday', 'recommendation'));
    // }

    public function run(Request $request)
    {
        // existing full generation left as-is
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
            // fallback
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
                // Bali
                ['name' => 'Bali Seaside Resort', 'type' => 'Hotel', 'price_per_night' => 150],
                ['name' => 'Bali Ubud Villa', 'type' => 'Villa', 'price_per_night' => 180],
                // Kyoto
                ['name' => 'Kyoto Ryokan', 'type' => 'Ryokan', 'price_per_night' => 100],
                ['name' => 'Kyoto City Hotel', 'type' => 'Hotel', 'price_per_night' => 120],
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

        return redirect()->route('preferences.index')
            ->with('status', 'Rekomendasi AI berhasil dibuat.');
    }

    public function accommodations(Request $request)
    {
        $userId = Auth::id();
        // Validasi kondisional: GET boleh tanpa durasi, POST harus isi durasi
        $rules = [
            'recommendation_id' => ['required', 'integer'],
            'selected_destinations' => ['nullable', 'array'],
            'selected_destinations.*' => ['integer'],
        ];
        if ($request->isMethod('post')) {
            $rules['duration_days'] = ['required', 'integer', 'min:1', 'max:60'];
        } else {
            $rules['duration_days'] = ['nullable', 'integer', 'min:1', 'max:60'];
        }

        $data = $request->validate($rules, [
            'duration_days.required' => 'Durasi menginap wajib diisi.',
            'duration_days.integer' => 'Durasi harus berupa angka.',
            'duration_days.min' => 'Durasi minimal 1 malam.',
            'duration_days.max' => 'Durasi maksimal 60 malam.',
        ]);

        // Ambil rekomendasi berdasarkan ID yang dikirim dari form.
        // Beberapa setup DB memakai nama PK berbeda, jadi siapkan fallback.
        try {
            $recRow = DB::table('ai_recommendations')
                ->where('user_id', $userId)
                ->where('id', $data['recommendation_id'])
                ->first();
        } catch (\Throwable $e) {
            // Fallback: coba kolom 'recommendation_id'
            try {
                $recRow = DB::table('ai_recommendations')
                    ->where('user_id', $userId)
                    ->where('recommendation_id', $data['recommendation_id'])
                    ->first();
            } catch (\Throwable $e2) {
                // Fallback terakhir: ambil rekomendasi terakhir milik user
                $recRow = DB::table('ai_recommendations')
                    ->where('user_id', $userId)
                    ->orderByDesc('created_at')
                    ->first();
            }
        }

        if (!$recRow) {
            return redirect()->route('preferences.index')->with('error', 'Rekomendasi tidak ditemukan.');
        }

        // Ambil preferensi user (kategori/negara/mood) untuk ditampilkan di UI
        $holidayRow = DB::table('users_holiday')->where('holiday_id', $recRow->holiday_id)->first();
        $prefCategoryName = null; $prefCountryName = null; $prefMoodName = null;
        if ($holidayRow) {
            $cat = DB::table('master_category')->where('category_id', $holidayRow->category_id)->first();
            $country = DB::table('master_country')->where('country_id', $holidayRow->country_id)->first();
            $mood = DB::table('master_mood')->where('mood_id', $holidayRow->mood_id)->first();
            $prefCategoryName = $cat->category_name ?? null;
            $prefCountryName = $country->country_name ?? null;
            $prefMoodName = $mood->mood_name ?? null;
        }

        $rec = json_decode($recRow->response_json ?? '{}', true);
        $destinations = $rec['destinations'] ?? [];

        $selectedIdx = $data['selected_destinations'] ?? [];
        if (!$selectedIdx || !is_array($selectedIdx)) {
            // Jika tidak ada yang dipilih, gunakan semua destinasi
            $selectedIdx = array_keys($destinations);
        }

        $selectedDestinations = [];
        foreach ($selectedIdx as $i) {
            if (isset($destinations[$i])) {
                $selectedDestinations[] = $destinations[$i];
            }
        }

        $nights = (int) ($data['duration_days'] ?? 3);
        if ($nights < 1) { $nights = 1; }
        if ($nights > 60) { $nights = 60; }

        // Persist durasi ke users_holiday, dan pilihan destinasi ke holiday_choices (tanpa akomodasi dulu)
        if ($request->isMethod('post')) {
            // Update durasi di tabel preferensi
            DB::table('users_holiday')
                ->where('holiday_id', $recRow->holiday_id)
                ->update([
                    'duration_days' => $nights,
                    'updated_at' => now(),
                ]);

            // Simpan satu destinasi terpilih (pakai yang pertama) ke holiday_choices
            $destChoice = $selectedDestinations[0] ?? null;
            $destName = $destChoice['name'] ?? null;
            $destBudget = isset($destChoice['est_budget']) ? (float) $destChoice['est_budget'] : null;
            $baseTransport = 300; // ribuan
            $totalEstimate = $baseTransport + (($destBudget ?? 0) * $nights);

            DB::table('holiday_choices')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'holiday_id' => $recRow->holiday_id,
                ],
                [
                    'destination_name' => $destName,
                    'dest_est_budget' => $destBudget,
                    'duration_days' => $nights,
                    'total_estimate' => $totalEstimate,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Hanya tampilkan rekomendasi akomodasi setelah user klik "Find Recommendation" (POST)
        $accommodations = [];
        if ($request->isMethod('post')) {
            // Ambil dari hasil rekomendasi sebelumnya; fallback ke hardcode bila kosong
            $rawAcc = $rec['accommodations'] ?? [];
            if (empty($rawAcc)) {
                $rawAcc = [
                    // Bali
                    ['name' => 'Bali Seaside Resort', 'type' => 'Hotel', 'price_per_night' => 150],
                    ['name' => 'Bali Ubud Villa', 'type' => 'Villa', 'price_per_night' => 180],
                    // Kyoto
                    ['name' => 'Kyoto Ryokan', 'type' => 'Ryokan', 'price_per_night' => 100],
                    ['name' => 'Kyoto City Hotel', 'type' => 'Hotel', 'price_per_night' => 120],
                ];
            }

            // Filter akomodasi agar relevan dengan destinasi terpilih (nama/negara)
            $selectedNames = array_map(fn($d) => strtolower($d['name'] ?? ''), $selectedDestinations);
            $selectedCountries = array_map(fn($d) => strtolower($d['country'] ?? ''), $selectedDestinations);
            $filteredAcc = [];
            foreach ($rawAcc as $rawIdx => $acc) {
                $name = strtolower($acc['name'] ?? '');
                $match = false;
                foreach ($selectedNames as $dn) {
                    if (!$dn) { continue; }
                    if ((str_contains($name, 'bali') && str_contains($dn, 'bali')) ||
                        (str_contains($name, 'kyoto') && str_contains($dn, 'kyoto'))) {
                        $match = true; break;
                    }
                }
                if (!$match && !empty($selectedCountries)) {
                    // Cocokkan berdasarkan negara bila nama tidak cocok
                    $accCountry = str_contains($name, 'bali') ? 'indonesia' : (str_contains($name, 'kyoto') ? 'japan' : null);
                    if ($accCountry && in_array($accCountry, $selectedCountries, true)) {
                        $match = true;
                    }
                }
                if ($match) {
                    // Sertakan indeks asli agar bisa dipakai di halaman Anggaran
                    $acc['rec_index'] = $rawIdx;
                    $filteredAcc[] = $acc;
                }
            }
            if (empty($filteredAcc)) { $filteredAcc = $rawAcc; }

            foreach ($filteredAcc as $acc) {
                $price = (int) ($acc['price_per_night'] ?? 0);
                $acc['total_estimate'] = $price * $nights;
                $accommodations[] = $acc;
            }
        }

        return view('recommendations.accommodations', [
            'selectedDestinations' => $selectedDestinations,
            'selectedIndexes' => $selectedIdx,
            'accommodations' => $accommodations,
            'nights' => $nights,
            'recommendationId' => $data['recommendation_id'],
            'showResults' => $request->isMethod('post'),
            'prefCategoryName' => $prefCategoryName,
            'prefCountryName' => $prefCountryName,
            'prefMoodName' => $prefMoodName,
            'prefBudgetMin' => $holidayRow->budget_min ?? null,
            'prefBudgetMax' => $holidayRow->budget_max ?? null,
        ]);
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
