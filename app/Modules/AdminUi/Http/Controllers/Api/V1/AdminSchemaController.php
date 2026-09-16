<?php

declare(strict_types=1);

namespace App\Modules\AdminUi\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\AdminUi\Domain\AdminSchemaRegistry;
use App\Modules\AdminUi\Http\Resources\AdminSchemaResource;

class AdminSchemaController extends Controller
{
    /**
     * The admin panel's nav/list/form metadata. Any authenticated tenant
     * user may read it — it describes UI structure, not tenant data, and
     * every resource it points at still enforces its own Policy on read/write.
     */
    public function index(AdminSchemaRegistry $registry): AdminSchemaResource
    {
        return AdminSchemaResource::make($registry->build());
    }
}
