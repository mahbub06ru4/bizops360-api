<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_segments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->string('flight_number');
            $table->string('airline')->nullable();
            $table->string('from_airport');
            $table->string('to_airport');
            $table->dateTime('depart_at');
            $table->dateTime('arrive_at')->nullable();
            $table->string('cabin')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_segments');
    }
};
