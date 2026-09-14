<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Industry\RealEstate\Actions\GenerateInstallmentInvoice;
use App\Modules\Industry\RealEstate\Actions\MarkInstallmentPaid;
use App\Modules\Industry\RealEstate\Http\Resources\InstallmentResource;
use App\Modules\Industry\RealEstate\Models\Installment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function show(Installment $installment): InstallmentResource
    {
        $this->authorize('view', $installment);

        return InstallmentResource::make($installment);
    }

    public function generateInvoice(Request $request, Installment $installment, GenerateInstallmentInvoice $action): InstallmentResource
    {
        $this->authorize('update', $installment);

        /** @var User $user */
        $user = $request->user();

        return InstallmentResource::make($action->handle($installment, $user));
    }

    public function markPaid(Installment $installment, MarkInstallmentPaid $action): InstallmentResource
    {
        $this->authorize('update', $installment);

        return InstallmentResource::make($action->handle($installment));
    }
}
