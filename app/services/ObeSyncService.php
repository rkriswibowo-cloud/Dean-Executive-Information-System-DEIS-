<?php
namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;

class ObeSyncService {
    private PDO $db;
    private string $obeDbName;

    public function __construct() {
        $this->db = Database::getConnection();
        $config = require __DIR__ . '/../config/database.php';
        $this->obeDbName = $config['obe_dbname'] ?? 'obe_cpl';
    }

    /**
     * Memeriksa apakah database OBE tersedia dan dapat diakses
     */
    public function isObeAvailable(): bool {
        try {
            $stmt = $this->db->query("SHOW DATABASES LIKE '{$this->obeDbName}'");
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Mengambil daftar mata kuliah langsung dari OBE secara realtime (Opsi 1)
     */
    public function getRealtimeObeCourses(?int $obeProdiId = null): array {
        if (!$this->isObeAvailable()) {
            return [];
        }

        $sql = "
            SELECT 
                m.id as obe_mk_id,
                m.kode as code,
                m.nama as name,
                (m.sks_teori + m.sks_praktek) as sks,
                m.semester,
                m.sifat,
                m.kelompok_keilmuan,
                k.nama as kurikulum_nama,
                k.tahun_mulai as kurikulum_year,
                p.id as obe_prodi_id,
                p.nama as prodi_nama,
                p.jenjang as prodi_jenjang,
                -- Cek data RPS dari tabel rps obe_cpl
                r.id as obe_rps_id,
                r.versi as rps_versi,
                r.updated_at as rps_updated_at,
                r.bentuk_pembelajaran,
                r.jenis_penilaian_json,
                r.dosen_pengampu as rps_dosen_pengampu,
                pjmk.nama_lengkap as pjmk_nama,
                pjmk.nidn as pjmk_nidn,
                ta.nama as pjmk_tahun_akademik,
                dl.id as deis_lecturer_id,
                dl.name as deis_lecturer_name,
                -- Deteksi status RPS
                CASE 
                    WHEN r.id IS NOT NULL AND (r.sistem_penilaian_desc IS NOT NULL OR r.rencana_evaluasi_json IS NOT NULL) THEN 'Lengkap'
                    WHEN r.id IS NOT NULL THEN 'Revisi'
                    ELSE 'Belum Lengkap'
                END as rps_status,
                -- Deteksi Case Method (IKU-7) dari bentuk pembelajaran atau jenis evaluasi
                CASE 
                    WHEN r.bentuk_pembelajaran LIKE '%Case%' 
                      OR r.bentuk_pembelajaran LIKE '%Proyek%' 
                      OR r.bentuk_pembelajaran LIKE '%Project%' 
                      OR r.jenis_penilaian_json LIKE '%STK%' 
                      OR r.jenis_penilaian_json LIKE '%PRJ%' 
                    THEN 1 ELSE 0 
                END as is_case_method
            FROM `{$this->obeDbName}`.mata_kuliah m
            JOIN `{$this->obeDbName}`.kurikulum k ON m.kurikulum_id = k.id
            JOIN `{$this->obeDbName}`.program_studi p ON k.prodi_id = p.id
            LEFT JOIN `{$this->obeDbName}`.rps r ON m.id = r.mata_kuliah_id
            LEFT JOIN `{$this->obeDbName}`.mata_kuliah_pengampu pm
                ON pm.mata_kuliah_id = m.id
                AND pm.status = 'ACTIVE'
                AND pm.tahun_akademik_id = (
                    SELECT ta_active.id
                    FROM `{$this->obeDbName}`.tahun_akademik ta_active
                    WHERE ta_active.is_active = 1
                    ORDER BY ta_active.id DESC
                    LIMIT 1
                )
            LEFT JOIN `{$this->obeDbName}`.tahun_akademik ta
                ON ta.id = pm.tahun_akademik_id AND ta.is_active = 1
            LEFT JOIN `{$this->obeDbName}`.users pjmk
                ON pjmk.id = pm.pjmk_user_id AND ta.id IS NOT NULL
            LEFT JOIN lecturers dl
                ON NULLIF(TRIM(dl.nidn), '') = NULLIF(TRIM(pjmk.nidn), '')
            WHERE m.status = 'ACTIVE'
        ";

        $params = [];
        if ($obeProdiId) {
            $sql .= " AND p.id = :pid";
            $params['pid'] = $obeProdiId;
        }

        $sql .= " ORDER BY p.nama ASC, m.semester ASC, m.kode ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menyinkronkan seluruh mata kuliah dari OBE ke tabel courses di DEIS (Opsi 2)
     */
    public function syncCoursesFromObe(): array {
        throw new \LogicException('Sinkronisasi salinan master dinonaktifkan. Gunakan lapisan baca realtime OBE.');
        /* Legacy implementation retained temporarily for migration traceability.
        if (!$this->isObeAvailable()) {
            throw new Exception("Database '{$this->obeDbName}' tidak ditemukan atau tidak dapat diakses.");
        }

        // 1. Ambil pemetaan prodi DEIS berdasarkan kecocokan nama
        $deisProdis = $this->db->query("SELECT id, name FROM study_programs")->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // 2. Ambil seluruh mata kuliah aktif dari OBE beserta status RPS-nya
        $obeCourses = $this->getRealtimeObeCourses();
        if (empty($obeCourses)) {
            return [
                'inserted' => 0,
                'updated'  => 0,
                'total'    => 0,
                'message'  => 'Tidak ada mata kuliah aktif di sistem OBE.'
            ];
        }

        $inserted = 0;
        $updated = 0;

        $checkStmt = $this->db->prepare("SELECT id FROM courses WHERE code = :code LIMIT 1");
        $insertStmt = $this->db->prepare("
            INSERT INTO courses (
                study_program_id, code, name, sks, semester, kurikulum_year,
                lecturer_id, rps_status, cpl_status, case_method_flag, rps_document_url, rps_updated_at, rps_notes
            ) VALUES (
                :prodi_id, :code, :name, :sks, :semester, :kurikulum_year,
                :lecturer_id, :rps_status, :cpl_status, :case_method, :doc_url, :updated_at, :notes
            )
        ");

        $updateStmt = $this->db->prepare("
            UPDATE courses SET 
                name = :name,
                sks = :sks,
                semester = :semester,
                kurikulum_year = :kurikulum_year,
                lecturer_id = COALESCE(:lecturer_id, lecturer_id),
                rps_status = :rps_status,
                cpl_status = :cpl_status,
                case_method_flag = :case_method,
                rps_document_url = COALESCE(:doc_url, rps_document_url),
                rps_updated_at = :updated_at,
                rps_notes = :notes
            WHERE id = :id
        ");

        foreach ($obeCourses as $mk) {
            // Cocokkan prodi
            $matchedProdiId = 1; // Default ke prodi 1
            foreach ($deisProdis as $pid => $pname) {
                if (stripos($mk['prodi_nama'], $pname) !== false || stripos($pname, $mk['prodi_nama']) !== false) {
                    $matchedProdiId = $pid;
                    break;
                }
            }

            $cplStatus = ($mk['rps_status'] === 'Lengkap') ? 'Memenuhi Standar OBE' : 'Perlu Kelengkapan RPS';
            $docUrl = $mk['obe_rps_id'] ? "http://localhost/obe/rps/preview/{$mk['obe_mk_id']}" : null;
            $notes = "Disinkronkan otomatis dari Sistem OBE (Kurikulum: {$mk['kurikulum_nama']}, Sifat: {$mk['sifat']})";
            $updatedAt = !empty($mk['rps_updated_at']) ? date('Y-m-d', strtotime($mk['rps_updated_at'])) : date('Y-m-d');

            // Cek apakah sudah ada di courses berdasarkan KODE
            $checkStmt->execute(['code' => $mk['code']]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $updateStmt->execute([
                    'id'             => $existing['id'],
                    'name'           => $mk['name'],
                    'sks'            => max(1, (int)$mk['sks']),
                    'semester'       => max(1, (int)$mk['semester']),
                    'kurikulum_year' => $mk['kurikulum_year'] ?: '2024',
                    'lecturer_id'    => !empty($mk['deis_lecturer_id']) ? (int)$mk['deis_lecturer_id'] : null,
                    'rps_status'     => $mk['rps_status'],
                    'cpl_status'     => $cplStatus,
                    'case_method'    => $mk['is_case_method'],
                    'doc_url'        => $docUrl,
                    'updated_at'     => $updatedAt,
                    'notes'          => $notes
                ]);
                $updated++;
            } else {
                $insertStmt->execute([
                    'prodi_id'       => $matchedProdiId,
                    'code'           => $mk['code'],
                    'name'           => $mk['name'],
                    'sks'            => max(1, (int)$mk['sks']),
                    'semester'       => max(1, (int)$mk['semester']),
                    'kurikulum_year' => $mk['kurikulum_year'] ?: '2024',
                    'lecturer_id'    => !empty($mk['deis_lecturer_id']) ? (int)$mk['deis_lecturer_id'] : null,
                    'rps_status'     => $mk['rps_status'],
                    'cpl_status'     => $cplStatus,
                    'case_method'    => $mk['is_case_method'],
                    'doc_url'        => $docUrl,
                    'updated_at'     => $updatedAt,
                    'notes'          => $notes
                ]);
                $inserted++;
            }
        }

        return [
            'inserted' => $inserted,
            'updated'  => $updated,
            'total'    => count($obeCourses)
        ]; */
    }
}
