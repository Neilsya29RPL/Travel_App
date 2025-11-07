<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RecommendationService
{
    public function createRecommendation(array $data): int
    {
        return DB::table('ai_recommendations')->insertGetId($data);
    }

    public function deleteRecommendation(int $recommendationId): bool
    {
        return DB::table('ai_recommendations')->where('id', $recommendationId)->delete();
    }
}
