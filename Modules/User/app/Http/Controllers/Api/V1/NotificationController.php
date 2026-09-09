<?php

namespace Modules\User\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\User\Actions\MarkAllNotificationsRead;
use Modules\User\Actions\MarkNotificationRead;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->user()
            ->notifications()
            ->when($request->boolean('unread'), fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(min($request->integer('per_page', 20), 100));

        return NotificationResource::collection($notifications);
    }

    public function markRead(
        Request $request,
        string $notification,
        MarkNotificationRead $markNotificationRead,
    ): NotificationResource {
        return new NotificationResource(
            $markNotificationRead->handle($request->user(), $notification),
        );
    }

    public function markAllRead(Request $request, MarkAllNotificationsRead $markAllNotificationsRead): JsonResponse
    {
        $markAllNotificationsRead->handle($request->user());

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
