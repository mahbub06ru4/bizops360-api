<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category')->default('other');
            $table->string('source')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('received_on');
            $table->string('method')->default('cash');
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'received_on']);
            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
