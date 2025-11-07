<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AIRecommendationService;
use App\Services\RecommendationService;

class RecommendationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AIRecommendationService::class, function ($app) {
            return new AIRecommendationService($app->make(RecommendationService::class));
        });

        $this->app->singleton(RecommendationService::class, function ($app) {
            return new RecommendationService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
