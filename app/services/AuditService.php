<?php
namespace App\Services;

use App\Core\Database;
use App\Helpers\AuthHelper;

class AuditService {
    public static function log(string $action, string $module, ?string $recordId = null, $oldValues = null, $newValues = null): void {
        try {
            $db = Database::getConnection();
            $userId = AuthHelper::id();
            $user = AuthHelper::user();
            $username = $user ? $user['username'] : 'system';

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 250);

            $oldJson = $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
            $newJson = $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;

            $stmt = $db->prepare("
                INSERT INTO `audit_logs` (`user_id`, `username`, `action`, `module`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`)
                VALUES (:user_id, :username, :action, :module, :record_id, :old_values, :new_values, :ip_address, :user_agent)
            ");

            $stmt->execute([
                'user_id'    => $userId,
                'username'   => $username,
                'action'     => strtoupper($action),
                'module'     => $module,
                'record_id'  => $recordId,
                'old_values' => $oldJson,
                'new_values' => $newJson,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ]);
        } catch (\Exception $e) {
            // Silently fail to not block primary business transactions if log fails
            error_log("Audit log failed: " . $e->getMessage());
        }
    }
}
