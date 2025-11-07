<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIRecommendationService
{
    private $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    public function generate($userId, $holidayId, $data)
    {
        $payload = [
            'category_id'   => $data['category_id'],
            'country_id'    => $data['country_id'],
            'mood_id'       => $data['mood_id'],
            'duration_days' => $data['duration_days'] ?? null,
            'budget_min'    => $data['budget_min'],
            'budget_max'    => $data['budget_max'],
        ];

        $apiKey = env('GOOGLE_AI_API_KEY');
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";
        $timeout = 2000;
        $result = null;
        $source = 'local';

        try {
            if ($apiKey) {
                $categoryName = DB::table('master_category')->where('category_id', $payload['category_id'])->value('category_name');
                $countryName = DB::table('master_country')->where('country_id', $payload['country_id'])->value('country_name');
                $moodName = DB::table('master_mood')->where('mood_id', $payload['mood_id'])->value('mood_name');

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
                    {\"name\": \"...\", \"country\": \"...\", \"mood\": \"...\", \"activities\": [\"...\"], \"est_budget\": ...}
                ],
                \"accommodations\": [{\"name\": \"...\", \"type\": \"...\", \"price_per_night\": ...}],
                \"total_estimate\": ...,
                \"currency\": \"Rupiah\"
                }
                ";

                $geminiPayload = [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['responseMimeType' => 'application/json']
                ];

                $resp = Http::timeout($timeout)
                    ->withHeaders(['x-goog-api-key' => $apiKey])
                    ->accept('application/json')
                    ->asJson()
                    ->post($endpoint, $geminiPayload);

                if ($resp->successful()) {
                    $apiJsonString = $resp->json('candidates.0.content.parts.0.text');
                    if ($apiJsonString) {
                        $result = json_decode($apiJsonString, true);
                        if (json_last_error() === JSON_ERROR_NONE && !empty($result)) {
                            $source = 'api';
                        } else {
                            Log::error('Gemini API returned invalid JSON: ' . $apiJsonString);
                        }
                    }
                } else {
                    Log::error('Gemini API request failed: ' . $resp->body());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Gemini integration error: ' . $e->getMessage());
        }

        if (!$result) {
            $result = $this->fallbackRecommendation($data);
        }

        $recommendationId = $this->recommendationService->createRecommendation([
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

    public function delete($recommendationId)
    {
        return $this->recommendationService->deleteRecommendation($recommendationId);
    }

    private function fallbackRecommendation($data)
    {
        $baseTransport = 300;
        $activities = 200;

        return [
            'destinations' => [
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
            ],
            'accommodations' => [],
            'total_estimate' => $baseTransport + $activities,
            'currency' => 'Rupiah',
        ];
    }
}
