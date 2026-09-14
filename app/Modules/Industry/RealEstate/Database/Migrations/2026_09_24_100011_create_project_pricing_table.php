<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per project (the cost breakdown that rolls up into
        // estimated_total, computed at write time by SetProjectPricing).
        Schema::create('project_pricing', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('real_estate_projects')->cascadeOnDelete();
            $table->decimal('land_cost', 15, 2)->default(0);
            $table->decimal('construction_cost', 15, 2)->default(0);
            $table->decimal('consultancy_cost', 15, 2)->default(0);
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->string('currency', 3)->default('BDT');
            $table->timestamps();

            $table->unique('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_pricing');
    }
};
