<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The share structure only. The ownership ledger (who holds which
        // shares, transfers, payouts) is Phase 1+ and will hang off this row.
        Schema::create('land_shares', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('real_estate_projects')->cascadeOnDelete();
            $table->unsignedInteger('total_shares');
            $table->decimal('share_value', 15, 2);
            $table->timestamps();

            $table->index(['tenant_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('land_shares');
    }
};
