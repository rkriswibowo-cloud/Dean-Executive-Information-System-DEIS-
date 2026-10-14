<?php
namespace App\Services;

use App\Core\Database;

class NotificationService {
    public static function send(?int $userId, string $title, string $message, string $type = 'alert', ?string $targetUrl = null): int {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `target_url`, `is_read`, `created_at`)
                VALUES (:user_id, :title, :message, :type, :target_url, 0, NOW())
            ");
            $stmt->execute([
                'user_id'    => $userId,
                'title'      => $title,
                'message'    => $message,
                'type'       => $type,
                'target_url' => $targetUrl
            ]);
            return (int)$db->lastInsertId();
        } catch (\Throwable $e) {
            error_log("NotificationService::send error: " . $e->getMessage());
            return 0;
        }
    }

    public static function getUnread(?int $userId, int $limit = 5): array {
        try {
            $db = Database::getConnection();
            $limit = max(1, min(50, (int)$limit));
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
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log("NotificationService::getUnread error: " . $e->getMessage());
            return [];
        }
    }

    public static function countUnread(?int $userId): int {
        try {
            $db = Database::getConnection();
            if ($userId) {
                $stmt = $db->prepare("
                    SELECT COUNT(*) as total FROM `notifications` 
                    WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0
                ");
                $stmt->execute(['user_id' => $userId]);
            } else {
                $stmt = $db->query("SELECT COUNT(*) as total FROM `notifications` WHERE is_read = 0");
            }
            $res = $stmt->fetch();
            return (int)($res['total'] ?? 0);
        } catch (\Throwable $e) {
            error_log("NotificationService::countUnread error: " . $e->getMessage());
            return 0;
        }
    }

    public static function markAsRead(int $id): bool {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE `notifications` SET is_read = 1 WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (\Throwable $e) {
            error_log("NotificationService::markAsRead error: " . $e->getMessage());
            return false;
        }
    }

    public static function markAllAsRead(?int $userId): bool {
        try {
            $db = Database::getConnection();
            if ($userId) {
                $stmt = $db->prepare("UPDATE `notifications` SET is_read = 1 WHERE user_id = :user_id OR user_id IS NULL");
                return $stmt->execute(['user_id' => $userId]);
            } else {
                return (bool)$db->exec("UPDATE `notifications` SET is_read = 1");
            }
        } catch (\Throwable $e) {
            error_log("NotificationService::markAllAsRead error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Auto-sync live system alerts into notifications if there are pending items
     * Ensures executive users always have relevant and fresh notifications.
     */
    public static function syncLiveAlerts(?int $userId = null): void {
        try {
            $db = Database::getConnection();

            // 1. Check pending approvals
            $pendingCount = (int)$db->query("SELECT COUNT(*) FROM `approvals` WHERE `status` = 'Pending'")->fetchColumn();
            if ($pendingCount > 0) {
                $exists = (int)$db->query("
                    SELECT COUNT(*) FROM `notifications` 
                    WHERE `type` = 'approval' AND `is_read` = 0 AND `created_at` >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ")->fetchColumn();
                if ($exists === 0) {
                    self::send(
                        null,
                        "Permohonan Approval Menunggu ({$pendingCount})",
                        "Terdapat {$pendingCount} berkas/kegiatan yang memerlukan validasi persetujuan Dekan.",
                        'approval',
                        'command-center/approvals'
                    );
                }
            }

            // 2. Check critical unresolved alerts
            $alertCount = (int)$db->query("SELECT COUNT(*) FROM `critical_alerts` WHERE `is_resolved` = 0")->fetchColumn();
            if ($alertCount > 0) {
                $exists = (int)$db->query("
                    SELECT COUNT(*) FROM `notifications` 
                    WHERE `type` = 'alert' AND `is_read` = 0 AND `created_at` >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ")->fetchColumn();
                if ($exists === 0) {
                    self::send(
                        null,
                        "Peringatan Radar Command Center",
                        "Terdapat {$alertCount} anomali indikator mutu/akademik yang membutuhkan perhatian eksekutif.",
                        'alert',
                        'command-center'
                    );
                }
            }

            // 3. Check RTL action items overdue
            $overdueCount = (int)$db->query("
                SELECT COUNT(*) FROM `action_items` 
                WHERE `status` = 'Terlambat' OR (`deadline` < CURDATE() AND `status` NOT IN ('Selesai', 'Dibatalkan'))
            ")->fetchColumn();
            if ($overdueCount > 0) {
                $exists = (int)$db->query("
                    SELECT COUNT(*) FROM `notifications` 
                    WHERE `type` = 'rtl' AND `is_read` = 0 AND `created_at` >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ")->fetchColumn();
                if ($exists === 0) {
                    self::send(
                        null,
                        "RTL Rapat Terlambat ({$overdueCount})",
                        "Terdapat {$overdueCount} item tindak lanjut rapat yang melewati batas waktu penyelesaian.",
                        'rtl',
                        'meetings/rtl'
                    );
                }
            }
        } catch (\Throwable $e) {
            error_log("NotificationService::syncLiveAlerts error: " . $e->getMessage());
        }
    }
}
