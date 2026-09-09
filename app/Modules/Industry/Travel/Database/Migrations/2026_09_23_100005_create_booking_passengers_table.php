<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_passengers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('traveller_id')->constrained('travellers')->cascadeOnDelete();
            $table->string('ticket_number')->nullable();
            $table->string('baggage')->nullable();
            $table->decimal('fare_amount', 15, 2)->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'traveller_id']);
            $table->index(['tenant_id', 'traveller_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_passengers');
    }
};
