<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('description');
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['tenant_id', 'subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
    }
};
