<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Notifications\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    /**
     * The authenticated user's notifications (newest first). `?unread=1` limits to
     * unread.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $this->user($request);

        $query = $request->boolean('unread')
            ? $user->unreadNotifications()
            : $user->notifications();

        return NotificationResource::collection($query->paginate());
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'data' => ['unread' => $this->user($request)->unreadNotifications()->count()],
        ]);
    }

    public function markRead(Request $request, string $notification): NotificationResource
    {
        /** @var DatabaseNotification $model */
        $model = $this->user($request)->notifications()->whereKey($notification)->firstOrFail();
        $model->markAsRead();

        return NotificationResource::make($model);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->user($request)->unreadNotifications()->update(['read_at' => Carbon::now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }
}
