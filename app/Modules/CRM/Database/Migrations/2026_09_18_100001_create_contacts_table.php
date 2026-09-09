<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('contactable_type');
            $table->unsignedBigInteger('contactable_id');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'contactable_type', 'contactable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
