<?php

declare(strict_types=1);

namespace App\Modules\HR\Database\Factories;

use App\Modules\HR\Domain\EmployeeDocumentCategory;
use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EmployeeDocument>
 */
class EmployeeDocumentFactory extends Factory
{
    protected $model = EmployeeDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::random(12).'.pdf';

        return [
            'tenant_id' => Tenant::factory(),
            'employee_id' => Employee::factory(),
            'category' => EmployeeDocumentCategory::Contract,
            'title' => fake()->sentence(3),
            'disk' => 'local',
            'path' => "documents/{$name}",
            'original_name' => $name,
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1000, 500000),
            'expires_at' => null,
            'uploaded_by' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
