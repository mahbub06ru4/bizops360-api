<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table): void {
            $table->string('label')->default('Head Office')->after('tenant_id');
            $table->decimal('latitude', 10, 7)->nullable()->after('label');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedInteger('radius_meters')->default(500)->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table): void {
            $table->dropColumn(['label', 'latitude', 'longitude', 'radius_meters']);
        });
    }
};
