<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class FinanceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    public function from(): Carbon
    {
        return $this->date('from') ?? Carbon::now()->startOfMonth();
    }

    public function to(): Carbon
    {
        return $this->date('to') ?? Carbon::now();
    }

    public function year(): int
    {
        return $this->integer('year', (int) Carbon::now()->year);
    }
}
