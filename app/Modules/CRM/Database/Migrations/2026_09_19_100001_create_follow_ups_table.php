<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('followupable_type');
            $table->unsignedBigInteger('followupable_id');
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('call');
            $table->timestamp('due_at');
            $table->string('status')->default('pending');
            $table->string('notes')->nullable();
            $table->string('outcome')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'due_at']);
            $table->index(['followupable_type', 'followupable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
