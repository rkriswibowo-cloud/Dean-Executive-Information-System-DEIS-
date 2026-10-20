<?php
namespace App\Services\Obe;

final class AcademicReferenceRepository extends BaseObeRepository
{
    public function curriculumRules(): array
    {
        return $this->connection->fetchAll(
            'SELECT ak.*, p.nama AS prodi_nama, k.nama AS kurikulum_nama
             FROM aturan_kurikulum ak
             JOIN kurikulum k ON k.id = ak.kurikulum_id
             JOIN program_studi p ON p.id = k.prodi_id
             ORDER BY p.nama ASC, ak.id DESC'
        );
    }

    public function graduateProfiles(): array
    {
        return $this->connection->fetchAll(
            'SELECT pl.*, p.nama AS prodi_nama, k.nama AS kurikulum_nama
             FROM profil_lulusan pl
             JOIN kurikulum k ON k.id = pl.kurikulum_id
             JOIN program_studi p ON p.id = k.prodi_id
             ORDER BY p.nama ASC, pl.kode ASC'
        );
    }

    public function learningOutcomes(): array
    {
        return $this->connection->fetchAll(
            'SELECT c.*, p.nama AS prodi_nama, k.nama AS kurikulum_nama
             FROM cpl c
             JOIN kurikulum k ON k.id = c.kurikulum_id
             JOIN program_studi p ON p.id = k.prodi_id
             ORDER BY p.nama ASC, c.kode ASC'
        );
    }

    public function rubrics(): array
    {
        return $this->connection->fetchAll(
            "SELECT r.id, r.kode_jenis, r.jenis, r.nama, r.status,
                    COUNT(DISTINCT rk.id) AS criteria_count,
                    COUNT(DISTINCT rl.id) AS level_count
             FROM rubrik r
             LEFT JOIN rubrik_kriteria rk ON rk.rubrik_id = r.id
             LEFT JOIN rubrik_level rl ON rl.rubrik_id = r.id
             GROUP BY r.id, r.kode_jenis, r.jenis, r.nama, r.status
             ORDER BY r.jenis ASC, r.nama ASC"
        );
    }

    public function counts(): array
    {
        return [
            'academic-years' => (int)$this->connection->scalar('SELECT COUNT(*) FROM tahun_akademik'),
            'lecturers' => (int)$this->connection->scalar("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name IN ('Dosen','Kaprodi')"),
            'students' => (int)$this->connection->scalar("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='Mahasiswa'"),
            'study-programs' => (int)$this->connection->scalar('SELECT COUNT(*) FROM program_studi'),
            'curricula' => (int)$this->connection->scalar('SELECT COUNT(*) FROM kurikulum'),
            'curriculum-rules' => (int)$this->connection->scalar('SELECT COUNT(*) FROM aturan_kurikulum'),
            'graduate-profiles' => (int)$this->connection->scalar('SELECT COUNT(*) FROM profil_lulusan'),
            'learning-outcomes' => (int)$this->connection->scalar('SELECT COUNT(*) FROM cpl'),
            'courses' => (int)$this->connection->scalar('SELECT COUNT(*) FROM mata_kuliah'),
            'rubrics' => (int)$this->connection->scalar('SELECT COUNT(*) FROM rubrik'),
            'imports' => null,
        ];
    }
}
