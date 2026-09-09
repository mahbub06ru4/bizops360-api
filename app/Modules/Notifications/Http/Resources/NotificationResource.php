<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;

class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var DatabaseNotification $notification */
        $notification = $this->resource;

        /** @var Carbon|null $readAt */
        $readAt = $notification->getAttribute('read_at');
        /** @var Carbon|null $createdAt */
        $createdAt = $notification->getAttribute('created_at');

        return [
            'id' => $notification->getKey(),
            'type' => class_basename((string) $notification->getAttribute('type')),
            'data' => $notification->getAttribute('data'),
            'read_at' => $readAt?->toIso8601String(),
            'created_at' => $createdAt?->toIso8601String(),
        ];
    }
}
