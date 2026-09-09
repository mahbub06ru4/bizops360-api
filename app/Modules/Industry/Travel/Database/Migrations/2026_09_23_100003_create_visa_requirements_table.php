<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visa_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visa_application_id')->constrained('visa_applications')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('collected')->default(false);
            $table->date('collected_on')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['visa_application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_requirements');
    }
};
