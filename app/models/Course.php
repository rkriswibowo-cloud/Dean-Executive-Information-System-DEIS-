<?php
namespace App\Models;

use App\Core\Model;

class Course extends Model {
    protected string $table = 'courses';

    public function allWithLecturerAndProgram(?int $programId = null, bool $preferObePjmk = false): array {
        $sql = "SELECT c.*, sp.name as program_name, l.name as lecturer_name,
                       l.name as deis_lecturer_name
                FROM courses c 
                JOIN study_programs sp ON c.study_program_id = sp.id 
                LEFT JOIN lecturers l ON c.lecturer_id = l.id";
        $params = [];
        if ($programId) {
            $sql .= " WHERE c.study_program_id = :pid";
            $params['pid'] = $programId;
        }
        $sql .= " ORDER BY c.semester ASC, c.code ASC";
        $courses = $this->rawFetch($sql, $params);

        if (!$preferObePjmk) {
            return $courses;
        }

        // Begitu konsumen meminta data PJMK OBE, jangan pernah menyisakan
        // identitas dosen lokal lama sebagai fallback.
        foreach ($courses as &$course) {
            $course['lecturer_name'] = null;
            $course['lecturer_source'] = 'OBE tidak tersedia';
        }
        unset($course);

        // Untuk monitoring/audit, nama PJMK OBE adalah sumber utama. Relasi
        // lecturer_id DEIS tetap dipertahankan untuk modul transaksional lain.
        try {
            $obeRows = $this->rawFetch("SELECT m.kode,
                    k.tahun_mulai,
                    u.nama_lengkap AS pjmk_nama,
                    u.nidn AS pjmk_nidn,
                    r.dosen_pengampu AS rps_dosen_pengampu
                FROM obe_cpl.mata_kuliah m
                JOIN obe_cpl.kurikulum k ON k.id = m.kurikulum_id
                LEFT JOIN obe_cpl.rps r ON r.mata_kuliah_id = m.id
                LEFT JOIN obe_cpl.mata_kuliah_pengampu pm
                    ON pm.mata_kuliah_id = m.id
                    AND pm.status = 'ACTIVE'
                    AND pm.tahun_akademik_id = (
                        SELECT ta_active.id FROM obe_cpl.tahun_akademik ta_active
                        WHERE ta_active.is_active = 1
                        ORDER BY ta_active.id DESC LIMIT 1
                    )
                LEFT JOIN obe_cpl.users u ON u.id = pm.pjmk_user_id
                WHERE m.status = 'ACTIVE'
                ORDER BY k.tahun_mulai ASC, m.id ASC");

            $pjmkByCode = [];
            foreach ($obeRows as $obeRow) {
                $name = trim((string)($obeRow['pjmk_nama'] ?? ''));
                $source = 'penugasan OBE';
                if ($name === '') {
                    $rpsName = trim(strtok((string)($obeRow['rps_dosen_pengampu'] ?? ''), "\r\n"));
                    $rpsName = preg_replace('/^\s*\d+\.\s*/u', '', $rpsName);
                    $name = trim((string)preg_replace('/\s*\(PJMK\).*$/iu', '', $rpsName));
                    $source = 'dokumen RPS OBE';
                }
                $pjmkByCode[$obeRow['kode']] = [
                    'name' => $name !== '' ? $name : null,
                    'nidn' => $obeRow['pjmk_nidn'] ?? null,
                    'source' => $name !== '' ? $source : 'belum ditetapkan di OBE',
                ];
            }

            // Laporan OBE tidak boleh menampilkan penugasan lokal lama. Jika
            // suatu kode tidak ada/PJMK belum ada di OBE, tampilkan kosong.
            foreach ($courses as &$course) {
                $course['lecturer_source'] = 'belum ditetapkan di OBE';
            }
            unset($course);

            foreach ($courses as &$course) {
                if (array_key_exists($course['code'], $pjmkByCode)) {
                    $course['lecturer_name'] = $pjmkByCode[$course['code']]['name'];
                    $course['obe_pjmk_nidn'] = $pjmkByCode[$course['code']]['nidn'];
                    $course['lecturer_source'] = $pjmkByCode[$course['code']]['source'];
                }
            }
            unset($course);
        } catch (\Throwable $e) {
            // Fail closed: nama lokal tetap kosong agar data lama tidak tampil
            // seolah-olah berasal dari OBE.
        }

        return $courses;
    }

    public function getRpsSummary(): array {
        $sql = "SELECT 
                    COUNT(*) as total_courses,
                    SUM(CASE WHEN rps_status = 'Lengkap' THEN 1 ELSE 0 END) as complete_count,
                    SUM(CASE WHEN rps_status = 'Revisi' THEN 1 ELSE 0 END) as revision_count,
                    SUM(CASE WHEN rps_status = 'Belum Lengkap' THEN 1 ELSE 0 END) as incomplete_count,
                    SUM(CASE WHEN case_method_flag = 1 THEN 1 ELSE 0 END) as case_method_count
                FROM courses";
        $res = $this->rawFetch($sql);
        return $res[0] ?? [
            'total_courses' => 0,
            'complete_count' => 0,
            'revision_count' => 0,
            'incomplete_count' => 0,
            'case_method_count' => 0
        ];
    }

    public function getRpsByProdi(): array {
        $sql = "SELECT sp.id as program_id, sp.name as program_name, sp.degree,
                       COUNT(c.id) as total_courses,
                       SUM(CASE WHEN c.rps_status = 'Lengkap' THEN 1 ELSE 0 END) as complete_count,
                       SUM(CASE WHEN c.rps_status = 'Revisi' THEN 1 ELSE 0 END) as revision_count,
                       SUM(CASE WHEN c.rps_status = 'Belum Lengkap' THEN 1 ELSE 0 END) as incomplete_count,
                       SUM(CASE WHEN c.case_method_flag = 1 THEN 1 ELSE 0 END) as case_method_count
                FROM study_programs sp
                LEFT JOIN courses c ON sp.id = c.study_program_id
                GROUP BY sp.id, sp.name, sp.degree
                ORDER BY sp.id ASC";
        return $this->rawFetch($sql);
    }
}
