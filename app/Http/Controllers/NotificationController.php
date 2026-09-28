<?php

namespace App\Http\Controllers;

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
            $notifications = $user->notifications()
                ->latest()
                ->take(15)
                ->get()
                ->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'data' => $n->data,
                        'read_at' => $n->read_at,
                        'created_at' => $n->created_at->diffForHumans(),
                    ];
                });

            return response()->json([
                'unread_count' => $user->unreadNotifications()->count(),
                'notifications' => $notifications,
            ]);
        }

        $notifications = $user->notifications()->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
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
