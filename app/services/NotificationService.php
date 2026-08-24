<?php
namespace App\Services;

use App\Core\Database;

class NotificationService {
    public static function send(?int $userId, string $title, string $message, string $type = 'alert', ?string $targetUrl = null): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `target_url`, `is_read`)
            VALUES (:user_id, :title, :message, :type, :target_url, 0)
        ");
        $stmt->execute([
            'user_id'    => $userId,
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'target_url' => $targetUrl
        ]);
        return (int)$db->lastInsertId();
    }

    public static function getUnread(?int $userId, int $limit = 5): array {
        $db = Database::getConnection();
        if ($userId) {
            $stmt = $db->prepare("
                SELECT * FROM `notifications`
                WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0
                ORDER BY id DESC LIMIT {$limit}
            ");
            $stmt->execute(['user_id' => $userId]);
        } else {
            $stmt = $db->query("
                SELECT * FROM `notifications`
                WHERE is_read = 0
                ORDER BY id DESC LIMIT {$limit}
            ");
        }
        return $stmt->fetchAll();
    }

    public static function countUnread(?int $userId): int {
        $db = Database::getConnection();
        if ($userId) {
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM `notifications` WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0");
            $stmt->execute(['user_id' => $userId]);
        } else {
            $stmt = $db->query("SELECT COUNT(*) as total FROM `notifications` WHERE is_read = 0");
        }
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }

    public static function markAsRead(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE `notifications` SET is_read = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function markAllAsRead(?int $userId): bool {
        $db = Database::getConnection();
        if ($userId) {
            $stmt = $db->prepare("UPDATE `notifications` SET is_read = 1 WHERE user_id = :user_id OR user_id IS NULL");
            return $stmt->execute(['user_id' => $userId]);
        } else {
            return (bool)$db->exec("UPDATE `notifications` SET is_read = 1");
        }
    }
}
