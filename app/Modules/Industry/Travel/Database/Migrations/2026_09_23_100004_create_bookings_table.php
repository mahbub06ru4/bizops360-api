<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('handled_by_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference');
            $table->string('type')->default('air_ticket');
            $table->string('title');
            $table->string('supplier_name')->nullable();
            $table->string('pnr')->nullable();
            $table->string('airline')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->date('depart_on')->nullable();
            $table->date('return_on')->nullable();
            $table->string('status')->default('quoted');
            $table->decimal('cost_amount', 15, 2)->default(0);
            $table->decimal('sell_amount', 15, 2)->default(0);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('BDT');
            $table->date('issued_on')->nullable();
            $table->date('cancelled_on')->nullable();
            $table->date('refund_on')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'reference']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'depart_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
