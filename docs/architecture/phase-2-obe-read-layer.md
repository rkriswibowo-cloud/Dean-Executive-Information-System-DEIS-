# Fase 2 — Lapisan Baca Master Data OBE

Status: **diimplementasikan**  
Tanggal: **19 Oktober 2026**  
Ruang lingkup perubahan: **hanya proyek DEIS**

## Tujuan

Menyediakan satu jalur integrasi read-only dari DEIS ke master akademik OBE tanpa mengubah proyek maupun database OBE. Lapisan ini menjadi fondasi penggantian model master lokal secara bertahap.

## Komponen

### `ObeReadConnection`

- Membuat koneksi terpisah langsung ke database yang dikonfigurasi melalui `obe_dbname`.
- Tidak mengekspos objek PDO kepada repository/controller.
- Hanya menerima query yang diawali `SELECT`.
- Menolak kata kunci mutasi, lock, `FOR UPDATE`, dan ekspor file.
- Mendukung akun khusus melalui `OBE_DB_USERNAME` dan `OBE_DB_PASSWORD`.

Pembatasan di kode adalah defense-in-depth. Lingkungan produksi tetap wajib menggunakan akun MySQL yang hanya memiliki privilege `SELECT` pada `obe_cpl`.

### Repository yang tersedia

| Repository | Sumber OBE | Fungsi awal |
|---|---|---|
| `AcademicYearRepository` | `tahun_akademik` | Semua, aktif, detail |
| `StudyProgramRepository` | `program_studi` | Semua aktif, semua status, detail |
| `LecturerRepository` | `users`, `roles`, `program_studi` | Semua/filter prodi, detail |
| `StudentRepository` | `users`, `roles`, `program_studi` | Semua/filter prodi, detail |
| `CurriculumRepository` | `kurikulum`, `program_studi` | Semua/filter prodi/status, detail |
| `CourseRepository` | `mata_kuliah`, `kurikulum`, `program_studi` | Semua/filter prodi/kurikulum/status, detail |

### Normalisasi data

`MasterDataNormalizer` mengubah nama kolom OBE menjadi kontrak stabil untuk DEIS. Semua entitas membawa `obe_id`; controller baru tidak boleh menggunakan ID lokal DEIS sebagai identitas master OBE.

### Health check

`ObeHealthService::check()` mengembalikan:

- status ketersediaan;
- nama database yang terhubung;
- jumlah mata kuliah;
- latensi koneksi;
- pesan kesalahan yang aman untuk logging internal.

## Konfigurasi

Variabel lingkungan produksi:

```text
OBE_DB_USERNAME=deis_obe_reader
OBE_DB_PASSWORD=<secret>
```

Akun yang direkomendasikan hanya memperoleh:

```sql
GRANT SELECT ON obe_cpl.* TO 'deis_obe_reader'@'host-deis';
```

Pembuatan akun/`GRANT` tidak dijalankan oleh proyek DEIS dan harus dilakukan administrator database.

## Pengujian

Jalankan:

```powershell
php tests/obe_read_layer_test.php
```

Tes memverifikasi health check, kontrak keenam repository, jumlah baseline, penolakan `UPDATE`, penolakan `SELECT INTO OUTFILE`, dan fingerprint tabel mata kuliah sebelum/sesudah tes.

## Batas Fase 2

- Belum mengganti route/menu DEIS yang ada.
- Belum menghapus tabel master lokal.
- Belum membuat pemetaan ID lokal–OBE.
- Belum mengubah data atau source code OBE.
- `ObeSyncService` lama belum dihapus agar kompatibilitas halaman saat ini tidak terputus; penggantiannya dilakukan setelah pilot migrasi.

## Langkah berikutnya

Fase 3/pilot integrasi:

1. gunakan `StudyProgramRepository` dan `AcademicYearRepository` pada halaman referensi DEIS;
2. buat tabel mapping/extension yang menyimpan `obe_id` untuk data DEIS yang masih memiliki FK lokal;
3. ubah POST master prodi/tahun akademik menjadi tidak aktif;
4. tampilkan status OBE melalui health check;
5. tambahkan pengujian controller dan fallback gangguan yang eksplisit.
