<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_itinerary_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'day_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_itinerary_items');
    }
};
