<?php

declare(strict_types=1);

namespace App\Modules\Finance\Database\Factories;

use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoicePayment;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoicePayment>
 */
class InvoicePaymentFactory extends Factory
{
    protected $model = InvoicePayment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'invoice_id' => Invoice::factory(),
            'customer_id' => null,
            'recorded_by' => null,
            'amount' => fake()->randomFloat(2, 50, 5000),
            'paid_on' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'reference' => fake()->optional()->bothify('PAY-####'),
            'note' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->getKey(),
            'customer_id' => $invoice->customer_id,
        ]);
    }
}
