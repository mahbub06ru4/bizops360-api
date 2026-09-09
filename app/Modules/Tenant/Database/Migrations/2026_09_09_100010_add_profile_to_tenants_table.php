<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('legal_name')->nullable()->after('name');
            $table->string('email')->nullable()->after('industry');
            $table->string('phone')->nullable()->after('email');
            $table->string('address')->nullable()->after('phone');
            $table->string('timezone')->default('UTC')->after('address');
            $table->string('currency', 3)->default('USD')->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn(['legal_name', 'email', 'phone', 'address', 'timezone', 'currency']);
        });
    }
};
