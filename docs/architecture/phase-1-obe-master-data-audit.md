# Fase 1 — Audit Kepemilikan Master Data OBE–DEIS

Status: **selesai untuk baseline audit**  
Tanggal audit: **19 Oktober 2026**  
Ruang lingkup perubahan: **hanya proyek DEIS**

## 1. Keputusan arsitektur

1. Database `obe_cpl` dan proyek `obe` adalah sumber kebenaran untuk master akademik.
2. DEIS tidak boleh menulis ke `obe_cpl`; integrasi yang akan dibuat hanya menggunakan `SELECT`.
3. DEIS tetap menjadi pemilik data eksekutif/nonakademik: akreditasi, SPMI/AMI, indikator strategis, rapat dan RTL, kerja sama, keuangan, persetujuan, notifikasi, serta audit DEIS.
4. Salinan master akademik di DEIS dipertahankan sementara untuk kompatibilitas, tetapi akan dihentikan sebagai sumber kebenaran secara bertahap.
5. Relasi antarsistem harus memakai ID OBE/pemetaan eksplisit. Nama, kode, NIDN, dan NIM hanya boleh digunakan untuk rekonsiliasi awal, bukan identitas permanen.
6. Data historis yang harus tetap sama setelah master OBE berubah akan disimpan sebagai snapshot di DEIS pada fase berikutnya.

## 2. Sebelas master data OBE

| No. | Menu master OBE | Tabel utama OBE | Padanan saat ini di DEIS | Keputusan fase migrasi |
|---:|---|---|---|---|
| 1 | Tahun Akademik | `tahun_akademik` | `academic_years` | OBE menjadi sumber baca; CRUD DEIS dihentikan |
| 2 | Dosen | `users` (`role_id=3`) | `lecturers` | Identitas akademik dari OBE; metrik eksekutif khusus tetap di DEIS |
| 3 | Mahasiswa | `users` (`role_id=5`) dan relasi akademiknya | `students` | Identitas dari OBE; atribut risiko/monitoring tetap di DEIS |
| 4 | Program Studi | `program_studi` | `study_programs` | OBE menjadi sumber identitas prodi; atribut akreditasi/target tetap di DEIS |
| 5 | Kurikulum | `kurikulum` | Belum ada master setara; hanya `courses.kurikulum_year` | Dibaca langsung dari OBE |
| 6 | Aturan Kurikulum | `aturan_kurikulum` | Tidak ada | Dibaca langsung dari OBE bila diperlukan |
| 7 | Profil Lulusan | `profil_lulusan`, `pl_cpl` | Tidak ada | Dibaca langsung untuk monitoring mutu |
| 8 | CPL | `cpl`, `pl_cpl`, `mk_cpl`, `cpmk_cpl` | Hanya status ringkas pada `courses.cpl_status` | Detail dari OBE; DEIS menyimpan catatan audit saja |
| 9 | Mata Kuliah | `mata_kuliah` | `courses` | OBE menjadi sumber identitas, SKS, semester, kurikulum, dan status aktif |
| 10 | Rubrik Penilaian | `rubrik`, `rubrik_level`, `rubrik_kriteria`, `rubrik_deskriptor` | Tidak ada master setara | Dibaca langsung untuk pemeriksaan kesiapan asesmen |
| 11 | Import Data | Fitur/proses import OBE | Tidak ada padanan langsung | Tetap hanya di OBE; tidak diduplikasi di DEIS |

Catatan: mahasiswa OBE tidak memiliki tabel `mahasiswa` tersendiri pada skema saat audit. Akun mahasiswa berada di `users` dengan `role_id=5`; data akademiknya tersebar pada tabel kelas, asesmen, KHS/transkrip, dan RPL.

## 3. Baseline data saat audit

| Entitas | OBE | DEIS | Temuan |
|---|---:|---:|---|
| Tahun akademik | 8 | 3 | ID dan jumlah berbeda |
| Dosen bernomor NIDN | 6 | 12 | Master DEIS berisi data lama/dummy yang tidak identik dengan OBE |
| Mahasiswa | 31 akun mahasiswa | 12 | Struktur identitas berbeda dan perlu adapter |
| Program studi | 4 | 4 | Jumlah sama, tetapi ID tidak boleh diasumsikan sama |
| Kurikulum | 4 | Tidak ada tabel master | DEIS hanya menyimpan tahun kurikulum per mata kuliah |
| Aturan kurikulum | 2 | Tidak ada | Konsumen baru diperlukan jika ditampilkan |
| Profil lulusan | 10 | Tidak ada | Belum dikonsumsi langsung oleh DEIS |
| CPL | 21 | Tidak ada master | DEIS saat ini hanya menyimpan label status |
| Mata kuliah | 147 | 107 | Daftar DEIS tidak identik dan masih mengandung data lokal lama |
| Rubrik | 11 | Tidak ada | Belum dikonsumsi langsung oleh DEIS |

Konsekuensi: sinkronisasi berdasarkan nama atau kode saja tidak cukup aman. Kasus PJMK lama yang tampil pada laporan adalah bukti bahwa fallback ke data lokal dapat menyamarkan ketiadaan data OBE.

## 4. Inventaris route DEIS yang terdampak

### Route master yang akan menjadi read-only atau dihentikan

| Route | Operasi sekarang | Keputusan target |
|---|---|---|
| `/master/study-programs` | Lihat dan CRUD prodi lokal | Tampilan referensi OBE; POST dihentikan |
| `/master/academic-years` | Lihat/ubah periode lokal | Tampilan referensi OBE; POST dihentikan |
| `/lecturers` | Lihat dan CRUD dosen lokal | Monitoring read-only berbasis identitas OBE |
| `/students` | Monitoring mahasiswa lokal | Monitoring berbasis identitas OBE; aksi risiko tetap DEIS |
| `/academic/courses` | Daftar mata kuliah lokal | Daftar read-only dari OBE |
| `/academic/sync-obe` | Menyalin OBE ke `courses` | Diganti adapter baca/status integrasi; bukan sinkronisasi master |
| `/academic/obe-live` | Tampilan query OBE | Menjadi dasar halaman Referensi OBE |

### Route yang tetap dimiliki DEIS tetapi bergantung pada master OBE

- `/dashboard`, `/dashboard/analytics`, `/dashboard/chart-data`
- `/command-center` dan persetujuan
- `/academic` dan detail perkuliahan
- `/academic/rps-monitoring` serta ekspor RPS
- `/academic/practicum`
- `/academic/guidance`
- `/lecturers/kpi`
- `/students/early-warning` dan `/students/alumni`
- `/accreditation`
- `/quality` dan `/quality/ami`
- `/strategic` dan indikator
- `/finances`
- `/reports`

Route tersebut tidak dihapus. Query referensi program studi, dosen, mahasiswa, mata kuliah, dan periode akademik di dalamnya akan dialihkan ke lapisan baca OBE.

## 5. Konsumen model master lokal

| Model lokal | Konsumen utama | Risiko bila langsung dihapus |
|---|---|---|
| `AcademicYear` | `AcademicController`, kelas, ekspor perkuliahan | Filter periode dan FK `classes.academic_year_id` putus |
| `StudyProgram` | Dashboard, akademik, akreditasi, keuangan, dosen, mahasiswa, laporan, master | Hampir seluruh agregasi prodi dan banyak FK putus |
| `Lecturer` | Dashboard, command center, dosen/KPI, laporan, kelas, bimbingan | KPI/BKD dan relasi operasional DEIS hilang |
| `Student` | Dashboard, command center, mahasiswa, laporan, bimbingan | Early warning dan data pendamping DEIS hilang |
| `Course` | Monitoring RPS, daftar MK, ekspor, kelas | RPS dan kelas lokal kehilangan referensi |

Kesimpulan: tabel lokal belum boleh dihapus pada fase awal. Tabel tersebut perlu diubah menjadi tabel ekstensi/pemetaan atau digantikan secara bertahap setelah seluruh FK dikaji.

## 6. Klasifikasi tabel DEIS

### A. Master akademik duplikat — kandidat deprecate

- `academic_years`
- `study_programs`
- `lecturers` untuk kolom identitas master
- `students` untuk kolom identitas master
- `courses` untuk kolom identitas akademik

### B. Data DEIS yang tetap dipertahankan

- `faculties` untuk konfigurasi organisasi DEIS, sampai ada keputusan sumber fakultas
- `accreditations`
- `spmi_standards`, `ami_audits`, `ami_findings`
- `indicators`, target, realisasi, dan bukti
- `renstra_programs`
- `approvals`, `critical_alerts`
- `meetings`, peserta, dokumen, dan `action_items`
- `cooperations`
- `finances`
- `practicum_modules` jika tetap menjadi proses eksekutif DEIS
- `guidances` jika tidak dipindahkan ke sistem akademik
- `surveys`
- `users`, `roles`, dan `permissions` untuk akses aplikasi DEIS
- `notifications`, `audit_logs`, `app_settings`, dan `files`

### C. Data operasional yang memerlukan keputusan pada fase desain

- `classes` dan `class_meetings`
- atribut KPI/BKD pada `lecturers`
- atribut risiko/IPK/kehadiran pada `students`
- status audit RPS pada `courses`

Untuk kategori C, rekomendasinya adalah memisahkan identitas OBE dari atribut monitoring DEIS, bukan menghapus seluruh tabel.

## 7. Matriks kepemilikan data

| Jenis data | Create/Update/Delete | Read utama | Snapshot historis |
|---|---|---|---|
| 11 master akademik | OBE | OBE | DEIS bila dibutuhkan laporan |
| PJMK dan tim teaching | OBE | OBE | DEIS untuk audit semester |
| RPS, CPL, CPMK, rubrik | OBE | OBE | DEIS untuk laporan yang sudah disahkan |
| KPI/BKD dan risiko eksekutif | DEIS | DEIS | DEIS |
| Akreditasi, AMI, SPMI | DEIS | DEIS + referensi prodi OBE | DEIS |
| Keuangan, kerja sama, rapat/RTL | DEIS | DEIS | DEIS |
| Pengguna dan hak akses DEIS | DEIS | DEIS | Audit DEIS |

## 8. Guardrail implementasi

- Tidak mengubah file pada proyek `obe`.
- Tidak menjalankan DDL atau DML pada database `obe_cpl`.
- Akun koneksi OBE target produksi harus hanya memiliki izin `SELECT`.
- Jangan menggunakan `COALESCE(data_obe, data_lama_deis)` untuk laporan resmi karena dapat menampilkan data lama seolah-olah berasal dari OBE.
- Ketika data OBE kosong, tampilkan “Belum ditetapkan di OBE”.
- Ketika OBE tidak tersedia, tampilkan status gangguan yang jelas; jangan diam-diam memakai master lokal.
- Tidak menghapus tabel atau route lama sebelum konsumen dan FK-nya selesai dimigrasikan.

## 9. Deliverable fase berikutnya

Fase 2 dimulai dengan lapisan baca OBE di dalam DEIS:

1. konfigurasi koneksi read-only terpisah;
2. `ObeConnection`/gateway tunggal;
3. repository untuk tahun akademik, prodi, dosen, mahasiswa, kurikulum, dan mata kuliah;
4. health check integrasi;
5. DTO/normalisasi data agar controller tidak bergantung pada nama kolom database OBE;
6. pengujian yang membuktikan tidak ada operasi tulis ke OBE.

Pilot pertama yang disarankan: **Program Studi + Tahun Akademik**, karena cakupannya kecil tetapi dipakai luas dan akan memvalidasi pola ID mapping sebelum Dosen/PJMK dan Mata Kuliah dimigrasikan.
