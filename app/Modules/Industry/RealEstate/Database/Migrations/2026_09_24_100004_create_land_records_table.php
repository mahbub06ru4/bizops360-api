<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legal/private data (mouza, JL/khatian/dag numbers). Visibility is
        // enforced at the Policy layer, not by a column here — see
        // App\Modules\Industry\RealEstate\Policies\LandRecordPolicy.
        Schema::create('land_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('real_estate_projects')->cascadeOnDelete();
            $table->string('mouza')->nullable();
            $table->string('jl_no')->nullable();
            $table->string('khatian_no')->nullable();
            $table->string('dag_no')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('land_records');
    }
};
