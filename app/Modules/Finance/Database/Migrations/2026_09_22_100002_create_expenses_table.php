<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category')->default('other');
            $table->string('supplier_name')->nullable();
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->date('spent_on');
            $table->string('method')->default('cash');
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'spent_on']);
            $table->index(['tenant_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
