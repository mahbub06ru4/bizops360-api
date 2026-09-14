<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\UnitMediaData;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Industry\RealEstate\Models\UnitMedia;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Stores one unit photo/floor-plan/video on the configured disk and records
 * its metadata.
 */
class AddUnitMedia
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Unit $unit, UnitMediaData $data, UploadedFile $file): UnitMedia
    {
        $this->assertTenantOwns($unit);

        $tenantId = (int) $unit->tenant_id;
        $disk = (string) config('filesystems.default');

        $path = $file->store("tenants/{$tenantId}/real-estate/units/{$unit->getKey()}/media", $disk);

        if (! is_string($path)) {
            throw ValidationException::withMessages(['file' => 'The file could not be stored.']);
        }

        $media = new UnitMedia([
            'media_type' => $data->mediaType,
            'sort_order' => $data->sortOrder,
        ]);
        $media->tenant_id = $tenantId;
        $media->unit_id = $unit->getKey();
        $media->file_path = $path;
        $media->save();

        return $media;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
