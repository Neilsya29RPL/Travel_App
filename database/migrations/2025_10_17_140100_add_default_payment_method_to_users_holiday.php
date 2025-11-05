<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users_holiday', function (Blueprint $table) {
            $table->string('default_payment_method', 30)->nullable()->after('budget_max');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_holiday', function (Blueprint $table) {
            $table->dropColumn('default_payment_method');
        });
    }
};