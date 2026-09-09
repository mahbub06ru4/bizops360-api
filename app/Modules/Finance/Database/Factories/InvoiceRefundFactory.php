<?php

declare(strict_types=1);

namespace App\Modules\Finance\Database\Factories;

use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoiceRefund;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceRefund>
 */
class InvoiceRefundFactory extends Factory
{
    protected $model = InvoiceRefund::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'invoice_id' => Invoice::factory(),
            'payment_id' => null,
            'recorded_by' => null,
            'amount' => fake()->randomFloat(2, 10, 1000),
            'refunded_on' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'reason' => fake()->optional()->sentence(4),
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
        ]);
    }
}
