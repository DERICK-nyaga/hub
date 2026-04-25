<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Collection;

class NotificationService
{
    public function getDashboardStats(): array
    {
        return [
            'unread' => Notification::whereNull('read_at')->count(),
            'total' => Notification::count(),
        ];
    }

    public function getUnreadCount($userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function getUserNotifications($userId, int $limit = 5): Collection
    {
        return Notification::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function checkAllExpiries(): array
    {
        return [
            'airtime' => collect([]),
            'internet' => collect([]),
            'overdue_payments' => collect([]),
            'upcoming_schedules' => collect([]),
        ];
    }
}
