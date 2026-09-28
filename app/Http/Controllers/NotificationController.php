<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of user notifications or return JSON list.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();

        if ($request->wantsJson() || $request->ajax()) {
            $rawNotifications = $user->notifications()
                ->latest()
                ->take(15)
                ->get();

            $interestIds = $rawNotifications->pluck('data.interest_id')->filter()->unique();
            $interests = Interest::whereIn('id', $interestIds)->get()->keyBy('id');

            $notifications = $rawNotifications->map(function ($n) use ($interests) {
                $itemData = $n->data;
                if (! empty($itemData['interest_id']) && isset($interests[$itemData['interest_id']])) {
                    $itemData['interest_status'] = $interests[$itemData['interest_id']]->status;
                }

                return [
                    'id' => $n->id,
                    'data' => $itemData,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            });

            return response()->json([
                'unread_count' => $user->unreadNotifications()->count(),
                'notifications' => $notifications,
            ]);
        }

        // When user visits the notifications page, auto-mark unread notifications as read
        $user->unreadNotifications()->update(['read_at' => now()]);

        $notifications = $user->notifications()->latest()->paginate(20);
        $interestIds = $notifications->pluck('data.interest_id')->filter()->unique();
        $interests = Interest::whereIn('id', $interestIds)->get()->keyBy('id');

        return view('notifications.index', compact('notifications', 'interests'));
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $id): JsonResponse|RedirectResponse
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => Auth::user()->unreadNotifications()->count(),
            ]);
        }

        return back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse|RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
