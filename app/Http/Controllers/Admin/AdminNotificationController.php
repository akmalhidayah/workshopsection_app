<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function dropdown()
    {
        $notifications = SystemNotification::where('target_role', 'admin')
            ->latest()
            ->take(7)
            ->get();

        $unreadCount = SystemNotification::where('target_role', 'admin')
            ->where('is_read', false)
            ->count();

        return compact('notifications', 'unreadCount');
    }

    public function markAsRead($id)
    {
        $notif = SystemNotification::findOrFail($id);
        $notif->update(['is_read' => true]);

        return redirect($notif->url);
    }
}
