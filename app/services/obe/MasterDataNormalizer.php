<?php
namespace App\Services\Obe;

final class MasterDataNormalizer
{
    public static function academicYear(array $row): array
    {
        $label = trim((string)$row['nama']);
        preg_match('/^(\d{4}\/\d{4})\s+(Ganjil|Genap)$/iu', $label, $matches);
        return [
            'obe_id' => (int)$row['id'],
            'name' => $label,
            'year' => $matches[1] ?? $label,
            'semester' => isset($matches[2]) ? ucfirst(mb_strtolower($matches[2], 'UTF-8')) : null,
            'label' => $label,
            'is_active' => (bool)$row['is_active'],
        ];
    }

    public static function studyProgram(array $row): array
    {
        return [
            'obe_id' => (int)$row['id'],
            'code' => (string)$row['kode'],
            'name' => (string)$row['nama'],
            'degree' => (string)$row['jenjang'],
            'faculty' => (string)$row['fakultas'],
            'head_name' => $row['nama_kaprodi'] ?: null,
            'head_nidn' => $row['nidn_kaprodi'] ?: null,
            'status' => (string)$row['status'],
        ];
    }

    public static function person(array $row, string $type): array
    {
        return [
            'obe_id' => (int)$row['id'],
            'type' => $type,
            'username' => (string)$row['username'],
            'name' => (string)$row['nama_lengkap'],
            'email' => $row['email'] ?: null,
            'nidn' => $row['nidn'] ?: null,
            'nuptk' => $row['nuptk'] ?: null,
            'study_program_id' => isset($row['prodi_id']) ? (int)$row['prodi_id'] : null,
            'study_program_name' => $row['prodi_nama'] ?? null,
            'homebase' => $row['homebase'] ?: null,
        ];
    }

    public static function curriculum(array $row): array
    {
        return [
            'obe_id' => (int)$row['id'],
            'study_program_id' => (int)$row['prodi_id'],
            'study_program_name' => (string)$row['prodi_nama'],
            'code' => (string)$row['kode'],
            'name' => (string)$row['nama'],
            'start_year' => (int)$row['tahun_mulai'],
            'version' => (string)$row['versi'],
            'status' => (string)$row['status'],
        ];
    }

    public static function course(array $row): array
    {
        return [
            'obe_id' => (int)$row['id'],
            'curriculum_id' => (int)$row['kurikulum_id'],
            'curriculum_name' => (string)$row['kurikulum_nama'],
            'study_program_id' => (int)$row['prodi_id'],
            'study_program_name' => (string)$row['prodi_nama'],
            'code' => (string)$row['kode'],
            'name' => (string)$row['nama'],
            'theory_credits' => (int)$row['sks_teori'],
            'practice_credits' => (int)$row['sks_praktek'],
            'credits' => (int)$row['sks_teori'] + (int)$row['sks_praktek'],
            'semester' => (int)$row['semester'],
            'nature' => (string)$row['sifat'],
            'knowledge_group' => $row['kelompok_keilmuan'] ?: null,
            'status' => (string)$row['status'],
        ];
    }
}
