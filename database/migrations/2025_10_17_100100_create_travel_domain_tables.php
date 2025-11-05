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
        // 5. Users Holiday
        Schema::create('users_holiday', function (Blueprint $table) {
            $table->id('holiday_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('master_category', 'category_id');
            $table->foreignId('country_id')->nullable()->constrained('master_country', 'country_id');
            $table->foreignId('mood_id')->nullable()->constrained('master_mood', 'mood_id');
            $table->string('destination_hint', 100)->nullable();
            $table->integer('duration_days')->nullable();
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->timestamps();
        });

        // 6. Destinations
        Schema::create('destinations', function (Blueprint $table) {
            $table->id('destination_id');
            $table->string('name', 100);
            $table->foreignId('country_id')->nullable()->constrained('master_country', 'country_id');
            $table->foreignId('category_id')->nullable()->constrained('master_category', 'category_id');
            $table->foreignId('mood_id')->nullable()->constrained('master_mood', 'mood_id');
            $table->text('description')->nullable();
            $table->decimal('average_cost', 15, 2)->nullable();
            $table->timestamps();
        });

        // 7. Accommodations
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id('accommodation_id');
            $table->foreignId('destination_id')->nullable()->constrained('destinations', 'destination_id');
            $table->string('name', 120);
            $table->string('type', 40)->nullable();
            $table->decimal('price_per_night', 15, 2)->nullable();
            $table->decimal('rating', 4, 2)->nullable();
            $table->string('provider', 60)->nullable();
            $table->string('external_id', 80)->nullable();
            $table->timestamps();
        });

        // 8. AI Recommendations (store JSON payload)
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id('recommendation_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('holiday_id')->nullable()->constrained('users_holiday', 'holiday_id')->onDelete('cascade');
            $table->string('source', 50)->default('local');
            $table->json('payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
        Schema::dropIfExists('accommodations');
        Schema::dropIfExists('destinations');
        Schema::dropIfExists('users_holiday');
    }
};