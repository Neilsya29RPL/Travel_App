<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('holiday_choices', function (Blueprint $table) {
            $table->id('choice_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('holiday_id')->constrained('users_holiday', 'holiday_id')->onDelete('cascade');
            $table->string('destination_name', 120)->nullable();
            $table->decimal('dest_est_budget', 15, 2)->nullable();
            $table->string('accommodation_name', 120)->nullable();
            $table->decimal('acc_price_per_night', 15, 2)->nullable();
            $table->integer('duration_days')->nullable();
            $table->decimal('total_estimate', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_choices');
    }
};