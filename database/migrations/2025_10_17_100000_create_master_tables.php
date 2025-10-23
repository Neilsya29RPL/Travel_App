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
        Schema::create('master_category', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('category_name', 50);
            $table->timestamps();
        });

        Schema::create('master_country', function (Blueprint $table) {
            $table->id('country_id');
            $table->string('country_name', 50);
            $table->timestamps();
        });

        Schema::create('master_mood', function (Blueprint $table) {
            $table->id('mood_id');
            $table->string('mood_name', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_mood');
        Schema::dropIfExists('master_country');
        Schema::dropIfExists('master_category');
    }
};