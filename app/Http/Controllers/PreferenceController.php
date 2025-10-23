<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PreferenceController extends Controller
{
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
            'category_id' => ['nullable', 'integer'],
            'country_id' => ['nullable', 'integer'],
            'mood_id' => ['nullable', 'integer'],
            'destination_hint' => ['nullable', 'string', 'max:100'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'default_payment_method' => ['nullable', 'string'],
        ]);

        $data['user_id'] = Auth::id();
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('users_holiday')->insert($data);

        return redirect()->route('recommendations.index')->with('status', 'Preferensi liburan tersimpan.');
    }
}