<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Policies\LandRecordPolicy;

/**
 * Application input for a project's land record (mouza, JL/khatian/dag no.).
 * Admin-only — see {@see LandRecordPolicy}.
 */
final readonly class LandRecordData
{
    public function __construct(
        public ?string $mouza,
        public ?string $jlNo,
        public ?string $khatianNo,
        public ?string $dagNo,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            mouza: isset($validated['mouza']) ? (string) $validated['mouza'] : null,
            jlNo: isset($validated['jl_no']) ? (string) $validated['jl_no'] : null,
            khatianNo: isset($validated['khatian_no']) ? (string) $validated['khatian_no'] : null,
            dagNo: isset($validated['dag_no']) ? (string) $validated['dag_no'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'mouza' => $this->mouza,
            'jl_no' => $this->jlNo,
            'khatian_no' => $this->khatianNo,
            'dag_no' => $this->dagNo,
        ];
    }
}
