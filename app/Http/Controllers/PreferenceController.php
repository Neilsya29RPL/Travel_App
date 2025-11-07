<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PreferenceService;
use App\Services\AIRecommendationService;

class PreferenceController extends Controller
{
    private $preferenceService;
    private $aiRecommendationService;

    public function __construct(PreferenceService $preferenceService, AIRecommendationService $aiRecommendationService)
    {
        $this->preferenceService = $preferenceService;
        $this->aiRecommendationService = $aiRecommendationService;
    }

    // Preferences (single form)
    public function index()
    {
        $data = $this->preferenceService->getPreferenceData();
        return view('preferences.index', $data);
    }

    public function store(Request $request)
    {
        $data = $this->preferenceService->validateAndSavePreferences($request);

        // Generate AI Recommendation using the service
        $recommendation = $this->aiRecommendationService->generate($data['user_id'], $data['holiday_id'], $data);

        $viewData = $this->preferenceService->getPreferenceData($data['holiday_id']);
        $viewData['recommendation'] = $recommendation;

        return view('preferences.index', $viewData)
            ->with('status', 'Rekomendasi AI berhasil dibuat!');
    }
}
