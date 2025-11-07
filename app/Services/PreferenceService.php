<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PreferenceService
{
    public function getPreferenceData($holidayId = null)
    {
        $userId = Auth::id();
        $holiday = $holidayId ? DB::table('users_holiday')->find($holidayId) : DB::table('users_holiday')->where('user_id', $userId)->orderByDesc('holiday_id')->first();
        $categories = DB::table('master_category')->orderBy('category_name')->get();
        $countries = DB::table('master_country')->orderBy('country_name')->get();
        $moods = DB::table('master_mood')->orderBy('mood_name')->get();

        return compact('holiday', 'categories', 'countries', 'moods');
    }

    public function validateAndSavePreferences(Request $request)
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

        // Save preferences
        $holidayId = DB::table('users_holiday')->insertGetId($data);
        $data['holiday_id'] = $holidayId;

        return $data;
    }
}
