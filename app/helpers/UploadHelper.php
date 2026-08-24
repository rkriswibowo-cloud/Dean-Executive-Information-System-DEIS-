<?php
namespace App\Helpers;

use App\Core\Database;
use Exception;

class UploadHelper {
    private static array $allowedMimes = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip'
    ];

    public static function upload(array $file, string $module = 'general', ?int $recordId = null): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid parameters.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error code: ' . $file['error']);
        }

        if ($file['size'] > 15 * 1024 * 1024) { // 15MB limit
            throw new Exception('Ukuran file melebihi batas maksimal 15MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, self::$allowedMimes)) {
            throw new Exception('Tipe file tidak diizinkan (' . $mime . '). Hanya PDF, Word, Excel, PPT, Gambar, dan ZIP.');
        }

        $ext = self::$allowedMimes[$mime];
        $originalName = basename($file['name']);
        $storedName = $module . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $targetDir = dirname(__DIR__, 2) . '/storage/' . $module;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . '/' . $storedName;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Gagal menyimpan file ke penyimpanan server.');
        }

        $relativePath = 'storage/' . $module . '/' . $storedName;

        // Record in files table
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `files` (`original_name`, `stored_name`, `file_path`, `file_type`, `file_size`, `module`, `record_id`, `uploaded_by`)
            VALUES (:original_name, :stored_name, :file_path, :file_type, :file_size, :module, :record_id, :uploaded_by)
        ");
        $stmt->execute([
            'original_name' => $originalName,
            'stored_name'   => $storedName,
            'file_path'     => $relativePath,
            'file_type'     => $mime,
            'file_size'     => $file['size'],
            'module'        => $module,
            'record_id'     => $recordId,
            'uploaded_by'   => AuthHelper::id()
        ]);

        $fileId = (int)$db->lastInsertId();

        return [
            'id'            => $fileId,
            'original_name' => $originalName,
            'stored_name'   => $storedName,
            'file_path'     => $relativePath,
            'file_size'     => $file['size'],
            'file_type'     => $mime
        ];
    }
}
