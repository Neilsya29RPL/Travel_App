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
        // 9. Bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('holiday_id')->nullable()->constrained('users_holiday', 'holiday_id')->onDelete('cascade');
            $table->foreignId('destination_id')->nullable()->constrained('destinations', 'destination_id');
            $table->foreignId('accommodation_id')->nullable()->constrained('accommodations', 'accommodation_id');
            $table->string('status', 30)->default('pending');
            $table->string('currency', 10)->default('IDR');
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->timestamp('booking_date')->useCurrent();
            $table->string('payment_status', 30)->default('unpaid');
            $table->timestamps();
        });

        // 10. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('booking_id')->constrained('bookings', 'booking_id')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('method', 30)->default('virtual_account');
            $table->string('provider', 50)->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('transaction_id', 80)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
    }
};