-- =======================================================
-- DEAN EXECUTIVE INFORMATION SYSTEM (DEIS)
-- Relational Database Schema (MySQL 5.7+ / 8.0+)
-- =======================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `app_settings`;
DROP TABLE IF EXISTS `files`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `surveys`;
DROP TABLE IF EXISTS `critical_alerts`;
DROP TABLE IF EXISTS `approvals`;
DROP TABLE IF EXISTS `action_items`;
DROP TABLE IF EXISTS `meeting_documents`;
DROP TABLE IF EXISTS `meeting_participants`;
DROP TABLE IF EXISTS `meetings`;
DROP TABLE IF EXISTS `finances`;
DROP TABLE IF EXISTS `cooperations`;
DROP TABLE IF EXISTS `renstra_programs`;
DROP TABLE IF EXISTS `indicator_evidences`;
DROP TABLE IF EXISTS `indicator_realizations`;
DROP TABLE IF EXISTS `indicator_targets`;
DROP TABLE IF EXISTS `indicators`;
DROP TABLE IF EXISTS `accreditations`;
DROP TABLE IF EXISTS `ami_findings`;
DROP TABLE IF EXISTS `ami_audits`;
DROP TABLE IF EXISTS `spmi_standards`;
DROP TABLE IF EXISTS `guidances`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `lecturers`;
DROP TABLE IF EXISTS `academic_years`;
DROP TABLE IF EXISTS `study_programs`;
DROP TABLE IF EXISTS `faculties`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Roles
CREATE TABLE `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Permissions
CREATE TABLE `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `module` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Role Permissions Pivot
CREATE TABLE `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Users
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `nidn` VARCHAR(20) NULL,
    `avatar` VARCHAR(255) NULL,
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `last_login_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Faculties
CREATE TABLE `faculties` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `dean_name` VARCHAR(150) NOT NULL,
    `vision` TEXT NULL,
    `mission` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Study Programs
CREATE TABLE `study_programs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `faculty_id` INT NOT NULL,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `degree` ENUM('D3', 'D4', 'S1', 'S2', 'S3') DEFAULT 'S1',
    `head_name` VARCHAR(150) NULL,
    `head_user_id` INT NULL,
    `accreditation_status` VARCHAR(50) DEFAULT 'Baik Sekali',
    `accreditation_score` DECIMAL(5,2) DEFAULT 0,
    `accreditation_expire` DATE NULL,
    `student_count` INT DEFAULT 0,
    `lecturer_count` INT DEFAULT 0,
    `target_retention` DECIMAL(5,2) DEFAULT 85.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`faculty_id`) REFERENCES `faculties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Academic Years
CREATE TABLE `academic_years` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(30) NOT NULL, -- e.g. 2025/2026
    `semester` ENUM('Ganjil', 'Genap') NOT NULL,
    `is_active` TINYINT(1) DEFAULT 0,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Lecturers
CREATE TABLE `lecturers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `study_program_id` INT NOT NULL,
    `nidn` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `gender` ENUM('L', 'P') DEFAULT 'L',
    `academic_rank` ENUM('Tenaga Pengajar', 'Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar') DEFAULT 'Lektor',
    `education_level` ENUM('S2', 'S3') DEFAULT 'S2',
    `certification_status` ENUM('Tersertifikasi', 'Belum Tersertifikasi') DEFAULT 'Tersertifikasi',
    `teaching_load_sks` DECIMAL(4,2) DEFAULT 12.00,
    `bkd_status` ENUM('Memenuhi', 'Belum Memenuhi', 'Dalam Penilaian') DEFAULT 'Memenuhi',
    `sinta_score` DECIMAL(8,2) DEFAULT 0,
    `scopus_h_index` INT DEFAULT 0,
    `publication_count` INT DEFAULT 0,
    `pkm_count` INT DEFAULT 0,
    `hki_count` INT DEFAULT 0,
    `books_count` INT DEFAULT 0,
    `attendance_percentage` DECIMAL(5,2) DEFAULT 100.00,
    `status` ENUM('Aktif', 'Tugas Belajar', 'Cuti', 'Pensiun') DEFAULT 'Aktif',
    `phone` VARCHAR(30) NULL,
    `email` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`study_program_id`) REFERENCES `study_programs`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Students
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `study_program_id` INT NOT NULL,
    `nim` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `batch_year` INT NOT NULL,
    `semester` INT NOT NULL DEFAULT 1,
    `current_gpa` DECIMAL(3,2) DEFAULT 3.00,
    `credits_earned` INT DEFAULT 0,
    `attendance_percentage` DECIMAL(5,2) DEFAULT 100.00,
    `status` ENUM('Aktif', 'Cuti', 'Non-Aktif', 'Lulus', 'Drop Out') DEFAULT 'Aktif',
    `risk_status` ENUM('Normal', 'Warning', 'Critical') DEFAULT 'Normal',
    `risk_reason` VARCHAR(255) NULL,
    `scholarship` VARCHAR(100) NULL,
    `organization` VARCHAR(150) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`study_program_id`) REFERENCES `study_programs`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Courses
CREATE TABLE `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `study_program_id` INT NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `sks` INT NOT NULL DEFAULT 3,
    `semester` INT NOT NULL DEFAULT 1,
    `kurikulum_year` VARCHAR(10) DEFAULT '2024',
    `lecturer_id` INT NULL,
    `rps_status` ENUM('Lengkap', 'Belum Lengkap', 'Revisi') DEFAULT 'Lengkap',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`study_program_id`) REFERENCES `study_programs`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Classes & Perkuliahan
CREATE TABLE `classes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `academic_year_id` INT NOT NULL,
    `lecturer_id` INT NOT NULL,
    `class_name` VARCHAR(20) NOT NULL,
    `room` VARCHAR(50) NULL,
    `total_planned_meetings` INT DEFAULT 16,
    `total_held_meetings` INT DEFAULT 0,
    `average_attendance` DECIMAL(5,2) DEFAULT 0,
    `problem_flag` TINYINT(1) DEFAULT 0,
    `problem_notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Guidances (DPA, Skripsi, Magang, MBKM)
CREATE TABLE `guidances` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lecturer_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `type` ENUM('DPA', 'Skripsi', 'Magang', 'MBKM') NOT NULL,
    `title` VARCHAR(255) NULL,
    `progress_percentage` DECIMAL(5,2) DEFAULT 0,
    `status` ENUM('Aktif', 'Selesai', 'Bermasalah', 'Terlambat') DEFAULT 'Aktif',
    `last_guidance_date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. SPMI Standards
CREATE TABLE `spmi_standards` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` ENUM('Pendidikan', 'Penelitian', 'Pengabdian', 'Tata Kelola', 'Kemahasiswaan', 'SDM') NOT NULL,
    `code` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `target_metric` VARCHAR(100) NOT NULL,
    `current_metric` VARCHAR(100) NOT NULL,
    `status` ENUM('Tercapai', 'Proses', 'Belum Tercapai') DEFAULT 'Proses',
    `ppepp_stage` ENUM('Penetapan', 'Pelaksanaan', 'Evaluasi', 'Pengendalian', 'Peningkatan') DEFAULT 'Pelaksanaan',
    `pic` VARCHAR(100) NOT NULL,
    `period_year` INT NOT NULL DEFAULT 2026,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. AMI Audits
CREATE TABLE `ami_audits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `study_program_id` INT NOT NULL,
    `period_year` INT NOT NULL,
    `audit_date` DATE NOT NULL,
    `lead_auditor` VARCHAR(150) NOT NULL,
    `auditor_members` VARCHAR(255) NULL,
    `kts_major_count` INT DEFAULT 0,
    `kts_minor_count` INT DEFAULT 0,
    `ob_count` INT DEFAULT 0,
    `status` ENUM('Terjadwal', 'Berlangsung', 'Selesai', 'RTL Terbuka', 'RTL Selesai') DEFAULT 'Terjadwal',
    `summary` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`study_program_id`) REFERENCES `study_programs`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. AMI Findings
CREATE TABLE `ami_findings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ami_audit_id` INT NOT NULL,
    `standard_id` INT NULL,
    `finding_type` ENUM('KTS Major', 'KTS Minor', 'OB') NOT NULL,
    `description` TEXT NOT NULL,
    `root_cause` TEXT NULL,
    `corrective_action` TEXT NULL,
    `pic` VARCHAR(150) NOT NULL,
    `deadline` DATE NOT NULL,
    `status` ENUM('Open', 'In Progress', 'Submitted', 'Closed') DEFAULT 'Open',
    `verification_notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`ami_audit_id`) REFERENCES `ami_audits`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`standard_id`) REFERENCES `spmi_standards`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Accreditations
CREATE TABLE `accreditations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `study_program_id` INT NOT NULL,
    `institution` VARCHAR(50) DEFAULT 'BAN-PT', -- BAN-PT, LAM-INFOKOM, LAM-PTKES, etc.
    `current_grade` VARCHAR(30) NOT NULL,
    `target_grade` VARCHAR(30) NOT NULL,
    `valid_until` DATE NOT NULL,
    `days_remaining` INT DEFAULT 0,
    `led_progress` DECIMAL(5,2) DEFAULT 0,
    `lkps_progress` DECIMAL(5,2) DEFAULT 0,
    `overall_progress` DECIMAL(5,2) DEFAULT 0,
    `status` ENUM('Aman', 'Perhatian', 'Kritis', 'Expired') DEFAULT 'Aman',
    `gap_notes` TEXT NULL,
    `action_plan` TEXT NULL,
    `pic` VARCHAR(150) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`study_program_id`) REFERENCES `study_programs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Dynamic Indicators (PRD Section 38 - Dynamic IKU / Renstra)
CREATE TABLE `indicators` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `category` ENUM('IKU', 'Renstra', 'Fakultas', 'SPMI') NOT NULL DEFAULT 'IKU',
    `formula` TEXT NULL,
    `unit` VARCHAR(50) DEFAULT '%',
    `data_source` VARCHAR(150) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Indicator Targets
CREATE TABLE `indicator_targets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `indicator_id` INT NOT NULL,
    `faculty_id` INT NULL,
    `study_program_id` INT NULL,
    `year` INT NOT NULL,
    `period` VARCHAR(30) DEFAULT 'Tahunan',
    `target_value` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`indicator_id`) REFERENCES `indicators`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`faculty_id`) REFERENCES `faculties`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`study_program_id`) REFERENCES `study_programs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Indicator Realizations
CREATE TABLE `indicator_realizations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `indicator_target_id` INT NOT NULL,
    `realization_value` DECIMAL(10,2) NOT NULL,
    `achievement_percentage` DECIMAL(5,2) NOT NULL,
    `status` ENUM('Success', 'Attention', 'Warning', 'Critical') DEFAULT 'Success',
    `notes` TEXT NULL,
    `verified_by` VARCHAR(100) NULL,
    `verified_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`indicator_target_id`) REFERENCES `indicator_targets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Indicator Evidences
CREATE TABLE `indicator_evidences` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `indicator_realization_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) NULL,
    `file_size` INT DEFAULT 0,
    `verified_status` ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending',
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`indicator_realization_id`) REFERENCES `indicator_realizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Renstra Strategic Programs
CREATE TABLE `renstra_programs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `faculty_id` INT NOT NULL,
    `strategic_objective` VARCHAR(255) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `pic` VARCHAR(150) NOT NULL,
    `budget` DECIMAL(15,2) DEFAULT 0,
    `start_year` INT NOT NULL,
    `end_year` INT NOT NULL,
    `progress_percentage` DECIMAL(5,2) DEFAULT 0,
    `status` ENUM('Belum Mulai', 'Berjalan', 'Tercapai', 'Tertunda') DEFAULT 'Berjalan',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`faculty_id`) REFERENCES `faculties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Cooperations (MoU, MoA, IA)
CREATE TABLE `cooperations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `partner_name` VARCHAR(200) NOT NULL,
    `type` ENUM('MoU', 'MoA', 'IA') NOT NULL DEFAULT 'MoA',
    `level` ENUM('Internasional', 'Nasional', 'Lokal') DEFAULT 'Nasional',
    `scope` VARCHAR(255) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `pic_internal` VARCHAR(150) NOT NULL,
    `pic_partner` VARCHAR(150) NULL,
    `document_file` VARCHAR(255) NULL,
    `status` ENUM('Aktif', 'Akan Berakhir', 'Kadaluarsa') DEFAULT 'Aktif',
    `real_activities_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Finances (RKA & Realization)
CREATE TABLE `finances` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `faculty_id` INT NOT NULL,
    `study_program_id` INT NULL,
    `fiscal_year` INT NOT NULL,
    `category` ENUM('RKA Operasional', 'RKA Pengembangan', 'Penelitian', 'PkM', 'Kemahasiswaan', 'Pendapatan') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `budgeted_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `realized_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `absorption_percentage` DECIMAL(5,2) DEFAULT 0,
    `status` ENUM('Optimal', 'Cukup', 'Rendah', 'Overbudget') DEFAULT 'Optimal',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`faculty_id`) REFERENCES `faculties`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`study_program_id`) REFERENCES `study_programs`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Digital Meetings Suite
CREATE TABLE `meetings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_number` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `type` ENUM('Rapat Kaprodi', 'Rapat Dosen Prodi', 'Rapat Gabungan', 'Rapat Pimpinan', 'Rapat Senat') NOT NULL,
    `meeting_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NULL,
    `location` VARCHAR(150) NOT NULL,
    `chairperson_id` INT NULL,
    `secretary_id` INT NULL,
    `agenda` TEXT NOT NULL,
    `status` ENUM('Terjadwal', 'Sedang Berlangsung', 'Selesai', 'Dibatalkan') DEFAULT 'Terjadwal',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Meeting Participants
CREATE TABLE `meeting_participants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_id` INT NOT NULL,
    `user_id` INT NULL,
    `name` VARCHAR(150) NOT NULL,
    `role_in_meeting` VARCHAR(50) DEFAULT 'Peserta',
    `attendance_status` ENUM('Hadir', 'Izin', 'Sakit', 'Alpa') DEFAULT 'Hadir',
    `signed_at` DATETIME NULL,
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. Meeting Documents (Digital Packet: Undangan, Absensi, Notulensi, Materi, Foto)
CREATE TABLE `meeting_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_id` INT NOT NULL,
    `document_type` ENUM('Undangan', 'Daftar Hadir', 'Materi', 'Notulensi', 'Foto', 'Lainnya') NOT NULL,
    `file_title` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` INT DEFAULT 0,
    `uploaded_by` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. Action Items (RTL Rapat & Tracking Workflow)
CREATE TABLE `action_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_id` INT NOT NULL,
    `item_code` VARCHAR(50) NOT NULL,
    `description` TEXT NOT NULL,
    `pic_user_id` INT NULL,
    `pic_name` VARCHAR(150) NOT NULL,
    `study_program_id` INT NULL,
    `priority` ENUM('Tinggi', 'Sedang', 'Rendah') DEFAULT 'Sedang',
    `deadline` DATE NOT NULL,
    `status` ENUM('Belum Mulai', 'Proses', 'Diserahkan', 'Diverifikasi', 'Selesai', 'Terlambat', 'Dibatalkan') DEFAULT 'Belum Mulai',
    `progress_percentage` DECIMAL(5,2) DEFAULT 0,
    `evidence_file` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `verified_by` VARCHAR(100) NULL,
    `verified_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. Approvals Workflow
CREATE TABLE `approvals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `module` ENUM('Kegiatan', 'Penelitian', 'PkM', 'Kerja Sama', 'Anggaran', 'Surat', 'RTL') NOT NULL,
    `record_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `requester_id` INT NULL,
    `requester_name` VARCHAR(150) NOT NULL,
    `study_program_id` INT NULL,
    `submission_date` DATE NOT NULL,
    `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    `notes` TEXT NULL,
    `approved_by` VARCHAR(100) NULL,
    `approved_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 29. Critical Alerts Engine
CREATE TABLE `critical_alerts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `alert_type` ENUM('Dosen', 'Mahasiswa', 'Deadline', 'Dokumen', 'Akreditasi', 'RTL', 'SPMI') NOT NULL,
    `severity` ENUM('Critical', 'Warning', 'Attention', 'Info') NOT NULL DEFAULT 'Warning',
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `target_url` VARCHAR(255) NULL,
    `record_id` INT NULL,
    `is_resolved` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 30. Surveys
CREATE TABLE `surveys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` ENUM('Mahasiswa', 'Dosen', 'Tendik', 'Alumni', 'Pengguna Lulusan') NOT NULL,
    `period_year` INT NOT NULL,
    `respondents_count` INT DEFAULT 0,
    `average_score` DECIMAL(3,2) DEFAULT 0,
    `satisfaction_percentage` DECIMAL(5,2) DEFAULT 0,
    `status` ENUM('Sangat Baik', 'Baik', 'Cukup', 'Kurang') DEFAULT 'Baik',
    `summary` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 31. Audit Logs (PRD Section 29)
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `username` VARCHAR(100) NOT NULL,
    `action` ENUM('CREATE', 'READ', 'UPDATE', 'DELETE', 'APPROVE', 'REJECT', 'EXPORT', 'DOWNLOAD', 'LOGIN', 'LOGOUT') NOT NULL,
    `module` VARCHAR(100) NOT NULL,
    `record_id` VARCHAR(50) NULL,
    `old_values` LONGTEXT NULL,
    `new_values` LONGTEXT NULL,
    `ip_address` VARCHAR(50) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 32. In-App Notifications
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('alert', 'reminder', 'approval', 'deadline', 'rtl', 'system') DEFAULT 'alert',
    `target_url` VARCHAR(255) NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 33. Files Repository
CREATE TABLE `files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `original_name` VARCHAR(255) NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) NULL,
    `file_size` INT DEFAULT 0,
    `module` VARCHAR(100) NOT NULL,
    `record_id` INT NULL,
    `uploaded_by` INT NULL,
    `version` INT DEFAULT 1,
    `status` VARCHAR(50) DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 34. Application Settings
CREATE TABLE `app_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `category` VARCHAR(50) DEFAULT 'general',
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 35. Practicum Modules & Laboratory Verification
CREATE TABLE `practicum_modules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `study_program_id` INT NOT NULL,
    `academic_year_id` INT NOT NULL,
    `semester` INT NOT NULL DEFAULT 1,
    `course_code` VARCHAR(20) NOT NULL,
    `course_name` VARCHAR(150) NOT NULL,
    `sks_lab` INT DEFAULT 1,
    `lab_name` VARCHAR(100) NOT NULL,
    `lecturer_name` VARCHAR(150) NOT NULL,
    `assistant_name` VARCHAR(150) NULL,
    `target_modules` INT NOT NULL DEFAULT 12,
    `completed_modules` INT NOT NULL DEFAULT 12,
    `module_file` VARCHAR(255) NULL,
    `rubric_file` VARCHAR(255) NULL,
    `logbook_status` ENUM('Lengkap', 'Sebagian', 'Belum Ada') DEFAULT 'Lengkap',
    `status` ENUM('Terpenuhi 100%', 'Progres Berjalan', 'Perlu Perhatian', 'Dikonfirmasi ke Kaprodi', 'Revisi Modul') DEFAULT 'Terpenuhi 100%',
    `dekan_notes` TEXT NULL,
    `kaprodi_feedback` TEXT NULL,
    `last_confirmed_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`study_program_id`) REFERENCES `study_programs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
