<?php

namespace App\Services;

use App\Models\SystemNotification;

class NotificationService
{
    public static function notifyAdmin(array $data)
    {
        return SystemNotification::create([
            'target_role' => 'admin',
            'entity_type' => $data['entity_type'],
            'entity_id'   => $data['entity_id'],
            'action'      => $data['action'],
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'url'         => $data['url'],
            'priority'    => $data['priority'] ?? 'normal',
            'is_read'     => false,
        ]);
    }

    // ✅ JADIKAN PUBLIC
    public static function mapPriorityFromNotification(string $priority): string
    {
        return match ($priority) {
            'Urgently' => 'high',
            'Hard'     => 'high',
            'Medium'   => 'normal',
            'Low'      => 'low',
            default    => 'normal',
        };
    }
}


