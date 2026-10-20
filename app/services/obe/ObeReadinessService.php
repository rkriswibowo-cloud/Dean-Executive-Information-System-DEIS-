<?php
namespace App\Services\Obe;

use App\Services\ObeSyncService;
use InvalidArgumentException;
use Throwable;

final class ObeReadinessService
{
    public function __construct(private ?ObeReadConnection $obe = null)
    {
        $this->obe ??= new ObeReadConnection();
    }

    public function report(): array
    {
        $health = (new ObeHealthService($this->obe))->check();
        $checks = [];
        $checks[] = $this->check(
            'Koneksi OBE',
            (bool)$health['available'],
            $health['available']
                ? "Terhubung ke {$health['database']} ({$health['latency_ms']} ms)."
                : 'Gagal: ' . ($health['error'] ?? 'tidak diketahui'),
            true
        );

        $writeBlocked = false;
        try {
            $this->obe->scalar('UPDATE users SET id=id');
        } catch (InvalidArgumentException) {
            $writeBlocked = true;
        } catch (Throwable) {
            $writeBlocked = false;
        }
        $checks[] = $this->check(
            'Guard read-only', $writeBlocked,
            $writeBlocked ? 'Operasi tulis ditolak sebelum mencapai database.' : 'Guard operasi tulis tidak bekerja.', true
        );

        $config = require __DIR__ . '/../../config/database.php';
        $dedicatedAccount = ($config['obe_username'] ?? null) !== ($config['username'] ?? null);
        $checks[] = $this->check(
            'Akun database khusus', $dedicatedAccount,
            $dedicatedAccount
                ? 'OBE menggunakan kredensial terpisah dari koneksi utama DEIS.'
                : 'Lingkungan lokal masih memakai akun database utama. Atur OBE_DB_USERNAME dengan privilege SELECT saja sebelum produksi.',
            false,
            $dedicatedAccount ? null : 'warning'
        );

        $syncBlocked = false;
        try {
            (new ObeSyncService())->syncCoursesFromObe();
        } catch (\LogicException) {
            $syncBlocked = true;
        } catch (Throwable) {
            $syncBlocked = false;
        }
        $checks[] = $this->check(
            'Sinkronisasi lama', $syncBlocked,
            $syncBlocked ? 'Penyalinan master ke DEIS telah dinonaktifkan.' : 'Metode sinkronisasi lama masih dapat dijalankan.', true
        );

        try {
            $mapping = (new ObeMappingService($this->obe))->summary();
            $mapped = $unmapped = $conflicts = 0;
            foreach ($mapping as $row) {
                $mapped += (int)($row['mapped'] ?? 0);
                $unmapped += (int)($row['unmapped'] ?? 0);
                $conflicts += (int)($row['conflict'] ?? 0);
            }
            $checks[] = $this->check(
                'Pemetaan identitas', $mapped > 0,
                "{$mapped} mapped, {$unmapped} unmapped, {$conflicts} conflict.", true,
                $conflicts > 0 ? 'warning' : null
            );
        } catch (Throwable $e) {
            $checks[] = $this->check('Pemetaan identitas', false, $e->getMessage(), true);
        }

        try {
            $latest = (new ObeSnapshotService($this->obe))->latest(1)[0] ?? null;
            $checks[] = $this->check(
                'Snapshot historis', $latest !== null,
                $latest
                    ? "Snapshot terakhir #{$latest['id']} pada {$latest['created_at']} ({$latest['record_count']} record)."
                    : 'Belum ada snapshot permanen; buat dari halaman OBE Live.',
                false,
                $latest === null ? 'warning' : null
            );
        } catch (Throwable $e) {
            $checks[] = $this->check('Snapshot historis', false, $e->getMessage(), false, 'warning');
        }

        $criticalFailures = count(array_filter($checks, static fn(array $c): bool => $c['critical'] && $c['status'] === 'failed'));
        $warnings = count(array_filter($checks, static fn(array $c): bool => $c['status'] === 'warning'));

        return [
            'status' => $criticalFailures > 0 ? 'not_ready' : ($warnings > 0 ? 'ready_with_warnings' : 'ready'),
            'checked_at' => (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format(DATE_ATOM),
            'critical_failures' => $criticalFailures,
            'warnings' => $warnings,
            'checks' => $checks,
        ];
    }

    private function check(string $name, bool $passed, string $detail, bool $critical, ?string $override = null): array
    {
        return [
            'name' => $name,
            'status' => $override ?? ($passed ? 'passed' : 'failed'),
            'detail' => $detail,
            'critical' => $critical,
        ];
    }
}
