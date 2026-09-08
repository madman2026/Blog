<?php

namespace Modules\User\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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

    public function markRead(Request $request, string $notification): NotificationResource
    {
        $notificationModel = $request->user()->notifications()->findOrFail($notification);
        $notificationModel->markAsRead();

        return new NotificationResource($notificationModel->refresh());
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
