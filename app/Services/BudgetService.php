<?php

namespace App\Services;

use Carbon\Carbon;

class BudgetService
{
    public function calculateNights($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return max(1, $start->diffInDays($end) + 1);
    }

    public function calculateTotal($destination, $accommodation, $nights)
    {
        $baseTransport = 300; // ribuan
        $destBudget = $destination['est_budget'] ?? 0;
        $activities = $destBudget > 0 ? $destBudget * $nights : 200 * $nights; // ribuan
        $priceNight = $accommodation['price_per_night'] ?? 80;
        $accommodationCost = $priceNight * $nights;

        return $baseTransport + $activities + $accommodationCost;
    }
}
