<?php
namespace App\Services\Obe;

use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Gerbang baca tunggal ke database OBE.
 *
 * PDO sengaja tidak diekspos agar konsumen tidak dapat menjalankan operasi
 * tulis. Di produksi, pertahanan utama tetap akun MySQL berprivilege SELECT.
 */
final class ObeReadConnection
{
    private ?PDO $pdo = null;
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? require __DIR__ . '/../../config/database.php';
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $this->assertReadOnlySql($sql);
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $rows = $this->fetchAll($sql, $params);
        return $rows[0] ?? null;
    }

    public function scalar(string $sql, array $params = []): mixed
    {
        $this->assertReadOnlySql($sql);
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function databaseName(): string
    {
        return (string)($this->config['obe_dbname'] ?? 'obe_cpl');
    }

    private function connection(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $host = (string)($this->config['host'] ?? 'localhost');
        $port = (int)($this->config['port'] ?? 3306);
        $database = $this->databaseName();
        $charset = (string)($this->config['charset'] ?? 'utf8mb4');
        $username = (string)($this->config['obe_username'] ?? $this->config['username'] ?? '');
        $password = (string)($this->config['obe_password'] ?? $this->config['password'] ?? '');

        if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
            throw new RuntimeException('Nama database OBE tidak valid.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci",
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Koneksi read-only OBE gagal: ' . $e->getMessage(), 0, $e);
        }

        return $this->pdo;
    }

    private function assertReadOnlySql(string $sql): void
    {
        $normalized = trim($sql);
        if (!preg_match('/^SELECT\b/i', $normalized)) {
            throw new InvalidArgumentException('Koneksi OBE hanya mengizinkan perintah SELECT.');
        }

        $blocked = '/\b(INSERT|UPDATE|DELETE|REPLACE|ALTER|DROP|CREATE|TRUNCATE|GRANT|REVOKE|CALL|LOAD|LOCK|UNLOCK|OUTFILE|DUMPFILE)\b|\bFOR\s+UPDATE\b/i';
        if (preg_match($blocked, $normalized)) {
            throw new InvalidArgumentException('Query OBE mengandung operasi yang tidak diizinkan.');
        }
    }
}
