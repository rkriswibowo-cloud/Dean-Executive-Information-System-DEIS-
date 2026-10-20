<?php
namespace App\Services\Obe;

use Throwable;

final class ObeHealthService
{
    public function __construct(private ?ObeReadConnection $connection = null)
    {
        $this->connection ??= new ObeReadConnection();
    }

    public function check(): array
    {
        $startedAt = microtime(true);
        try {
            $database = (string)$this->connection->scalar('SELECT DATABASE()');
            $courseCount = (int)$this->connection->scalar('SELECT COUNT(*) FROM mata_kuliah');
            return [
                'available' => true,
                'database' => $database,
                'course_count' => $courseCount,
                'latency_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'available' => false,
                'database' => $this->connection->databaseName(),
                'course_count' => null,
                'latency_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'error' => $e->getMessage(),
            ];
        }
    }
}
