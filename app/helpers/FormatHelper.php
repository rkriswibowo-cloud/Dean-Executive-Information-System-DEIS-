<?php
namespace App\Helpers;

class FormatHelper {
    private static array $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    public static function indonesianDate(?string $date, bool $withTime = false): string {
        if (!$date || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($date);
        if (!$timestamp) return $date;

        $day = date('j', $timestamp);
        $month = self::$months[(int)date('n', $timestamp)] ?? date('F', $timestamp);
        $year = date('Y', $timestamp);

        $result = "{$day} {$month} {$year}";
        if ($withTime) {
            $result .= " " . date('H:i', $timestamp) . " WIB";
        }
        return $result;
    }

    public static function currency($number, string $prefix = 'Rp '): string {
        if (!is_numeric($number)) return $prefix . '0';
        return $prefix . number_format((float)$number, 0, ',', '.');
    }

    public static function percent($number, int $decimals = 1): string {
        if (!is_numeric($number)) return '0%';
        return number_format((float)$number, $decimals, ',', '.') . '%';
    }

    public static function statusBadge(?string $status): string {
        if (!$status) return '<span class="badge bg-secondary">Unknown</span>';

        $map = [
            'Success'        => 'badge bg-success-subtle text-success border border-success-subtle',
            'Tercapai'       => 'badge bg-success-subtle text-success border border-success-subtle',
            'Selesai'        => 'badge bg-success-subtle text-success border border-success-subtle',
            'Approved'       => 'badge bg-success-subtle text-success border border-success-subtle',
            'Aktif'          => 'badge bg-success-subtle text-success border border-success-subtle',
            'Memenuhi'       => 'badge bg-success-subtle text-success border border-success-subtle',
            'Unggul'         => 'badge bg-success-subtle text-success border border-success-subtle',
            'Aman'           => 'badge bg-success-subtle text-success border border-success-subtle',
            'Optimal'        => 'badge bg-success-subtle text-success border border-success-subtle',
            'Lengkap'        => 'badge bg-success-subtle text-success border border-success-subtle',

            'Attention'      => 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'Proses'         => 'badge bg-info-subtle text-info border border-info-subtle',
            'Berjalan'       => 'badge bg-info-subtle text-info border border-info-subtle',
            'Pending'        => 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'Diserahkan'     => 'badge bg-info-subtle text-info border border-info-subtle',
            'Baik Sekali'    => 'badge bg-info-subtle text-info border border-info-subtle',
            'Perhatian'      => 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'Akan Berakhir'  => 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'Sedang Berlangsung' => 'badge bg-primary-subtle text-primary border border-primary-subtle',

            'Warning'        => 'badge bg-warning-subtle text-warning border border-warning-subtle',
            'Cukup'          => 'badge bg-warning-subtle text-warning border border-warning-subtle',
            'Baik'           => 'badge bg-info-subtle text-info border border-info-subtle',
            'Dalam Penilaian'=> 'badge bg-info-subtle text-info border border-info-subtle',
            'Revisi'         => 'badge bg-warning-subtle text-warning border border-warning-subtle',

            'Critical'       => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'Belum Tercapai' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'Terlambat'      => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'Rejected'       => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'Belum Memenuhi' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'Kritis'         => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'Kadaluarsa'     => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'Dibatalkan'     => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
            'Belum Mulai'    => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
            'Terjadwal'      => 'badge bg-primary-subtle text-primary border border-primary-subtle',
            'Belum Lengkap'  => 'badge bg-danger-subtle text-danger border border-danger-subtle',
        ];

        $class = $map[$status] ?? 'badge bg-secondary-subtle text-secondary border border-secondary-subtle';
        return '<span class="' . $class . ' px-2 py-1 fs-6">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    public static function priorityBadge(?string $priority): string {
        switch ($priority) {
            case 'Tinggi':
                return '<span class="badge bg-danger text-white"><i class="ti ti-flame me-1"></i>Tinggi</span>';
            case 'Sedang':
                return '<span class="badge bg-warning text-dark"><i class="ti ti-alert-triangle me-1"></i>Sedang</span>';
            case 'Rendah':
                return '<span class="badge bg-secondary text-white"><i class="ti ti-arrow-down me-1"></i>Rendah</span>';
            default:
                return '<span class="badge bg-light text-dark">' . htmlspecialchars($priority ?? '-', ENT_QUOTES, 'UTF-8') . '</span>';
        }
    }

    public static function timeAgo(?string $datetime): string {
        if (!$datetime) return '-';
        $timestamp = strtotime($datetime);
        if (!$timestamp) return $datetime;

        $diff = time() - $timestamp;
        if ($diff < 60) return 'Baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit yang lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam yang lalu';
        if ($diff < 2592000) return floor($diff / 86400) . ' hari yang lalu';
        return self::indonesianDate($datetime);
    }
}
