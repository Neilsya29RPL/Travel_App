<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PreferenceController extends Controller
{
    // Preferences (single form)
    public function index()
    {
        $userId = Auth::id();
        $holiday = DB::table('users_holiday')->where('user_id', $userId)->orderByDesc('holiday_id')->first();
        $categories = DB::table('master_category')->orderBy('category_name')->get();
        $countries = DB::table('master_country')->orderBy('country_name')->get();
        $moods = DB::table('master_mood')->orderBy('mood_name')->get();

        return view('preferences.index', compact('holiday', 'categories', 'countries', 'moods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer'],
            'country_id' => ['required', 'integer'],
            'mood_id' => ['required', 'integer'],
            'destination_hint' => ['nullable', 'string', 'max:100'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'budget_min' => ['required', 'numeric', 'min:0'],
            'budget_max' => ['required', 'numeric', 'min:0', 'gte:budget_min'],
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'country_id.required' => 'Negara wajib dipilih.',
            'mood_id.required' => 'Mood wajib dipilih.',
            'duration_days.min' => 'Durasi minimal 1 hari.',
            'duration_days.max' => 'Durasi maksimal 30 hari.',
            'budget_min.required' => 'Budget minimal wajib diisi.',
            'budget_max.required' => 'Budget maksimal wajib diisi.',
            'budget_max.gte' => 'Budget maksimal harus lebih besar atau sama dengan budget minimal.',
        ]);

        $userId = Auth::id();
        $data['user_id'] = $userId;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // Simpan preferensi
        $holidayId = DB::table('users_holiday')->insertGetId($data);

        // Generate AI Recommendation
        $recommendation = $this->generateAIRecommendation($userId, $holidayId, $data);

        // Ambil data untuk view
        $categories = DB::table('master_category')->orderBy('category_name')->get();
        $countries = DB::table('master_country')->orderBy('country_name')->get();
        $moods = DB::table('master_mood')->orderBy('mood_name')->get();
        $holiday = (object) $data;
        $holiday->holiday_id = $holidayId;

        return view('preferences.index', compact('holiday', 'categories', 'countries', 'moods', 'recommendation'))
            ->with('status', 'Rekomendasi AI berhasil dibuat!');
    }

    private function generateAIRecommendation($userId, $holidayId, $data)
    {
        $payload = [
            'category_id'   => $data['category_id'],
            'country_id'    => $data['country_id'],
            'mood_id'       => $data['mood_id'],
            'duration_days' => $data['duration_days'] ?? null,
            'budget_min'    => $data['budget_min'],
            'budget_max'    => $data['budget_max'],
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
            // fallback to local data
        }

        if (!$result) {
            $days = (int) ($data['duration_days'] ?? 3);
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

            $result = [
                'destinations' => $destinations,
                'accommodations' => $accommodations,
                'total_estimate' => $baseTransport + $activities,
                'currency' => 'Rupiah',
            ];
        }

        // Simpan ke database
        $recommendationId = DB::table('ai_recommendations')->insertGetId([
            'user_id' => $userId,
            'holiday_id' => $holidayId,
            'payload' => json_encode($payload),
            'response_json' => json_encode($result),
            'source' => $source,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (object) [
            'recommendation_id' => $recommendationId,
            'response_json' => json_encode($result)
        ];
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