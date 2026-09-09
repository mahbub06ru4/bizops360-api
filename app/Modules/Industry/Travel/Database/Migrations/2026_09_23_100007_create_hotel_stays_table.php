<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_stays', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('hotel_name');
            $table->string('city');
            $table->string('country')->nullable();
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('nights');
            $table->string('room_type')->nullable();
            $table->unsignedSmallInteger('rooms')->default(1);
            $table->unsignedSmallInteger('guests')->default(1);
            $table->string('board_basis')->default('room_only');
            $table->string('confirmation_no')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['booking_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_stays');
    }
};
