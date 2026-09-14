<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->unique()->constrained('real_estate_bookings')->cascadeOnDelete();
            $table->decimal('down_payment_amount', 15, 2);
            $table->unsignedInteger('installment_count');
            $table->string('frequency')->default('monthly');
            $table->date('start_date');
            $table->timestamps();

            $table->index(['tenant_id', 'booking_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_plans');
    }
};
