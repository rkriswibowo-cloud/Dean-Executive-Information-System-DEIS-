<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\NotificationService;
use App\Helpers\AuthHelper;
use App\Helpers\FormatHelper;

class NotificationController extends Controller {
    public function markAllRead(): void {
        $this->requireAuth();

        $userId = AuthHelper::id();
        $success = NotificationService::markAllAsRead($userId);

        $this->json([
            'success'     => $success,
            'message'     => 'Semua notifikasi berhasil ditandai telah dibaca.',
            'unreadCount' => 0
        ]);
    }

    public function markItemRead(): void {
        $this->requireAuth();

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true) ?? [];
        $id = (int)($data['id'] ?? $_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid notification ID'], 400);
            return;
        }

        $success = NotificationService::markAsRead($id);
        $userId = AuthHelper::id();
        $unreadCount = NotificationService::countUnread($userId);

        $this->json([
            'success'     => $success,
            'unreadCount' => $unreadCount
        ]);
    }

    public function poll(): void {
        $this->requireAuth();

        $userId = AuthHelper::id();
        NotificationService::syncLiveAlerts($userId);

        $unreadCount = NotificationService::countUnread($userId);
        $notifications = NotificationService::getUnread($userId, 10);

        $formatted = array_map(function($n) {
            return [
                'id'         => (int)$n['id'],
                'title'      => $n['title'],
                'message'    => $n['message'],
                'type'       => $n['type'],
                'target_url' => $n['target_url'] ?? 'dashboard',
                'time_ago'   => FormatHelper::timeAgo($n['created_at'])
            ];
        }, $notifications);

        $this->json([
            'success'       => true,
            'unreadCount'   => $unreadCount,
            'notifications' => $formatted
        ]);
    }
}
