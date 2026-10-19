<?php
// update_academic_features.php
use App\Core\Database;

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/core/Database.php';

try {
    $db = Database::getConnection();
    echo "Connecting to database...\n";

    // 1. Create table class_meetings (Log Pertemuan Perkuliahan / Jurnal Mengajar)
    $db->exec("CREATE TABLE IF NOT EXISTS `class_meetings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `class_id` INT NOT NULL,
        `meeting_no` INT NOT NULL,
        `meeting_date` DATE NOT NULL,
        `start_time` TIME NULL,
        `end_time` TIME NULL,
        `room` VARCHAR(50) NULL,
        `topic` VARCHAR(255) NOT NULL,
        `subtopic` TEXT NULL,
        `delivery_method` ENUM('Tatap Muka', 'Daring', 'Hybrid', 'Praktikum / Lab') DEFAULT 'Tatap Muka',
        `student_present` INT NOT NULL DEFAULT 0,
        `student_total` INT NOT NULL DEFAULT 0,
        `attendance_percentage` DECIMAL(5,2) DEFAULT 0,
        `status` ENUM('Terlaksana', 'Direncanakan', 'Pengganti / Reschedule', 'Batal') DEFAULT 'Terlaksana',
        `lecturer_notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "[OK] Table class_meetings created/verified.\n";

    // 2. Add columns to courses table if not exists (for comprehensive RPS monitoring)
    $columnsToAdd = [
        "cpl_status" => "VARCHAR(100) DEFAULT 'Terpenuhi'",
        "case_method_flag" => "TINYINT(1) DEFAULT 0",
        "rps_document_url" => "VARCHAR(255) NULL",
        "rps_updated_at" => "DATE NULL",
        "rps_notes" => "TEXT NULL"
    ];

    foreach ($columnsToAdd as $col => $definition) {
        try {
            $db->exec("ALTER TABLE `courses` ADD COLUMN `{$col}` {$definition}");
            echo "[OK] Added column `{$col}` to `courses`.\n";
        } catch (Exception $e) {
            // Already exists, ignore
        }
    }

    // 3. Seed data for class_meetings if empty
    $countMeetings = (int)$db->query("SELECT COUNT(*) FROM `class_meetings`")->fetchColumn();
    if ($countMeetings === 0) {
        echo "Seeding initial class_meetings data...\n";

        // Ambil kelas yang ada
        $classes = $db->query("SELECT cl.*, c.name as course_name FROM classes cl JOIN courses c ON cl.course_id = c.id")->fetchAll();
        $sampleTopics = [
            1 => ["Pengantar & Kontrak Kuliah", "Silabus, orientasi sistem, dan referensi dasar"],
            2 => ["Konsep Dasar & Teori Fundamental", "Review literatur dan definisi standar"],
            3 => ["Arsitektur & Model Referensi", "Lapisan arsitektur, diagram struktur"],
            4 => ["Metodologi & Analisis Kebutuhan", "Teknik elisitasi dan spesifikasi dokumen"],
            5 => ["Perancangan Sistem & Logika Kerja", "Data flow, schema relational, dan use cases"],
            6 => ["Studi Kasus 1 (Case Method Diskusi)", "Pembedahan kasus industri dan problem solving"],
            7 => ["Review Materi & Persiapan UTS", "Kuis komprehensif dan sesi tanya jawab interaktif"],
            8 => ["Ujian Tengah Semester (UTS)", "Evaluasi pencapaian kompetensi tengah semester"],
            9 => ["Pengembangan Tingkat Lanjut Bagian I", "Integrasi komponen dan implementasi algoritma"],
            10 => ["Pengembangan Tingkat Lanjut Bagian II", "Optimasi query, caching, dan keamanan"],
            11 => ["Project-Based Learning: Inisiasi", "Pembagian kelompok studi riil dan milestone"],
            12 => ["Project-Based Learning: Supervisi", "Review berkala, validasi pengujian sistem"],
            13 => ["Pengujian & Evaluasi Kinerja", "Black-box, usability, dan acceptance testing"],
            14 => ["Presentasi Hasil Proyek Mahasiswa", "Demo aplikasi dan penilaian peer-review"],
            15 => ["Review CPL & Refleksi Pembelajaran", "Evaluasi pencapaian IKU-7 dan standar mutu"],
            16 => ["Ujian Akhir Semester (UAS)", "Penilaian akhir kompetensi portofolio"]
        ];

        $insertStmt = $db->prepare("INSERT INTO `class_meetings` 
            (`class_id`, `meeting_no`, `meeting_date`, `start_time`, `end_time`, `room`, `topic`, `subtopic`, `delivery_method`, `student_present`, `student_total`, `attendance_percentage`, `status`, `lecturer_notes`) 
            VALUES (:class_id, :meeting_no, :meeting_date, :start_time, :end_time, :room, :topic, :subtopic, :delivery_method, :student_present, :student_total, :attendance_percentage, :status, :lecturer_notes)");

        $startDate = strtotime('2026-02-16'); // Semester Genap 2025/2026

        foreach ($classes as $cls) {
            $held = min(16, (int)$cls['total_held_meetings']);
            $totalMhs = 35; // Rata-rata per rombel

            for ($m = 1; $m <= 16; $m++) {
                $mDate = date('Y-m-d', strtotime("+" . (($m - 1) * 7) . " days", $startDate));
                $topicInfo = $sampleTopics[$m] ?? ["Materi Pertemuan ke-{$m}", "Subtopik pembahasan lanjutan"];
                
                if ($m <= $held) {
                    $status = 'Terlaksana';
                    // Generate realistis attendance
                    $present = rand(28, 34);
                    $pct = round(($present / $totalMhs) * 100, 1);
                    $notes = "Pertemuan berjalan tertib sesuai RPS. Diskusi interaktif terlaksana.";
                } else if ($cls['problem_flag'] && $m == $held + 1) {
                    $status = 'Batal';
                    $present = 0;
                    $pct = 0;
                    $notes = "Dosen berhalangan hadir dinas luar kota, perlu koordinasi kuliah pengganti.";
                } else {
                    $status = 'Direncanakan';
                    $present = 0;
                    $pct = 0;
                    $notes = "Terjadwal reguler";
                }

                $insertStmt->execute([
                    'class_id' => $cls['id'],
                    'meeting_no' => $m,
                    'meeting_date' => $mDate,
                    'start_time' => '08:00:00',
                    'end_time' => '10:30:00',
                    'room' => $cls['room'] ?: 'Lab Komputer 301',
                    'topic' => $topicInfo[0],
                    'subtopic' => $topicInfo[1],
                    'delivery_method' => ($m == 6 || $m == 11) ? 'Hybrid' : 'Tatap Muka',
                    'student_present' => $present,
                    'student_total' => $totalMhs,
                    'attendance_percentage' => $pct,
                    'status' => $status,
                    'lecturer_notes' => $notes
                ]);
            }
        }
        echo "[OK] Initial class_meetings seeded successfully.\n";
    }

    // 4. Update existing courses with sample Case Method and sample RPS documents
    $db->exec("UPDATE `courses` SET 
        `case_method_flag` = 1,
        `cpl_status` = 'Memenuhi Standar OBE',
        `rps_document_url` = 'https://docs.google.com/viewer?url=rps-fti-contoh.pdf',
        `rps_updated_at` = '2026-01-20',
        `rps_notes` = 'RPS telah disesuaikan dengan kurikulum OBE 2024 dan IKU-7 Case Method'
        WHERE `id` IN (1, 2, 3, 5)");

    $db->exec("UPDATE `courses` SET 
        `case_method_flag` = 0,
        `cpl_status` = 'Revisi Rubrik Penilaian',
        `rps_document_url` = NULL,
        `rps_updated_at` = '2025-08-15',
        `rps_notes` = 'Perlu melengkapi instrumen penilaian portofolio dan pemutakhiran pustaka 5 tahun terakhir'
        WHERE `id` IN (4, 6)");

    echo "Update migration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
