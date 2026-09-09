<?php

declare(strict_types=1);

namespace App\Modules\Finance\Database\Factories;

use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issue = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => null,
            'created_by' => null,
            'number' => 'INV-'.fake()->unique()->numerify('######'),
            'customer_name' => fake()->company(),
            'status' => InvoiceStatus::Draft,
            'issue_date' => $issue->format('Y-m-d'),
            'due_date' => (clone $issue)->modify('+14 days')->format('Y-m-d'),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'amount_paid' => 0,
            'amount_refunded' => 0,
            'notes' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function status(InvoiceStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }
}
