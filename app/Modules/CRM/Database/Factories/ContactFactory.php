<?php

declare(strict_types=1);

namespace App\Modules\CRM\Database\Factories;

use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Lead;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'contactable_type' => (new Lead)->getMorphClass(),
            'contactable_id' => Lead::factory(),
            'name' => fake()->name(),
            'title' => fake()->optional()->jobTitle(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'is_primary' => false,
            'notes' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function attachedTo(Model $contactable): static
    {
        return $this->state(fn (array $attributes): array => [
            'contactable_type' => $contactable->getMorphClass(),
            'contactable_id' => $contactable->getKey(),
        ]);
    }
}
