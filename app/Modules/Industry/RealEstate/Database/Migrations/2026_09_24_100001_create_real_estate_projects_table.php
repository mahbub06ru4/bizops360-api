<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('real_estate_projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('project_type')->default('apartment');
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('total_land_area', 12, 2)->nullable();
            $table->string('currency', 3)->default('BDT');
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'project_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('real_estate_projects');
    }
};
