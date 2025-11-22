<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        session()->forget('booking_cleared');
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

        //from env
        $apiKey = env('GOOGLE_AI_API_KEY'); // Your Google AI Studio Key
        // The API endpoint without the key in the URL
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";

        $timeout  = (int) 2000; // Give AI a bit more time

        $result = null;
        $source = 'local'; // Default to local

        try {
            if ($apiKey) {
                $categoryName = DB::table('master_category')->where('category_id', $payload['category_id'])->value('category_name');
                $countryName = DB::table('master_country')->where('country_id', $payload['country_id'])->value('country_name');
                $moodName = DB::table('master_mood')->where('mood_id', $payload['mood_id'])->value('mood_name');

                // --- 2. CREATE A PROMPT ---
                // This is the most important part. You must convert your data into an instruction.
                // PRO-TIP: This prompt would be even better if you passed names
                // instead of IDs (e.g., "Relax & Beach" instead of "mood_id: 3")
                $prompt = "You are a travel recommendation expert. Generate a travel plan with 3 different destination options based on these preferences:
                - Category: {$categoryName}
                - Country: {$countryName}
                - Mood: {$moodName}
                - Duration: {$payload['duration_days']} days
                - Budget: {$payload['budget_min']} to {$payload['budget_max']}

                Please provide a response ONLY in a valid JSON format, matching this exact structure:
                {
                \"destinations\": [
                    {\"name\": \"...\", \"country\": \"...\", \"mood\": \"...\", \"activities\": [\"...\"], \"est_budget\": ...},
                    {\"name\": \"...\", \"country\": \"...\", \"mood\": \"...\", \"activities\": [\"...\"], \"est_budget\": ...},
                    {\"name\": \"...\", \"country\": \"...\", \"mood\": \"...\", \"activities\": [\"...\"], \"est_budget\": ...},
                    {\"name\": \"...\", \"country\": \"...\", \"mood\": \"...\", \"activities\": [\"...\"], \"est_budget\": ...}
                ],
                \"accommodations\": [{\"name\": \"...\", \"type\": \"...\", \"price_per_night\": ...}],
                \"total_estimate\": ...,
                \"currency\": \"Rupiah\"
                }
                ";

                // --- 3. FORMAT THE GEMINI PAYLOAD ---
                // This is the specific JSON structure Gemini expects.
                $geminiPayload = [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    // This "pro-tip" tells Gemini to ALWAYS return JSON. Very reliable!
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ]
                ];

                // --- 4. MAKE THE API CALL ---
                $resp = Http::timeout($timeout)
                    ->withHeaders([
                        'x-goog-api-key' => $apiKey
                    ])
                    ->accept('application/json')
                    ->asJson() // Send our data as JSON
                    ->post($endpoint, $geminiPayload); // Send the NEW payload


                if ($resp->successful()) {
                    // --- 5. PARSE THE GEMINI RESPONSE ---
                    // The structure is different: { "candidates": [ ... ] }
                    // We also use json_decode because Gemini returns a JSON *string*.
                    $apiJsonString = $resp->json('candidates.0.content.parts.0.text');

                    if ($apiJsonString) {
                        $result = json_decode($apiJsonString, true);
                        // Check if decoding worked and it's not empty
                        if (json_last_error() === JSON_ERROR_NONE && !empty($result)) {
                            // Your normalizeResult might not be needed if you trust the prompt,
                            // but you can keep it for validation.
                            $result = $this->normalizeResult((array) $result) ?: (array) $result;
                            $source = 'api';
                        } else {
                            // Gemini response was not valid JSON
                            Log::error('Gemini API returned invalid JSON: ' . $apiJsonString);
                        }
                    }
                } else {
                    // Log the API error if it failed
                    Log::error('Gemini API request failed: ' . $resp->body());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Gemini integration error: ' . $e->getMessage());
            // fallback to local data
        }

        // --- 6. FALLBACK & SAVE (Your existing logic) ---
        // This part is identical to your original code.
        // If $result is still null, this logic will run.
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
            'payload' => json_encode($payload), // The original payload
            'response_json' => json_encode($result), // The AI or local result
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
