<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        abort_unless(Auth::check(), 403);

        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    public function show(DatabaseNotification $notification): View
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === (int) $notification->notifiable_id, 403);

        $notification->markAsRead();

        return view('notifications.show', compact('notification'));
    }

    public function markAsRead(DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === (int) $notification->notifiable_id, 403);

        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marquée comme lue.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        abort_unless(Auth::check(), 403);

        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
}

