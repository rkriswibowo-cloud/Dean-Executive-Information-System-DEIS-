# Fase 3–4 — Mapping ID dan Master OBE Read-Only

Status: **diimplementasikan**  
Tanggal: **19 Oktober 2026**  
Perubahan: **proyek dan database DEIS saja**

## Fase 3: Mapping identitas

Tabel `obe_entity_mappings` ditambahkan di `deis_db`. Tabel ini menyimpan hubungan identitas tanpa membuat foreign key ke database OBE.

Kolom penting:

- `entity_type`
- `obe_id`
- `deis_id` (nullable)
- `natural_key`
- `match_status`: `MAPPED`, `UNMAPPED`, atau `CONFLICT`
- `matched_by`
- `last_checked_at`

Rekonsiliasi awal hanya menggunakan natural key sebagai bootstrap:

| Entitas | Natural key awal |
|---|---|
| Tahun akademik | Nama periode + semester |
| Program studi | Kode prodi |
| Dosen | NIDN |
| Mahasiswa | Username OBE ↔ NIM DEIS |
| Mata kuliah | Kode MK |

Jika natural key kosong, tidak ditemukan, ditemukan lebih dari sekali di DEIS, atau dipakai beberapa objek OBE, sistem tidak memaksakan relasi. Hasilnya menjadi `UNMAPPED`/`CONFLICT`.

Hasil baseline:

| Entitas | Total | Mapped | Unmapped | Conflict |
|---|---:|---:|---:|---:|
| Tahun akademik | 8 | 3 | 5 | 0 |
| Program studi | 4 | 4 | 0 | 0 |
| Dosen | 6 | 0 | 4 | 2 |
| Mahasiswa | 31 | 0 | 31 | 0 |
| Mata kuliah | 147 | 55 | 0 | 92 |

Konflik mata kuliah terjadi karena kode yang sama digunakan pada lebih dari satu kurikulum OBE, sedangkan tabel lokal DEIS hanya mempunyai satu objek per kode. Ini sengaja ditandai konflik agar data lintas kurikulum tidak salah dipasangkan.

## Fase 4: Master read-only

Route baru:

```text
GET  /master/obe
POST /master/obe/reconcile
```

Halaman `Master Data OBE` menyediakan 11 kategori:

1. Tahun Akademik
2. Dosen
3. Mahasiswa
4. Program Studi
5. Kurikulum
6. Aturan Kurikulum
7. Profil Lulusan
8. CPL
9. Mata Kuliah
10. Rubrik Penilaian
11. Import Data

Karakteristik halaman:

- data dibaca langsung dari OBE;
- health/latency koneksi ditampilkan;
- tidak memiliki form tambah, edit, atau hapus;
- tombol `Kelola di OBE` membuka modul terkait;
- Import Data hanya memberikan tautan ke OBE;
- rekonsiliasi ID hanya tersedia untuk `super_admin`/`developer` dan menulis ke DEIS saja.

## Route lama

- `/master/study-programs` dialihkan ke kategori Program Studi OBE.
- `/master/academic-years` dialihkan ke kategori Tahun Akademik OBE.
- Semua POST CRUD Program Studi dan Tahun Akademik ditolak dengan pesan read-only.
- POST create/update/delete identitas dosen ditolak.
- Aksi monitoring dosen seperti BKD dan KPI tetap aktif karena merupakan kepemilikan DEIS.
- Master Fakultas tetap aktif karena belum ditetapkan sebagai master OBE.

## Menu

Menu terpisah Program Studi dan Tahun Akademik digabung menjadi `Master Data OBE`. Hal ini mengurangi duplikasi menu sekaligus memberi satu lokasi pemeriksaan semua master akademik.

## Pengujian

```powershell
php tests/obe_read_layer_test.php
php tests/obe_mapping_and_master_test.php
```

Pengujian memastikan:

- 11 kategori tersedia;
- data referensi lengkap terbaca;
- mapping dan konflik tersimpan di DEIS;
- konflik kode tidak dipaksakan;
- fingerprint tabel OBE tidak berubah;
- operasi tulis melalui gateway tetap ditolak.

## Pekerjaan lanjutan

- UI resolusi mapping manual untuk data `UNMAPPED`/`CONFLICT`.
- Memisahkan identitas dosen/mahasiswa dari atribut KPI/risiko DEIS secara eksplisit.
- Mengganti konsumen dashboard satu per satu agar memakai `obe_id`.
- Menonaktifkan mekanisme penyalinan `ObeSyncService` setelah semua konsumen selesai dialihkan.
