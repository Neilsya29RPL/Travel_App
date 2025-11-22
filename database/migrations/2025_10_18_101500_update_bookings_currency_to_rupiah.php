<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        try {
            DB::table('bookings')->where('currency', 'IDR')->update(['currency' => 'Rupiah']);
            DB::table('bookings')->whereNull('currency')->update(['currency' => 'Rupiah']);
        } catch (\Throwable $e) {
            // optional: log if needed
        }
    }

    public function down(): void
    {
        try {
            DB::table('bookings')->where('currency', 'Rupiah')->update(['currency' => 'IDR']);
        } catch (\Throwable $e) {
            // optional: log if needed
        }
    }
};