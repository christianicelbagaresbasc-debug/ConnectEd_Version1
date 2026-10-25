<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = $user->notifications();

        if ($request->input('filter') === 'unread') {
            $query = $user->unreadNotifications();
        }

        $notifications = $query->paginate(20)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            $actionUrl = $notification->data['action_url'] ?? null;
            if ($actionUrl) {
                return redirect($actionUrl);
            }
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
