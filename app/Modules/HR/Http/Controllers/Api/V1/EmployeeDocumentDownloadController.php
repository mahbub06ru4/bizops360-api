<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\EmployeeDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams an employee document's file. Reached only via a short-lived signed URL
 * (issued by EmployeeDocumentResource after the view policy passed) plus the
 * caller's Sanctum token and a re-check of the view policy.
 */
class EmployeeDocumentDownloadController extends Controller
{
    public function __invoke(EmployeeDocument $employeeDocument): StreamedResponse
    {
        $this->authorize('view', $employeeDocument);

        return Storage::disk($employeeDocument->disk)->download(
            $employeeDocument->path,
            $employeeDocument->original_name,
        );
    }
}
