<?php
namespace App\Services;

use App\Core\Database;
use PDO;

class CommandCenterService {
    /**
     * Get Executive "My Attention" summary card counts for Dekan
     */
    public static function getMyAttention(): array {
        $db = Database::getConnection();

        // 1. Pending Approvals
        $stmt = $db->query("SELECT COUNT(*) as count FROM approvals WHERE status = 'Pending'");
        $pendingApprovals = (int)$stmt->fetch()['count'];

        // 2. Overdue RTL (Tindak Lanjut Rapat)
        $stmt = $db->query("
            SELECT COUNT(*) as count FROM action_items 
            WHERE (status = 'Terlambat' OR (deadline < CURDATE() AND status NOT IN ('Selesai', 'Dibatalkan')))
        ");
        $overdueRtl = (int)$stmt->fetch()['count'];

        // 3. Problematic Lecturers (Kehadiran < 75% or BKD Belum Memenuhi)
        $stmt = $db->query("
            SELECT COUNT(*) as count FROM lecturers 
            WHERE attendance_percentage < 75.00 OR bkd_status = 'Belum Memenuhi'
        ");
        $problematicLecturers = (int)$stmt->fetch()['count'];

        // 4. Critical & Warning Students (DO risk / GPA < 2.0 / Attendance < 70%)
        $stmt = $db->query("
            SELECT COUNT(*) as count FROM students 
            WHERE risk_status IN ('Critical', 'Warning') AND status = 'Aktif'
        ");
        $atRiskStudents = (int)$stmt->fetch()['count'];

        // 5. Critical Deadlines (Accreditation expiring within 180 days or MoU expiring within 30 days)
        $stmt = $db->query("
            SELECT COUNT(*) as count FROM accreditations 
            WHERE valid_until <= DATE_ADD(CURDATE(), INTERVAL 180 DAY) AND status != 'Aman'
        ");
        $urgentAccreditations = (int)$stmt->fetch()['count'];

        $stmt = $db->query("
            SELECT COUNT(*) as count FROM cooperations 
            WHERE end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND status = 'Akan Berakhir'
        ");
        $urgentCooperations = (int)$stmt->fetch()['count'];

        $urgentDeadlines = $urgentAccreditations + $urgentCooperations;

        // 6. Incomplete / Problematic Meeting Documents
        $stmt = $db->query("
            SELECT COUNT(*) as count FROM meetings m
            WHERE m.status = 'Selesai' AND m.id NOT IN (
                SELECT DISTINCT meeting_id FROM meeting_documents WHERE document_type = 'Notulensi'
            )
        ");
        $incompleteMeetings = (int)$stmt->fetch()['count'];

        $totalAttention = $pendingApprovals + $overdueRtl + $problematicLecturers + $atRiskStudents + $urgentDeadlines;

        return [
            'total'                 => $totalAttention,
            'pending_approvals'     => $pendingApprovals,
            'overdue_rtl'           => $overdueRtl,
            'problematic_lecturers' => $problematicLecturers,
            'at_risk_students'      => $atRiskStudents,
            'urgent_deadlines'      => $urgentDeadlines,
            'incomplete_meetings'   => $incompleteMeetings
        ];
    }

    /**
     * Synchronize critical_alerts table with real-time live database state
     */
    public static function syncCriticalAlerts(): void {
        try {
            $db = Database::getConnection();

            // 1. Synchronize Students at risk
            $stmt = $db->query("SELECT id, name, current_gpa, semester FROM students WHERE risk_status = 'Critical' AND status = 'Aktif'");
            $criticalStudents = $stmt->fetchAll();
            $studentCount = count($criticalStudents);

            if ($studentCount === 0) {
                $db->exec("UPDATE critical_alerts SET is_resolved = 1 WHERE alert_type = 'Mahasiswa'");
            } else {
                $studentNames = implode(', ', array_map(fn($s) => $s['name'] . " (Sem {$s['semester']}, IPK {$s['current_gpa']})", $criticalStudents));
                $title = "{$studentCount} Mahasiswa Terindikasi Risiko Kritis Drop Out (DO)";
                $desc = "Mahasiswa {$studentNames} membutuhkan intervensi segera.";
                
                $stmt = $db->query("SELECT id FROM critical_alerts WHERE alert_type = 'Mahasiswa'");
                $existing = $stmt->fetch();
                if ($existing) {
                    $upd = $db->prepare("UPDATE critical_alerts SET is_resolved = 0, title = :t, description = :d WHERE id = :id");
                    $upd->execute(['t' => $title, 'd' => $desc, 'id' => $existing['id']]);
                } else {
                    $ins = $db->prepare("INSERT INTO critical_alerts (alert_type, severity, title, description, target_url, is_resolved) VALUES ('Mahasiswa', 'Critical', :t, :d, 'students/early-warning', 0)");
                    $ins->execute(['t' => $title, 'd' => $desc]);
                }
            }

            // 2. Synchronize Problematic Lecturers (Attendance < 75% or BKD Belum Memenuhi)
            $stmt = $db->query("SELECT id, name, attendance_percentage, bkd_status FROM lecturers WHERE (attendance_percentage < 75.00 OR bkd_status = 'Belum Memenuhi') AND status = 'Aktif'");
            $problemLecturers = $stmt->fetchAll();
            $lecCount = count($problemLecturers);

            if ($lecCount === 0) {
                $db->exec("UPDATE critical_alerts SET is_resolved = 1 WHERE alert_type = 'Dosen'");
            } else {
                $lecNames = implode(', ', array_map(fn($l) => $l['name'] . ($l['bkd_status'] === 'Belum Memenuhi' ? ' (BKD Belum Memenuhi)' : ' (Presensi ' . $l['attendance_percentage'] . '%)'), $problemLecturers));
                $title = "{$lecCount} Dosen Memiliki Beban BKD & Kehadiran di Bawah Batas Minimum";
                $desc = "Dosen {$lecNames} berpotensi tidak lulus BKD semester ini.";

                $stmt = $db->query("SELECT id FROM critical_alerts WHERE alert_type = 'Dosen'");
                $existing = $stmt->fetch();
                if ($existing) {
                    $upd = $db->prepare("UPDATE critical_alerts SET is_resolved = 0, title = :t, description = :d WHERE id = :id");
                    $upd->execute(['t' => $title, 'd' => $desc, 'id' => $existing['id']]);
                }
            }

            // 3. Synchronize Overdue RTL
            $stmt = $db->query("SELECT id, item_code, description FROM action_items WHERE (status = 'Terlambat' OR (deadline < CURDATE() AND status NOT IN ('Selesai', 'Dibatalkan')))");
            $overdueRtls = $stmt->fetchAll();
            $rtlCount = count($overdueRtls);

            if ($rtlCount === 0) {
                $db->exec("UPDATE critical_alerts SET is_resolved = 1 WHERE alert_type = 'RTL'");
            } else {
                $title = "Tindak Lanjut Rapat (RTL) Terlambat ({$rtlCount} Item)";
                $desc = "RTL " . $overdueRtls[0]['item_code'] . " (" . substr($overdueRtls[0]['description'], 0, 50) . ") melewati deadline dan belum diserahkan.";
                $stmt = $db->query("SELECT id FROM critical_alerts WHERE alert_type = 'RTL'");
                $existing = $stmt->fetch();
                if ($existing) {
                    $upd = $db->prepare("UPDATE critical_alerts SET is_resolved = 0, title = :t, description = :d WHERE id = :id");
                    $upd->execute(['t' => $title, 'd' => $desc, 'id' => $existing['id']]);
                }
            }

            // 4. Synchronize Accreditation expiring within 90 days
            $stmt = $db->query("SELECT a.id, sp.name as program_name, DATEDIFF(a.valid_until, CURDATE()) as days_left FROM accreditations a JOIN study_programs sp ON a.study_program_id = sp.id WHERE a.valid_until <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND a.status != 'Aman'");
            $expiringAcc = $stmt->fetchAll();
            if (count($expiringAcc) === 0) {
                $db->exec("UPDATE critical_alerts SET is_resolved = 1 WHERE alert_type = 'Akreditasi'");
            }

            // 5. Synchronize Cooperations expiring within 30 days
            $stmt = $db->query("SELECT id, partner_name FROM cooperations WHERE end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND status = 'Akan Berakhir'");
            $expiringCoop = $stmt->fetchAll();
            if (count($expiringCoop) === 0) {
                $db->exec("UPDATE critical_alerts SET is_resolved = 1 WHERE alert_type = 'Deadline'");
            }
        } catch (\Exception $e) {
            error_log("syncCriticalAlerts error: " . $e->getMessage());
        }
    }

    /**
     * Get Critical Alerts List
     */
    public static function getCriticalAlerts(): array {
        self::syncCriticalAlerts();

        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT * FROM critical_alerts 
            WHERE is_resolved = 0 
            ORDER BY 
                CASE severity 
                    WHEN 'Critical' THEN 1 
                    WHEN 'Warning' THEN 2 
                    WHEN 'Attention' THEN 3 
                    ELSE 4 
                END, id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get Deadline Radar Items
     */
    public static function getDeadlineRadar(): array {
        $db = Database::getConnection();
        $items = [];

        // 1. Accreditations
        $stmt = $db->query("
            SELECT a.id, sp.name as program_name, a.institution, a.valid_until, a.current_grade, a.target_grade, a.status,
                   DATEDIFF(a.valid_until, CURDATE()) as days_left
            FROM accreditations a
            JOIN study_programs sp ON a.study_program_id = sp.id
            ORDER BY a.valid_until ASC
        ");
        while ($row = $stmt->fetch()) {
            $items[] = [
                'category'    => 'Akreditasi',
                'title'       => 'Akreditasi ' . $row['program_name'] . ' (' . $row['institution'] . ')',
                'due_date'    => $row['valid_until'],
                'days_left'   => (int)$row['days_left'],
                'status'      => $row['days_left'] < 90 ? 'Kritis' : ($row['days_left'] < 180 ? 'Perhatian' : 'Aman'),
                'target_url'  => 'accreditation'
            ];
        }

        // 2. Action Items (RTL)
        $stmt = $db->query("
            SELECT id, item_code, description, deadline, status, pic_name,
                   DATEDIFF(deadline, CURDATE()) as days_left
            FROM action_items
            WHERE status NOT IN ('Selesai', 'Dibatalkan')
            ORDER BY deadline ASC
            LIMIT 10
        ");
        while ($row = $stmt->fetch()) {
            $items[] = [
                'category'    => 'RTL Rapat',
                'title'       => $row['item_code'] . ': ' . substr($row['description'], 0, 70) . '...',
                'due_date'    => $row['deadline'],
                'days_left'   => (int)$row['days_left'],
                'status'      => $row['days_left'] < 0 ? 'Terlambat' : ($row['days_left'] <= 3 ? 'Perhatian' : 'Proses'),
                'target_url'  => 'meetings/rtl'
            ];
        }

        // 3. Cooperations expiring soon
        $stmt = $db->query("
            SELECT id, partner_name, end_date, type,
                   DATEDIFF(end_date, CURDATE()) as days_left
            FROM cooperations
            WHERE end_date >= CURDATE()
            ORDER BY end_date ASC
            LIMIT 5
        ");
        while ($row = $stmt->fetch()) {
            $items[] = [
                'category'    => 'Kerja Sama',
                'title'       => 'Masa Berlaku ' . $row['type'] . ' - ' . $row['partner_name'],
                'due_date'    => $row['end_date'],
                'days_left'   => (int)$row['days_left'],
                'status'      => $row['days_left'] <= 30 ? 'Akan Berakhir' : 'Aktif',
                'target_url'  => 'cooperations'
            ];
        }

        // Sort all by days_left
        usort($items, function($a, $b) {
            return $a['days_left'] <=> $b['days_left'];
        });

        return $items;
    }

    /**
     * Get Pending Approvals
     */
    public static function getPendingApprovals(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT a.*, sp.name as program_name
            FROM approvals a
            LEFT JOIN study_programs sp ON a.study_program_id = sp.id
            WHERE a.status = 'Pending'
            ORDER BY a.submission_date DESC, a.id DESC
        ");
        return $stmt->fetchAll();
    }
}
