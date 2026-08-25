-- =======================================================
-- DEAN EXECUTIVE INFORMATION SYSTEM (DEIS)
-- Comprehensive Initial Dataset (Seeds)
-- =======================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Roles
INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Super Admin', 'super_admin', 'Full system access & administration'),
(2, 'Dekan (Executive)', 'dekan', 'Executive management & primary decision maker'),
(3, 'Kaprodi', 'kaprodi', 'Program study head and management'),
(4, 'Dosen', 'dosen', 'Lecturer personal performance & academic'),
(5, 'GKM / SPMI', 'spmi', 'Quality assurance & audit manager'),
(6, 'Operator', 'operator', 'Data administration & academic operator'),
(7, 'Developer', 'developer', 'Technical administration & system logs');

-- 2. Permissions
INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `description`) VALUES
(1, 'View Dashboard', 'view_dashboard', 'dashboard', 'Access executive dashboard'),
(2, 'View Command Center', 'view_command_center', 'command_center', 'Access command center & critical radar'),
(3, 'Approve Items', 'approve_items', 'approvals', 'Approve or reject pending requests'),
(4, 'Manage Data Master', 'manage_master', 'master', 'Create, update, delete master records'),
(5, 'View Academic Monitoring', 'view_academic', 'academic', 'Monitor lectures, attendance, & guidance'),
(6, 'Manage Lecturers', 'manage_lecturers', 'lecturers', 'Manage lecturer records & BKD'),
(7, 'Manage Students', 'manage_students', 'students', 'Manage student records & early warnings'),
(8, 'Manage Quality SPMI', 'manage_spmi', 'quality', 'Manage SPMI standards, PPEPP, & AMI'),
(9, 'Manage Accreditation', 'manage_accreditation', 'accreditation', 'Track accreditation, LED, & LKPS'),
(10, 'Manage Strategic KPI', 'manage_strategic', 'strategic', 'Manage IKU indicators, targets, realizations'),
(11, 'Manage Cooperations', 'manage_cooperations', 'cooperations', 'Manage MoU/MoA and partner programs'),
(12, 'Manage Finances', 'manage_finances', 'finances', 'Manage RKA and budget realizations'),
(13, 'Manage Meetings & RTL', 'manage_meetings', 'meetings', 'Manage digital meeting suite & RTL workflow'),
(14, 'Export Reports', 'export_reports', 'reports', 'Generate and export executive reports'),
(15, 'Manage Users & RBAC', 'manage_users', 'users', 'Manage user accounts and roles'),
(16, 'View Audit Logs', 'view_audit_logs', 'audit', 'View system audit trails');

-- Assign All Permissions to Super Admin & Dekan
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`;

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM `permissions`;

-- Assign Kaprodi Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(3, 1), (3, 2), (3, 5), (3, 6), (3, 7), (3, 8), (3, 9), (3, 10), (3, 13), (3, 14);

-- Assign Dosen Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(4, 1), (4, 5), (4, 6), (4, 13);

-- Assign SPMI Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(5, 1), (5, 8), (5, 9), (5, 13), (5, 14);

-- Assign Operator Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(6, 1), (6, 4), (6, 5), (6, 6), (6, 7), (6, 11), (6, 12), (6, 13), (6, 14);

-- 3. Users (Default password: password)
-- bcrypt of 'password': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO `users` (`id`, `role_id`, `name`, `username`, `email`, `password`, `phone`, `nidn`, `status`) VALUES
(1, 1, 'Administrator Sistem DEIS', 'admin', 'admin@deis.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', NULL, 'active'),
(2, 2, 'Prof. Dr. Ir. Hendra Wijaya, M.Kom.', 'dekan', 'dekan@deis.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567891', '0012057801', 'active'),
(3, 3, 'Dr. Rony Setiawan, S.Kom., M.T.', 'kaprodi.ti', 'rony@deis.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567892', '0015088202', 'active'),
(4, 3, 'Dr. Siti Nurhaliza, M.Cs.', 'kaprodi.si', 'siti@deis.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567893', '0020048503', 'active'),
(8, 7, 'Lead Developer & System Architect', 'developer', 'developer@deis.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567897', NULL, 'active');

-- 4. Faculty
INSERT INTO `faculties` (`id`, `code`, `name`, `dean_name`, `vision`, `mission`) VALUES
(1, 'FTIK', 'Fakultas Teknologi Informasi dan Komputer', 'Prof. Dr. Ir. Hendra Wijaya, M.Kom.', 'Menjadi fakultas unggul bertaraf internasional dalam rekayasa teknologi informasi cerdas dan komputasi berbasis riset terapan berkelanjutan pada tahun 2030.', '1. Menyelenggarakan pendidikan tinggi teknologi informasi yang adaptif terhadap industri 4.0.\n2. Melaksanakan riset inovatif dan publikasi bereputasi internasional.\n3. Mengimplementasikan pengabdian kepada masyarakat berbasis solusi digital nyata.\n4. Membangun tata kelola fakultas yang akuntabel, transparan, dan berorientasi mutu.');

-- 5. Study Programs
INSERT INTO `study_programs` (`id`, `faculty_id`, `code`, `name`, `degree`, `head_name`, `head_user_id`, `accreditation_status`, `accreditation_score`, `accreditation_expire`, `student_count`, `lecturer_count`, `target_retention`) VALUES
(1, 1, 'TI-S1', 'Teknik Informatika', 'S1', 'Dr. Rony Setiawan, S.Kom., M.T.', 3, 'Unggul', 372.50, '2028-10-15', 780, 24, 90.00),
(2, 1, 'SI-S1', 'Sistem Informasi', 'S1', 'Dr. Siti Nurhaliza, M.Cs.', 4, 'Baik Sekali', 345.00, '2026-11-20', 540, 18, 88.00),
(3, 1, 'DS-S1', 'Sains Data', 'S1', 'Dr. Agus Pratama, S.Si., M.Sc.', NULL, 'Baik', 298.00, '2027-05-30', 290, 12, 85.00);

-- 6. Academic Years
INSERT INTO `academic_years` (`id`, `name`, `semester`, `is_active`, `start_date`, `end_date`) VALUES
(1, '2025/2026', 'Genap', 1, '2026-02-01', '2026-07-31'),
(2, '2025/2026', 'Ganjil', 0, '2025-08-01', '2026-01-31'),
(3, '2024/2025', 'Genap', 0, '2025-02-01', '2025-07-31');

-- 7. Lecturers
INSERT INTO `lecturers` (`id`, `user_id`, `study_program_id`, `nidn`, `name`, `gender`, `academic_rank`, `education_level`, `certification_status`, `teaching_load_sks`, `bkd_status`, `sinta_score`, `scopus_h_index`, `publication_count`, `pkm_count`, `hki_count`, `books_count`, `attendance_percentage`, `status`, `phone`, `email`) VALUES
(1, 2, 1, '0012057801', 'Prof. Dr. Ir. Hendra Wijaya, M.Kom.', 'L', 'Guru Besar', 'S3', 'Tersertifikasi', 8.00, 'Memenuhi', 1250.50, 7, 38, 12, 8, 5, 96.50, 'Aktif', '081234567891', 'dekan@deis.ac.id'),
(2, 3, 1, '0015088202', 'Dr. Rony Setiawan, S.Kom., M.T.', 'L', 'Lektor Kepala', 'S3', 'Tersertifikasi', 12.00, 'Memenuhi', 840.00, 5, 24, 8, 4, 3, 98.00, 'Aktif', '081234567892', 'rony@deis.ac.id'),
(3, 4, 2, '0020048503', 'Dr. Siti Nurhaliza, M.Cs.', 'P', 'Lektor Kepala', 'S3', 'Tersertifikasi', 12.00, 'Memenuhi', 720.00, 4, 18, 9, 3, 2, 95.00, 'Aktif', '081234567893', 'siti@deis.ac.id'),
(4, 5, 1, '0011038904', 'Bambang Kusumo, Ph.D.', 'L', 'Lektor', 'S3', 'Tersertifikasi', 14.00, 'Memenuhi', 950.00, 6, 21, 6, 5, 2, 92.00, 'Aktif', '081234567894', 'bambang@deis.ac.id'),
(5, 6, 2, '0009028005', 'Dr. Maya Kartika, M.T.', 'P', 'Lektor', 'S3', 'Tersertifikasi', 10.00, 'Memenuhi', 450.00, 3, 14, 11, 2, 1, 97.00, 'Aktif', '081234567895', 'spmi@deis.ac.id'),
(6, NULL, 3, '0014078806', 'Dr. Agus Pratama, S.Si., M.Sc.', 'L', 'Lektor', 'S3', 'Tersertifikasi', 12.00, 'Memenuhi', 610.00, 4, 16, 5, 2, 2, 94.00, 'Aktif', '081234567897', 'agus.ds@deis.ac.id'),
(7, NULL, 1, '0025119007', 'Dimas Prasetyo, M.Kom.', 'L', 'Asisten Ahli', 'S2', 'Tersertifikasi', 16.00, 'Memenuhi', 280.00, 2, 9, 4, 1, 1, 91.00, 'Aktif', '081234567898', 'dimas@deis.ac.id'),
(8, NULL, 1, '0018099208', 'Nurlaila Sari, M.Kom.', 'P', 'Asisten Ahli', 'S2', 'Belum Tersertifikasi', 10.00, 'Belum Memenuhi', 120.00, 1, 3, 2, 0, 0, 72.00, 'Aktif', '081234567899', 'nurlaila@deis.ac.id'), -- Problematic alert lecturer
(9, NULL, 2, '0004068709', 'Eko Wahyudi, M.T.', 'L', 'Lektor', 'S2', 'Tersertifikasi', 14.00, 'Memenuhi', 390.00, 2, 11, 7, 2, 1, 88.00, 'Aktif', '081234567810', 'eko@deis.ac.id'),
(10, NULL, 3, '0012019310', 'Fitri Handayani, M.Stat.', 'P', 'Tenaga Pengajar', 'S2', 'Belum Tersertifikasi', 8.00, 'Dalam Penilaian', 95.00, 1, 2, 1, 0, 0, 96.00, 'Aktif', '081234567811', 'fitri@deis.ac.id'),
(11, NULL, 1, '0008087911', 'Ir. Gunawan Wibisono, M.T.', 'L', 'Lektor Kepala', 'S2', 'Tersertifikasi', 12.00, 'Memenuhi', 510.00, 3, 15, 8, 3, 4, 93.00, 'Aktif', '081234567812', 'gunawan@deis.ac.id'),
(12, NULL, 2, '0017038612', 'Ratna Juwita, M.Kom.', 'P', 'Lektor', 'S2', 'Tersertifikasi', 6.00, 'Belum Memenuhi', 190.00, 1, 4, 3, 1, 0, 68.50, 'Aktif', '081234567813', 'ratna@deis.ac.id'); -- Problematic alert lecturer

-- 8. Students (Sampling across batches, risk levels, and achievements)
INSERT INTO `students` (`id`, `study_program_id`, `nim`, `name`, `batch_year`, `semester`, `current_gpa`, `credits_earned`, `attendance_percentage`, `status`, `risk_status`, `risk_reason`, `scholarship`, `organization`) VALUES
(1, 1, '220101001', 'Aditya Pratama Putra', 2022, 6, 3.88, 110, 96.00, 'Aktif', 'Normal', NULL, 'KIP Kuliah', 'Ketua BEM Fakultas'),
(2, 1, '220101015', 'Muhammad Rizky Ramadhan', 2022, 6, 3.75, 108, 92.00, 'Aktif', 'Normal', NULL, 'Beasiswa Unggulan', 'Himpunan TI'),
(3, 1, '200101089', 'Farhan Maulana', 2020, 10, 1.85, 92, 54.00, 'Aktif', 'Critical', 'Batas masa studi semester 10 & IPK < 2.0 (Risiko DO)', NULL, NULL),
(4, 1, '210101044', 'Bayu Tri Nugroho', 2021, 8, 2.15, 100, 65.00, 'Aktif', 'Warning', 'Kehadiran < 75% dan progress skripsi terlambat', NULL, NULL),
(5, 2, '230201008', 'Anisa Rahmawati', 2023, 4, 3.92, 72, 98.00, 'Aktif', 'Normal', NULL, 'Beasiswa BCA Finansial', 'GDSC Core Team'),
(6, 2, '220201033', 'Kevin Christian', 2022, 6, 1.95, 78, 58.00, 'Aktif', 'Critical', 'IPK < 2.0 selama 2 semester berturut-turut', NULL, NULL),
(7, 2, '230201050', 'Dewi Lestari', 2023, 4, 3.68, 70, 94.00, 'Aktif', 'Normal', NULL, NULL, 'UKM Penalaran'),
(8, 3, '230301012', 'Fajar Sidik Permana', 2023, 4, 3.85, 74, 95.00, 'Aktif', 'Normal', NULL, 'Beasiswa BI', 'Data Science Club'),
(9, 3, '220301021', 'Rian Hidayat', 2022, 6, 2.30, 80, 68.00, 'Aktif', 'Warning', 'Kehadiran rendah & SKS semester ini tidak maksimal', NULL, NULL),
(10, 1, '190101005', 'Gilang Baskara', 2019, 12, 3.42, 144, 100.00, 'Lulus', 'Normal', NULL, NULL, 'Alumni (Software Eng di Tokopedia)'),
(11, 2, '190201018', 'Tiara Andini', 2019, 12, 3.65, 144, 100.00, 'Lulus', 'Normal', NULL, NULL, 'Alumni (Data Analyst di Mandiri)'),
(12, 1, '240101003', 'Raffi Ahmadinejad', 2024, 2, 3.55, 20, 94.00, 'Aktif', 'Normal', NULL, NULL, 'Anggota Robotika');

-- 9. Courses
INSERT INTO `courses` (`id`, `study_program_id`, `code`, `name`, `sks`, `semester`, `kurikulum_year`, `lecturer_id`, `rps_status`) VALUES
(1, 1, 'TIF301', 'Kecerdasan Buatan & Machine Learning', 3, 5, '2024', 4, 'Lengkap'),
(2, 1, 'TIF204', 'Rekayasa Perangkat Lunak Terdistribusi', 3, 4, '2024', 2, 'Lengkap'),
(3, 1, 'TIF102', 'Struktur Data & Algoritma Lanjut', 4, 2, '2024', 7, 'Lengkap'),
(4, 1, 'TIF405', 'Keamanan Siber & Forensik Digital', 3, 6, '2024', 8, 'Belum Lengkap'), -- Problematic RPS
(5, 2, 'SIF302', 'Enterprise Resource Planning (ERP)', 3, 5, '2024', 3, 'Lengkap'),
(6, 2, 'SIF205', 'Manajemen Proses Bisnis Digital', 3, 4, '2024', 9, 'Lengkap'),
(7, 3, 'DSF201', 'Analisis Multivariat Terapan', 3, 3, '2024', 6, 'Lengkap'),
(8, 3, 'DSF303', 'Big Data Engineering & Cloud Data Lake', 3, 5, '2024', 10, 'Lengkap');

-- 10. Classes & Perkuliahan Monitoring
INSERT INTO `classes` (`id`, `course_id`, `academic_year_id`, `lecturer_id`, `class_name`, `room`, `total_planned_meetings`, `total_held_meetings`, `average_attendance`, `problem_flag`, `problem_notes`) VALUES
(1, 1, 1, 4, 'TI-5A', 'Lab AI 301', 16, 12, 94.50, 0, NULL),
(2, 2, 1, 2, 'TI-4A', 'Ruang 204', 16, 12, 96.00, 0, NULL),
(3, 3, 1, 7, 'TI-2B', 'Lab RPL 102', 16, 11, 91.20, 0, NULL),
(4, 4, 1, 8, 'TI-6A', 'Lab Jaringan 201', 16, 6, 68.00, 1, 'Realisasi pertemuan tertinggal 6 pekan & presensi rendah'),
(5, 5, 1, 3, 'SI-5A', 'Ruang 305', 16, 12, 95.00, 0, NULL),
(6, 6, 1, 9, 'SI-4A', 'Ruang 302', 16, 12, 92.40, 0, NULL),
(7, 7, 1, 6, 'DS-3A', 'Lab Big Data 401', 16, 12, 93.80, 0, NULL),
(8, 8, 1, 10, 'DS-5A', 'Lab Komputasi 203', 16, 11, 90.50, 0, NULL),
(9, 1, 1, 4, 'TI-5B', 'Lab AI 302', 16, 11, 93.00, 0, NULL),
(10, 2, 1, 2, 'TI-4B', 'Ruang 205', 16, 12, 95.50, 0, NULL),
(11, 5, 1, 3, 'SI-5B', 'Ruang 306', 16, 11, 89.00, 0, NULL),
(12, 4, 1, 8, 'TI-6B', 'Lab Jaringan 202', 16, 7, 71.50, 1, 'Keterlambatan silabus & absensi mahasiswa di bawah 75%');

-- 11. Guidances
INSERT INTO `guidances` (`id`, `lecturer_id`, `student_id`, `type`, `title`, `progress_percentage`, `status`, `last_guidance_date`) VALUES
(1, 2, 1, 'Skripsi', 'Rancang Bangun Sistem Klasifikasi Citra Medis Menggunakan Deep Convolutional Neural Network', 80.00, 'Aktif', '2026-08-18'),
(2, 4, 2, 'Skripsi', 'Implementasi Reinforcement Learning untuk Optimasi Rute Logistik Maritim Indonesia', 75.00, 'Aktif', '2026-08-20'),
(3, 8, 3, 'Skripsi', 'Sistem Pakar Diagnosa Penyakit Tanaman', 20.00, 'Terlambat', '2026-05-10'), -- Problematic guidance
(4, 3, 5, 'MBKM', 'Magang Studi Independen Bersertifikat di PT Telkom Indonesia', 90.00, 'Aktif', '2026-08-22'),
(5, 6, 8, 'Magang', 'Data Scientist Intern di GoTo Financial', 85.00, 'Aktif', '2026-08-19');

-- 12. SPMI Standards
INSERT INTO `spmi_standards` (`id`, `category`, `code`, `name`, `target_metric`, `current_metric`, `status`, `ppepp_stage`, `pic`, `period_year`) VALUES
(1, 'Pendidikan', 'STD-DIK-01', 'Rata-rata IPK Lulusan Sarjana >= 3.35', '>= 3.35', '3.44', 'Tercapai', 'Evaluasi', 'Dr. Rony Setiawan', 2026),
(2, 'Pendidikan', 'STD-DIK-02', 'Ketepatan Waktu Kelulusan Mahasiswa (<= 4 Tahun) >= 75%', '>= 75%', '78.5%', 'Tercapai', 'Evaluasi', 'Dr. Siti Nurhaliza', 2026),
(3, 'Penelitian', 'STD-LIT-01', 'Rasio Publikasi Scopus/SINTA 1-2 per Dosen per Tahun >= 0.8', '>= 0.80', '0.86', 'Tercapai', 'Pengendalian', 'Prof. Dr. Ir. Hendra Wijaya', 2026),
(4, 'Pengabdian', 'STD-PKM-01', 'Implementasi PkM Berbasis Kemitraan Komunitas & Industri >= 15 Kegiatan', '>= 15', '18', 'Tercapai', 'Peningkatan', 'Dr. Maya Kartika', 2026),
(5, 'SDM', 'STD-SDM-01', 'Persentase Dosen Bergelar Doktor (S3) >= 50%', '>= 50%', '42.8%', 'Belum Tercapai', 'Pelaksanaan', 'Dekan FTIK', 2026), -- Alert
(6, 'Tata Kelola', 'STD-TKL-01', 'Kepatuhan Tindak Lanjut Hasil Rapat & Temuan AMI >= 90%', '>= 90%', '82.5%', 'Proses', 'Pengendalian', 'Ketua GKM', 2026);

-- 13. AMI Audits & Findings
INSERT INTO `ami_audits` (`id`, `study_program_id`, `period_year`, `audit_date`, `lead_auditor`, `auditor_members`, `kts_major_count`, `kts_minor_count`, `ob_count`, `status`, `summary`) VALUES
(1, 1, 2026, '2026-06-15', 'Dr. Maya Kartika, M.T.', 'Dr. Agus Pratama, S.Si.', 0, 2, 3, 'RTL Terbuka', 'Audit siklus reguler semester genap. Ketercapaian RPS dan publikasi sangat baik, perlu perbaikan kelengkapan berkas kontrak kerja sama.'),
(2, 2, 2026, '2026-06-18', 'Bambang Kusumo, Ph.D.', 'Dr. Maya Kartika, M.T.', 1, 3, 2, 'RTL Terbuka', 'Ditemukan 1 KTS Major terkait kesiapan berkas re-akreditasi LAM-INFOKOM dan kelengkapan portfolio evaluasi CPL lulusan.');

INSERT INTO `ami_findings` (`id`, `ami_audit_id`, `standard_id`, `finding_type`, `description`, `root_cause`, `corrective_action`, `pic`, `deadline`, `status`, `verification_notes`) VALUES
(1, 2, 6, 'KTS Major', 'Dokumen Evaluasi Capaian Pembelajaran Lulusan (CPL) Siklus 2025 belum disahkan secara formal oleh Senat Fakultas.', 'Keterlambatan penyusunan laporan evaluasi kurikulum berbasis OBE.', 'Menjadwalkan rapat finalisasi dan pengesahan evaluasi CPL dalam rapat pimpinan.', 'Kaprodi Sistem Informasi', '2026-09-10', 'In Progress', 'Draft sudah disusun, menunggu rapat pengesahan pimpinan.'),
(2, 1, 3, 'KTS Minor', '3 Dosen belum memperbarui ID SINTA dan sinkronisasi data Garuda pada profil PDDIKTI.', 'Kurangnya sosialisasi teknis integrasi akun SINTA v3.', 'Pendampingan langsung operator riset kepada dosen bersangkutan.', 'Sekretaris Prodi TI', '2026-08-30', 'Open', NULL);

-- 14. Accreditations
INSERT INTO `accreditations` (`id`, `study_program_id`, `institution`, `current_grade`, `target_grade`, `valid_until`, `days_remaining`, `led_progress`, `lkps_progress`, `overall_progress`, `status`, `gap_notes`, `action_plan`, `pic`) VALUES
(1, 1, 'LAM-INFOKOM', 'Unggul', 'Unggul (Re-akreditasi)', '2028-10-15', 782, 100.00, 100.00, 100.00, 'Aman', 'Semua kriteria IAPS 4.0 terpenuhi dengan baik.', 'Pemeliharaan continuous improvement SPMI.', 'Dr. Rony Setiawan, M.T.'),
(2, 2, 'LAM-INFOKOM', 'Baik Sekali', 'Unggul', '2026-11-20', 88, 72.00, 80.00, 76.00, 'Kritis', 'Tersisa 88 hari sebelum masa berlaku berakhir. LED Kriteria 4 & 6 masih dalam review eksternal.', 'Akselerasi submit berkas ke LAM-INFOKOM maksimal 30 hari ke depan.', 'Dr. Siti Nurhaliza, M.Cs.'), -- Critical Deadline
(3, 3, 'BAN-PT', 'Baik', 'Baik Sekali', '2027-05-30', 279, 45.00, 50.00, 47.50, 'Perhatian', 'Perlu penambahan jumlah publikasi dosen dan dosen bergelar S3.', 'Pemberian hibah percepatan studi S3 dan insentif publikasi.', 'Dr. Agus Pratama, M.Sc.');

-- 15. Dynamic Indicators (PRD Section 38)
INSERT INTO `indicators` (`id`, `code`, `name`, `category`, `formula`, `unit`, `data_source`, `is_active`) VALUES
(1, 'IKU-1', 'Lulusan Mendapat Pekerjaan yang Layak (Gaji > 1.2x UMR atau Berwirausaha)', 'IKU', '(Jumlah Lulusan Layak / Total Responden Tracer) * 100', '%', 'Tracer Study & CDC', 1),
(2, 'IKU-2', 'Mahasiswa Mendapat Pengalaman di Luar Kampus (MBKM / Magang / Pertukaran)', 'IKU', '(Jumlah Mahasiswa MBKM / Total Mahasiswa Aktif) * 100', '%', 'SIAKAD & MBKM Portal', 1),
(3, 'IKU-3', 'Dosen Berkegiatan di Luar Kampus (Praktisi / Industri / Konsultan)', 'IKU', '(Jumlah Dosen Berkegiatan / Total Dosen) * 100', '%', 'SISTER & BKD', 1),
(4, 'IKU-4', 'Kualifikasi Dosen S3 dan Bersertifikat Kompetensi / Profesi', 'IKU', '(Jumlah Dosen S3 & Sertifikasi / Total Dosen) * 100', '%', 'PDDIKTI SDM', 1),
(5, 'IKU-5', 'Keluaran Riset & PkM Dosen yang Digunakan Masyarakat / Terindeks Scopus', 'IKU', 'Jumlah Publikasi Bereputasi + HKI Berkomersialisasi', 'Karya', 'SINTA & LPPM', 1),
(6, 'IKU-6', 'Program Studi Bekerja Sama dengan Mitra Kelas Dunia', 'IKU', 'Jumlah MoA/IA Aktif dengan Perusahaan Multinasional/QS 500', 'Mitra', 'Kantor Kerja Sama', 1),
(7, 'IKU-7', 'Kelas yang Kolaboratif dan Partisipatif (Case Method / Project Based)', 'IKU', '(Jumlah Rombel Case Based / Total Rombel) * 100', '%', 'RPS & Audit Perkuliahan', 1),
(8, 'IKU-8', 'Program Studi Berstandar Internasional / Akreditasi Unggul', 'IKU', '(Jumlah Prodi Unggul / Total Prodi) * 100', '%', 'Penjaminan Mutu', 1),
(9, 'REN-01', 'Tingkat Serapan Anggaran Operasional dan Program Strategis', 'Renstra', '(Realisasi Anggaran / Total Pagu RKA) * 100', '%', 'Bagian Keuangan Fakultas', 1),
(10, 'REN-02', 'Indeks Kepuasan Layanan Akademik & Stakeholder', 'Renstra', 'Rata-rata Skor Survei Skala 4.0 Dikonversi ke %', '%', 'GKM Survei', 1);

-- 16. Indicator Targets
INSERT INTO `indicator_targets` (`id`, `indicator_id`, `faculty_id`, `study_program_id`, `year`, `period`, `target_value`) VALUES
(1, 1, 1, NULL, 2026, 'Tahunan', 80.00),
(2, 2, 1, NULL, 2026, 'Tahunan', 35.00),
(3, 3, 1, NULL, 2026, 'Tahunan', 25.00),
(4, 4, 1, NULL, 2026, 'Tahunan', 60.00),
(5, 5, 1, NULL, 2026, 'Tahunan', 45.00),
(6, 6, 1, NULL, 2026, 'Tahunan', 12.00),
(7, 7, 1, NULL, 2026, 'Tahunan', 75.00),
(8, 8, 1, NULL, 2026, 'Tahunan', 66.67),
(9, 9, 1, NULL, 2026, 'Tahunan', 92.00),
(10, 10, 1, NULL, 2026, 'Tahunan', 88.00);

-- 17. Indicator Realizations
INSERT INTO `indicator_realizations` (`id`, `indicator_target_id`, `realization_value`, `achievement_percentage`, `status`, `notes`, `verified_by`, `verified_at`) VALUES
(1, 1, 84.50, 100.00, 'Success', 'Capaian tracer study melebihi target nasional berkat kemitraan alumni aktif.', 'Prof. Dr. Ir. Hendra Wijaya', '2026-08-20 10:00:00'),
(2, 2, 38.20, 100.00, 'Success', 'Tingginya antusiasme mahasiswa pada program MSIB Kemendikbudristek.', 'Prof. Dr. Ir. Hendra Wijaya', '2026-08-20 10:15:00'),
(3, 3, 21.00, 84.00, 'Attention', 'Perlu akselerasi dosen berkegiatan praktisi pada semester ganjil mendatang.', 'Dr. Maya Kartika', '2026-08-21 11:00:00'),
(4, 4, 46.50, 77.50, 'Warning', '3 Calon doktor sedang menyelesaikan tahap disertasi akhir tahun 2026.', 'Dekan FTIK', '2026-08-21 11:30:00'),
(5, 5, 48.00, 100.00, 'Success', 'Target publikasi Scopus Q1/Q2 dan HKI sudah terlampaui di Q3.', 'Prof. Dr. Ir. Hendra Wijaya', '2026-08-22 09:00:00'),
(6, 6, 10.00, 83.33, 'Attention', '2 Draft MoA dengan Oracle Academy dan AWS sedang tahap legal review.', 'Dekan FTIK', '2026-08-22 09:30:00'),
(7, 7, 78.00, 100.00, 'Success', '92% Rombel perkuliahan telah menggunakan silabus case method.', 'Ketua GKM', '2026-08-22 10:00:00'),
(8, 8, 33.33, 50.00, 'Critical', 'Hanya 1 dari 3 prodi yang terakreditasi Unggul (Prodi SI dalam masa kritis).', 'Prof. Dr. Ir. Hendra Wijaya', '2026-08-23 14:00:00'),
(9, 9, 88.50, 96.20, 'Success', 'Serapan anggaran berjalan sehat sesuai time schedule RKA.', 'Kabag Keuangan', '2026-08-23 15:00:00'),
(10, 10, 89.20, 100.00, 'Success', 'Kepuasan mahasiswa dan mitra terhadap sarana laboratorium AI sangat tinggi.', 'Ketua GKM', '2026-08-23 16:00:00');

-- 18. Renstra Programs
INSERT INTO `renstra_programs` (`id`, `faculty_id`, `strategic_objective`, `program_name`, `pic`, `budget`, `start_year`, `end_year`, `progress_percentage`, `status`) VALUES
(1, 1, 'Peningkatan Mutu Tata Kelola & Akreditasi Internasional', 'Akselerasi Akreditasi Unggul LAM-INFOKOM & Persiapan Akreditasi Internasional ASIIN', 'Dr. Siti Nurhaliza, M.Cs.', 250000000.00, 2025, 2027, 75.00, 'Berjalan'),
(2, 1, 'Pengembangan Riset & Inovasi Teknologi Unggulan', 'Pusat Keunggulan AI & Autonomous IoT Smart Campus', 'Bambang Kusumo, Ph.D.', 400000000.00, 2024, 2026, 85.00, 'Berjalan'),
(3, 1, 'Penguatan Rekognisi SDM & Dosen Berkualifikasi S3', 'Program Beasiswa Fast-Track Doktoral & Insentif Guru Besar', 'Dekan FTIK', 300000000.00, 2025, 2028, 60.00, 'Berjalan');

-- 19. Cooperations
INSERT INTO `cooperations` (`id`, `partner_name`, `type`, `level`, `scope`, `start_date`, `end_date`, `pic_internal`, `pic_partner`, `document_file`, `status`, `real_activities_count`) VALUES
(1, 'PT Telkom Indonesia (Persero) Tbk', 'MoA', 'Nasional', 'Magang Industri Bersertifikat, Riset Bersama Smart City, Rekrutmen Kampus', '2024-03-01', '2027-03-01', 'Dr. Rony Setiawan', 'VP Digital Talent Telkom', 'moa_telkom_2024.pdf', 'Aktif', 8),
(2, 'Google Cloud Academic Program', 'MoU', 'Internasional', 'Kurikulum Cloud Computing, Voucher Sertifikasi Google Cloud, Lab AI', '2023-09-15', '2026-09-15', 'Bambang Kusumo, Ph.D.', 'Program Manager Google Cloud APAC', 'mou_google_2023.pdf', 'Akan Berakhir', 14), -- Expiration alert in 22 days
(3, 'PT Astra International Tbk', 'IA', 'Nasional', 'Beasiswa Prestasi & Proyek Riset IoT Otomotif', '2025-01-10', '2028-01-10', 'Dr. Agus Pratama', 'Head of CSR Astra', 'ia_astra_2025.pdf', 'Aktif', 4),
(4, 'Universiti Teknologi Malaysia (UTM)', 'MoA', 'Internasional', 'Joint Research, Visiting Professor & Student Exchange', '2024-06-01', '2027-06-01', 'Prof. Dr. Ir. Hendra Wijaya', 'Dean of Computing UTM', 'moa_utm_2024.pdf', 'Aktif', 6);

-- 20. Finances (RKA & Realization)
INSERT INTO `finances` (`id`, `faculty_id`, `study_program_id`, `fiscal_year`, `category`, `title`, `budgeted_amount`, `realized_amount`, `absorption_percentage`, `status`) VALUES
(1, 1, NULL, 2026, 'RKA Operasional', 'Operasional Kantor Dekanat & Sarana Pembelajaran', 1200000000.00, 1050000000.00, 87.50, 'Optimal'),
(2, 1, 1, 2026, 'RKA Pengembangan', 'Modernisasi Server & Cloud Lab Rekayasa Data TI', 450000000.00, 420000000.00, 93.33, 'Optimal'),
(3, 1, 2, 2026, 'RKA Pengembangan', 'Persiapan Dokumen & Asesmen Lapangan Re-Akreditasi SI', 180000000.00, 165000000.00, 91.67, 'Optimal'),
(4, 1, NULL, 2026, 'Penelitian', 'Hibah Penelitian Dosen Pemula & Riset Terapan Fakultas', 350000000.00, 310000000.00, 88.57, 'Optimal'),
(5, 1, NULL, 2026, 'PkM', 'Program Desa Digital Binaan & Hilirisasi Inovasi', 200000000.00, 175000000.00, 87.50, 'Optimal'),
(6, 1, NULL, 2026, 'Kemahasiswaan', 'Dukungan Lomba Internasional & Program Kreativitas Mahasiswa', 250000000.00, 235000000.00, 94.00, 'Optimal');

-- 21. Digital Meetings Suite
INSERT INTO `meetings` (`id`, `meeting_number`, `title`, `type`, `meeting_date`, `start_time`, `end_time`, `location`, `chairperson_id`, `secretary_id`, `agenda`, `status`, `notes`) VALUES
(1, 'RAPAT-2026-08-01', 'Rapat Koordinasi Pimpinan Fakultas: Evaluasi Tengah Semester & Kesiapan Akreditasi', 'Rapat Pimpinan', '2026-08-15', '09:00:00', '12:30:00', 'Ruang Senat Dekanat Lt. 3', 2, 3, '1. Review progres serapan anggaran Triwulan II\n2. Monitoring percepatan submit LED & LKPS Prodi Sistem Informasi ke LAM-INFOKOM\n3. Evaluasi dosen dengan realisasi perkuliahan dan BKD di bawah standar\n4. Tindak lanjut program kemitraan internasional', 'Selesai', 'Rapat dihadiri seluruh jajaran dekanat, kaprodi, dan ketua unit mutu.'),
(2, 'RAPAT-2026-08-02', 'Rapat Koordinasi Kaprodi: Evaluasi Perkuliahan Semester Genap & Verifikasi BKD Dosen', 'Rapat Kaprodi', '2026-08-20', '13:30:00', '16:00:00', 'Ruang Rapat Dekanat', 2, 4, '1. Verifikasi kelengkapan pelaporan BKD dosen semester genap 2025/2026\n2. Penanganan mahasiswa berisiko DO dan monitoring kehadiran perkuliahan\n3. Sinkronisasi jadwal UTS/UAS dan pengumpulan RPS', 'Selesai', 'Disepakati pembentukan satgas percepatan BKD dosen prodi.'),
(3, 'RAPAT-2026-08-03', 'Rapat Pleno Senat Fakultas: Penetapan Renstra 2026-2030 & Finalisasi Anggaran RKA', 'Rapat Senat', '2026-08-28', '09:00:00', '14:00:00', 'Auditorium FTIK', 2, 3, '1. Pembacaan laporan capaian kinerja tahun 2025\n2. Pembahasan naskah akademik Renstra 2026-2030\n3. Pengesahan usulan RKA tahun anggaran 2027', 'Terjadwal', 'Agenda wajib dihadiri seluruh anggota senat fakultas.');

-- 22. Meeting Participants
INSERT INTO `meeting_participants` (`id`, `meeting_id`, `user_id`, `name`, `role_in_meeting`, `attendance_status`, `signed_at`, `notes`) VALUES
(1, 1, 2, 'Prof. Dr. Ir. Hendra Wijaya, M.Kom.', 'Pimpinan Rapat', 'Hadir', '2026-08-15 08:55:00', 'Dekan FTIK'),
(2, 1, 3, 'Dr. Rony Setiawan, S.Kom., M.T.', 'Notulis / Sekretaris', 'Hadir', '2026-08-15 08:50:00', 'Kaprodi TI'),
(3, 1, 4, 'Dr. Siti Nurhaliza, M.Cs.', 'Peserta', 'Hadir', '2026-08-15 08:58:00', 'Kaprodi SI'),
(4, 1, 6, 'Dr. Maya Kartika, M.T.', 'Peserta', 'Hadir', '2026-08-15 08:52:00', 'Ketua GKM/SPMI'),
(5, 2, 2, 'Prof. Dr. Ir. Hendra Wijaya, M.Kom.', 'Pimpinan Rapat', 'Hadir', '2026-08-20 13:25:00', 'Dekan FTIK'),
(6, 2, 3, 'Dr. Rony Setiawan, S.Kom., M.T.', 'Peserta', 'Hadir', '2026-08-20 13:28:00', 'Kaprodi TI'),
(7, 2, 4, 'Dr. Siti Nurhaliza, M.Cs.', 'Notulis / Sekretaris', 'Hadir', '2026-08-20 13:20:00', 'Kaprodi SI');

-- 23. Meeting Documents
INSERT INTO `meeting_documents` (`id`, `meeting_id`, `document_type`, `file_title`, `file_path`, `file_size`, `uploaded_by`) VALUES
(1, 1, 'Undangan', 'Surat Undangan Rapat Pimpinan No. 112/UN/FTIK/2026', 'undangan_rapim_112.pdf', 245000, 'Ahmad Fauzi'),
(2, 1, 'Daftar Hadir', 'Daftar Hadir Digital Rapim 15 Agustus 2026', 'absensi_rapim_1508.pdf', 180000, 'Ahmad Fauzi'),
(3, 1, 'Materi', 'Slide Presentasi Capaian Kinerja & Progres Akreditasi', 'slide_evaluasi_kinerja_q2.pdf', 4500000, 'Dr. Maya Kartika'),
(4, 1, 'Notulensi', 'Risalah Notulensi Resmi Rapat Pimpinan FTIK', 'notulensi_rapim_15082026.pdf', 320000, 'Dr. Rony Setiawan'),
(5, 2, 'Notulensi', 'Notulensi Rapat Koordinasi Kaprodi 20 Agustus 2026', 'notulensi_rakor_kaprodi_2008.pdf', 290000, 'Dr. Siti Nurhaliza');

-- 24. Action Items (RTL Rapat & Tracking Workflow - PRD Section 21 & 33)
INSERT INTO `action_items` (`id`, `meeting_id`, `item_code`, `description`, `pic_user_id`, `pic_name`, `study_program_id`, `priority`, `deadline`, `status`, `progress_percentage`, `evidence_file`, `notes`, `verified_by`, `verified_at`) VALUES
(1, 1, 'RTL-2026-001', 'Finalisasi Bab 4 LED dan LKPS Prodi Sistem Informasi untuk re-akreditasi LAM-INFOKOM', 4, 'Dr. Siti Nurhaliza, M.Cs.', 2, 'Tinggi', '2026-08-22', 'Terlambat', 70.00, NULL, 'Deadline terlewat 2 hari, butuh perhatian khusus Dekan untuk percepatan review eksternal.', NULL, NULL), -- Overdue Alert
(2, 1, 'RTL-2026-002', 'Pemanggilan dan bimbingan khusus bagi Dosen Nurlaila Sari & Ratna Juwita terkait pemenuhan beban BKD & kehadiran mengajar', 3, 'Dr. Rony Setiawan, M.T.', 1, 'Tinggi', '2026-08-25', 'Proses', 60.00, NULL, 'Sudah dijadwalkan sesi konsultasi dengan pimpinan prodi tanggal 25 Agustus.', NULL, NULL),
(3, 1, 'RTL-2026-003', 'Perpanjangan dokumen MoU dengan Google Cloud Academic Program sebelum masa berlaku berakhir', 5, 'Bambang Kusumo, Ph.D.', 1, 'Sedang', '2026-09-05', 'Proses', 40.00, NULL, 'Draft adendum perpanjangan 3 tahun sudah dikirim ke legal Google.', NULL, NULL),
(4, 2, 'RTL-2026-004', 'Konseling akademik dan penerbitan Surat Peringatan bagi mahasiswa berisiko DO (NIM 200101089 & 220201033)', 3, 'Dr. Rony Setiawan, M.T.', 1, 'Tinggi', '2026-08-27', 'Diserahkan', 90.00, 'laporan_konseling_mahasiswa.pdf', 'Laporan hasil pemanggilan orang tua mahasiswa telah diunggah untuk verifikasi Dekan.', NULL, NULL),
(5, 2, 'RTL-2026-005', 'Sosialisasi pengisian Beban Kinerja Dosen (BKD) SISTER Cloud bagi dosen muda', 6, 'Dr. Maya Kartika, M.T.', NULL, 'Sedang', '2026-08-18', 'Selesai', 100.00, 'berita_acara_sosialisasi_bkd.pdf', 'Telah terlaksana dengan 100% kehadiran peserta.', 'Prof. Dr. Ir. Hendra Wijaya', '2026-08-19 14:00:00');

-- 25. Approvals Workflow (PRD Section 8.3 & 33)
INSERT INTO `approvals` (`id`, `module`, `record_id`, `title`, `requester_id`, `requester_name`, `study_program_id`, `submission_date`, `status`, `notes`, `approved_by`, `approved_at`) VALUES
(1, 'Kegiatan', 101, 'Proposal Seminar Internasional IEEE AI & Autonomous Systems Conference 2026', 4, 'Bambang Kusumo, Ph.D.', 1, '2026-08-21', 'Pending', 'Permohonan persetujuan dekanat dan alokasi dana pendamping Rp 75.000.000.', NULL, NULL),
(2, 'Penelitian', 102, 'Usulan Hibah Riset Terapan Kolaboratif Industri FTIK - PT Telkom', 2, 'Dr. Rony Setiawan, M.T.', 1, '2026-08-22', 'Pending', 'Usulan kemitraan riset machine learning untuk smart optical network senilai Rp 120.000.000.', NULL, NULL),
(3, 'Anggaran', 103, 'Revisi Anggaran Pembelian Lisensi Software Lab Cyber Security & Cloud', 3, 'Dr. Siti Nurhaliza, M.Cs.', 2, '2026-08-23', 'Pending', 'Pengalihan pos anggaran belanja ATK ke lisensi VMware & Fortinet sebesar Rp 45.000.000.', NULL, NULL),
(4, 'Kerja Sama', 104, 'Draft MoA Kerja Sama Program Double Degree dengan Universiti Teknologi Malaysia', 2, 'Prof. Dr. Ir. Hendra Wijaya', 1, '2026-08-20', 'Approved', 'Disetujui untuk penandatanganan resmi.', 'Prof. Dr. Ir. Hendra Wijaya', '2026-08-21 16:30:00');

-- 26. Critical Alerts Engine (PRD Section 8.1 & 32)
INSERT INTO `critical_alerts` (`id`, `alert_type`, `severity`, `title`, `description`, `target_url`, `record_id`, `is_resolved`) VALUES
(1, 'Akreditasi', 'Critical', 'Akreditasi Prodi Sistem Informasi Berakhir 88 Hari Lagi', 'Masa berlaku akreditasi LAM-INFOKOM akan habis pada 20 November 2026. Progress LED & LKPS saat ini baru 76%.', 'accreditation', 2, 0),
(2, 'RTL', 'Critical', 'Tindak Lanjut Rapat (RTL) Terlambat 2 Hari', 'RTL-2026-001 (Finalisasi LED Bab 4) melewati deadline 22 Agustus 2026 dan belum diserahkan.', 'meetings/rtl', 1, 0),
(3, 'Dosen', 'Warning', '2 Dosen Memiliki Beban BKD & Kehadiran di Bawah Batas Minimum', 'Dosen Nurlaila Sari (kehadiran 72%) dan Ratna Juwita (kehadiran 68.5%) berpotensi tidak lulus BKD semester ini.', 'lecturers', 8, 0),
(4, 'Mahasiswa', 'Critical', '2 Mahasiswa Terindikasi Risiko Kritis Drop Out (DO)', 'Mahasiswa Farhan Maulana (Semester 10, IPK 1.85) dan Kevin Christian (IPK 1.95) membutuhkan intervensi segera.', 'students/early-warning', 3, 0),
(5, 'Deadline', 'Warning', 'Kerja Sama Google Cloud Academic Program Berakhir dalam 22 Hari', 'Masa berlaku MoU Google Cloud akan berakhir pada 15 September 2026. Perpanjangan perlu segera disahkan.', 'cooperations', 2, 0),
(6, 'SPMI', 'Attention', 'Rasio Dosen Bergelar Doktor (S3) Masih 42.8% (Target 50%)', 'Standar STD-SDM-01 belum terpenuhi secara menyeluruh di tingkat fakultas.', 'quality', 5, 0);

-- 27. Surveys
INSERT INTO `surveys` (`id`, `category`, `period_year`, `respondents_count`, `average_score`, `satisfaction_percentage`, `status`, `summary`) VALUES
(1, 'Mahasiswa', 2026, 1250, 3.65, 91.25, 'Sangat Baik', 'Tingkat kepuasan mahasiswa terhadap kompetensi dosen dan fasilitas cloud lab sangat tinggi.'),
(2, 'Dosen', 2026, 52, 3.52, 88.00, 'Sangat Baik', 'Kepuasan dosen terhadap transparansi penilaian BKD dan dukungan insentif riset sangat positif.'),
(3, 'Tendik', 2026, 28, 3.40, 85.00, 'Baik', 'Apresiasi terhadap iklim kerja dan kebutuhan pelatihan sistem digital berkala.'),
(4, 'Alumni', 2026, 320, 3.70, 92.50, 'Sangat Baik', 'Alumni menilai materi kurikulum terapan relevan dengan kebutuhan industri digital saat ini.'),
(5, 'Pengguna Lulusan', 2026, 85, 3.62, 90.50, 'Sangat Baik', 'Industri menilai integritas, etos kerja, dan kemampuan pemecahan masalah alumni FTIK sangat memuaskan.');

-- 28. Initial Audit Logs (PRD Section 29)
INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `module`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`) VALUES
(1, 2, 'dekan', 'LOGIN', 'auth', '2', NULL, '{"status":"success"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(2, 2, 'dekan', 'APPROVE', 'approvals', '4', '{"status":"Pending"}', '{"status":"Approved","notes":"Disetujui untuk penandatanganan"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(3, 1, 'admin', 'UPDATE', 'settings', 'general', '{"theme":"light"}', '{"theme":"auto"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

-- 29. Notifications
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `target_url`, `is_read`) VALUES
(1, 2, 'Permohonan Approval Baru', 'Bambang Kusumo, Ph.D. mengajukan proposal Seminar Internasional IEEE AI 2026 untuk disetujui.', 'approval', 'command-center/approvals', 0),
(2, 2, 'Peringatan Akreditasi', 'Prodi Sistem Informasi tersisa 88 hari sebelum akreditasi berakhir. Segera tinjau progres LED.', 'alert', 'accreditation', 0),
(3, 2, 'RTL Terlambat', 'Tindak lanjut RTL-2026-001 melewati batas waktu 22 Agustus 2026.', 'rtl', 'meetings/rtl', 0),
(4, 2, 'Agenda Dekan Hari Ini', 'Rapat Pleno Senat Fakultas dijadwalkan pada 28 Agustus 2026 pukul 09:00 WIB.', 'reminder', 'meetings', 0);

-- 30. App Settings
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `category`, `description`) VALUES
(1, 'app_name', 'Dean Executive Information System', 'general', 'Nama Aplikasi'),
(2, 'app_short_name', 'DEIS', 'general', 'Singkatan Nama Aplikasi'),
(3, 'faculty_name', 'Fakultas Teknologi Informasi dan Komputer', 'general', 'Nama Fakultas'),
(4, 'dean_name', 'Prof. Dr. Ir. Hendra Wijaya, M.Kom.', 'general', 'Nama Dekan'),
(5, 'dean_nip', '197805122003121002', 'general', 'NIP Dekan'),
(6, 'active_academic_year', '2025/2026 Genap', 'academic', 'Tahun Akademik Aktif'),
(7, 'currency_symbol', 'Rp ', 'general', 'Simbol Mata Uang'),
(8, 'ai_assistant_enabled', '1', 'ai', 'Fitur AI Assistant Aktif');

-- 31. Practicum Modules & Lab Verification Seeds (1 MK = 1 Modul)
INSERT INTO `practicum_modules` (`id`, `study_program_id`, `academic_year_id`, `semester`, `course_code`, `course_name`, `sks_lab`, `lab_name`, `lecturer_name`, `assistant_name`, `is_module_ready`, `module_file`, `rubric_file`, `logbook_status`, `status`, `dekan_notes`, `kaprodi_feedback`, `last_confirmed_at`) VALUES
-- Teknik Informatika (10 MK = 10 Target Modul)
(1, 1, 1, 1, 'TI101', 'Dasar Pemrograman & Algoritma Lab', 1, 'Lab Rekayasa Perangkat Lunak', 'Dr. Rony Setiawan, S.Kom., M.T.', 'Ahmad Fauzi (Aslab Utama)', 1, 'Modul_Dasar_Pemrograman_TI101.pdf', 'Rubrik_Penilaian_Algoritma.pdf', 'Lengkap', 'Terpenuhi', 'Modul praktikum 1/1 terverifikasi lengkap.', NULL, NOW()),
(2, 1, 1, 1, 'TI102', 'Pengantar Teknologi Informasi & Hardware Lab', 1, 'Lab Komputasi Dasar & Hardware', 'Hendra Prasetyo, M.Kom.', 'Bima Sakti (Laboran)', 1, 'Modul_Hardware_PTI_TI102.pdf', 'Rubrik_Hardware.pdf', 'Lengkap', 'Terpenuhi', 'Modul praktikum 1/1 terverifikasi.', NULL, NOW()),
(3, 1, 1, 2, 'TI201', 'Struktur Data & Algoritma Terapan Lab', 1, 'Lab Rekayasa Perangkat Lunak', 'Dr. Rony Setiawan, S.Kom., M.T.', 'Dimas Aditya (Aslab)', 1, 'Modul_Struktur_Data_TI201.pdf', 'Rubrik_Struktur_Data.pdf', 'Lengkap', 'Terpenuhi', 'Modul praktikum 1/1 terverifikasi.', NULL, NOW()),
(4, 1, 1, 2, 'TI202', 'Sistem Operasi & Linux Shell Lab', 1, 'Lab Jaringan & Cloud', 'Fajar Nugraha, M.Kom.', 'Rian Hidayat (Aslab OS)', 1, 'Modul_Linux_Shell_TI202.pdf', 'Rubrik_Linux.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 lengkap.', NULL, NOW()),
(5, 1, 1, 3, 'TI301', 'Pemrograman Berorientasi Objek (Java/Spring) Lab', 1, 'Lab Rekayasa Perangkat Lunak', 'Fajar Nugraha, M.Kom.', 'Rizky Ramadhan (Aslab OOP)', 1, 'Modul_OOP_Java_TI301.pdf', 'Rubrik_OOP.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(6, 1, 1, 3, 'TI302', 'Jaringan Komputer & Cyber Security Lab', 1, 'Lab Jaringan & Keamanan Siber', 'Dr. Eng. Wahyu Pratama, M.T.', 'Farhan Kamil (Laboran Jarkom)', 0, NULL, NULL, 'Belum Ada', 'Dikonfirmasi ke Kaprodi', 'Dokumen modul praktikum Cisco & Wireshark belum diunggah oleh aslab. Mohon Kaprodi follow up.', 'Sedang divalidasi oleh tim dosen pengampu.', NOW()),
(7, 1, 1, 4, 'TI401', 'Basis Data Lanjut & Database Tuning Lab', 1, 'Lab Database & Sistem Enterprise', 'Maya Anggraini, M.Kom.', 'Irfan Hakim (Aslab DB)', 1, 'Modul_DB_Tuning_TI401.pdf', 'Rubrik_DB.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(8, 1, 1, 4, 'TI402', 'Pemrograman Web Framework (Laravel & React) Lab', 1, 'Lab Rekayasa Perangkat Lunak', 'Dr. Rony Setiawan, S.Kom., M.T.', 'Aldi Taher (Aslab Web)', 1, 'Modul_Fullstack_Web_TI402.pdf', 'Rubrik_Web.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(9, 1, 1, 5, 'TI501', 'Rekayasa Perangkat Lunak Terdistribusi & Microservices Lab', 1, 'Lab Cloud Computing & Distributed Systems', 'Dr. Rony Setiawan, S.Kom., M.T.', 'Gilang Pratama (Aslab Cloud)', 1, 'Modul_Microservices_Docker_TI501.pdf', 'Rubrik_Docker.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(10, 1, 1, 6, 'TI601', 'Mobile App Development (Flutter/Android) Lab', 1, 'Lab Rekayasa Perangkat Lunak', 'Fajar Nugraha, M.Kom.', 'Bagus Wicaksono (Aslab Mobile)', 1, 'Modul_Flutter_Mobile_TI601.pdf', 'Rubrik_Mobile.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
-- Sistem Informasi (6 MK = 6 Target Modul)
(11, 2, 1, 1, 'SI101', 'Algoritma & Logika Bisnis Lab', 1, 'Lab Sistem Enterprise & Database', 'Dr. Siti Nurhaliza, M.Cs.', 'Annisa Rahma (Aslab SI)', 1, 'Modul_Algoritma_Bisnis_SI101.pdf', 'Rubrik_SI101.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(12, 2, 1, 2, 'SI201', 'Basis Data & SQL Enterprise Lab', 1, 'Lab Sistem Enterprise & Database', 'Maya Anggraini, M.Kom.', 'Irfan Hakim (Aslab DB)', 1, 'Modul_Basis_Data_SI201.pdf', 'Rubrik_DB_SI.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(13, 2, 1, 3, 'SI301', 'Analisis & Desain Sistem Informasi (UML/BPMN) Lab', 1, 'Lab Business Intelligence & Analytics', 'Dr. Siti Nurhaliza, M.Cs.', 'Putri Wulandari (Aslab BPMN)', 1, 'Modul_ADSI_SI301.pdf', 'Rubrik_ADSI.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(14, 2, 1, 4, 'SI401', 'Enterprise Resource Planning (ERP Odoo) Lab', 1, 'Lab Sistem Enterprise & Database', 'Bambang Triyono, M.T.', 'Kevin Sanjaya (Laboran ERP)', 0, NULL, NULL, 'Sebagian', 'Dikonfirmasi ke Kaprodi', 'Modul praktikum Odoo 16 masih draft. Mohon Kaprodi SI segera memfinalisasi.', 'Tim laboratorium sedang menyelesaikan panduan praktikum.', NOW()),
(15, 2, 1, 5, 'SI501', 'Business Intelligence & Data Visualization Lab', 1, 'Lab Business Intelligence & Analytics', 'Dr. Siti Nurhaliza, M.Cs.', 'Tania Melati (Aslab BI)', 1, 'Modul_PowerBI_Tableau_SI501.pdf', 'Rubrik_BI.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(16, 2, 1, 6, 'SI601', 'Audit Sistem Informasi & Cobit Framework Lab', 1, 'Lab Tata Kelola & Audit TI', 'Bambang Triyono, M.T.', 'Dina Mariana (Aslab Audit)', 1, 'Modul_Audit_SI_Cobit_SI601.pdf', 'Rubrik_Audit.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
-- Sains Data (5 MK = 5 Target Modul)
(17, 3, 1, 1, 'DS101', 'Python for Data Science & Analytics Lab', 1, 'Lab Data Science & AI', 'Dr. Agus Pratama, S.Si., M.Sc.', 'Zaky Mubarok (Aslab Python)', 1, 'Modul_Python_Pandas_DS101.pdf', 'Rubrik_Python_DS.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(18, 3, 1, 2, 'DS201', 'Statistika Komputasi & R Programming Lab', 1, 'Lab Data Science & AI', 'Dr. Agus Pratama, S.Si., M.Sc.', 'Nabila Putri (Aslab R)', 1, 'Modul_R_Stats_DS201.pdf', 'Rubrik_R.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(19, 3, 1, 3, 'DS301', 'Machine Learning & Deep Learning Lab', 1, 'Lab Data Science & AI', 'Dr. Agus Pratama, S.Si., M.Sc.', 'Nadya Safira (Aslab AI/ML)', 1, 'Modul_ML_PyTorch_DS301.pdf', 'Rubrik_ML.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(20, 3, 1, 4, 'DS401', 'Data Mining & Web Scraping Lab', 1, 'Lab Data Science & AI', 'Dr. Eng. Wahyu Pratama, M.T.', 'Fikri Haikal (Aslab Mining)', 1, 'Modul_Data_Mining_DS401.pdf', 'Rubrik_Mining.pdf', 'Lengkap', 'Terpenuhi', 'Modul 1/1 terverifikasi.', NULL, NOW()),
(21, 3, 1, 5, 'DS501', 'Big Data Processing & Hadoop/Spark Lab', 1, 'Lab High Performance Computing & Big Data', 'Dr. Eng. Wahyu Pratama, M.T.', 'Taufik Hidayat (Laboran HPC)', 0, NULL, NULL, 'Sebagian', 'Dikonfirmasi ke Kaprodi', 'Modul praktikum Apache Spark & PySpark cluster belum lengkap.', 'Sedang disusun oleh tim asisten lab HPC.', NOW());

SET FOREIGN_KEY_CHECKS = 1;
