<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visa_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('traveller_id')->constrained('travellers')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('destination_country');
            $table->string('visa_type')->default('tourist');
            $table->string('mission')->nullable();
            $table->string('stage')->default('draft');
            $table->string('reference_no')->nullable();
            $table->string('application_no')->nullable();
            $table->decimal('government_fee', 15, 2)->default(0);
            $table->decimal('service_charge', 15, 2)->default(0);
            $table->date('submitted_on')->nullable();
            $table->date('decision_on')->nullable();
            $table->string('decision_note')->nullable();
            $table->date('expected_travel_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'stage']);
            $table->index(['tenant_id', 'traveller_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_applications');
    }
};
