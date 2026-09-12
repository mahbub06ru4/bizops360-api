<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // A platform admin has tenant_id = null and is authorized purely
            // by this flag (see Billing\Http\Middleware\EnsurePlatformAdmin) —
            // deliberately outside the tenant RBAC, since platform-level
            // analytics span every tenant.
            $table->boolean('is_platform_admin')->default(false)->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_platform_admin');
        });
    }
};
