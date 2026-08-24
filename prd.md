# PRODUCT REQUIREMENTS DOCUMENT (PRD)

## Dean Executive Information System (DEIS)

### Sistem Informasi Eksekutif Dekan Fakultas

**Versi:** 1.0
**Status:** Draft Final untuk Tahap Analisis & Desain
**Platform:** Web Application
**Arsitektur:** PHP Native Full MVC
**Database:** MySQL
**Target Deployment:** Shared Hosting / VPS
**Responsive:** Desktop, Tablet, Mobile

---

# 1. Ringkasan Produk

Dean Executive Information System (DEIS) adalah aplikasi berbasis web yang dirancang sebagai pusat kendali dan monitoring Dekan dalam mengelola operasional, kinerja, mutu, akademik, sumber daya manusia, mahasiswa, akreditasi, rapat, serta kinerja strategis fakultas.

DEIS bukan pengganti SIAKAD atau sistem akademik utama universitas. Sistem ini berfungsi sebagai **Executive Information System (EIS)** yang mengonsolidasikan informasi dari berbagai sumber untuk membantu Dekan melakukan monitoring, evaluasi, pengambilan keputusan, dan tindak lanjut.

Prinsip utama:

> **Collect → Monitor → Analyze → Alert → Action → Evaluate**

---

# 2. Latar Belakang

Dekan membutuhkan informasi lintas bidang untuk menjalankan fungsi kepemimpinan fakultas. Informasi tersebut biasanya tersebar pada berbagai dokumen, spreadsheet, aplikasi akademik, sistem PDDIKTI, SISTER, SINTA, dokumen SPMI, dokumen akreditasi, dan arsip manual.

Kondisi tersebut menyebabkan:

1. Data sulit dipantau secara terpusat.
2. Dekan membutuhkan waktu untuk mencari informasi.
3. Permasalahan sering diketahui setelah melewati deadline.
4. Tindak lanjut hasil rapat sulit dipantau.
5. Dokumen rapat dan eviden tersebar.
6. Data kinerja dosen tidak tersaji secara executive-friendly.
7. Data mutu dan akreditasi tidak terintegrasi dengan monitoring fakultas.
8. Pengambilan keputusan belum sepenuhnya berbasis data.

DEIS dirancang untuk mengatasi permasalahan tersebut.

---

# 3. Tujuan Produk

## 3.1 Tujuan Utama

Membangun sistem informasi eksekutif yang membantu Dekan memantau kondisi fakultas dan mengambil tindakan berdasarkan data secara cepat, terstruktur, dan terdokumentasi.

## 3.2 Tujuan Khusus

* Menyediakan dashboard eksekutif.
* Menyediakan Command Center.
* Memusatkan data fakultas.
* Memantau kinerja dosen.
* Memantau akademik.
* Memantau mahasiswa dan alumni.
* Memantau SPMI.
* Memantau akreditasi.
* Memantau kinerja strategis.
* Mengarsipkan rapat dan dokumen.
* Memantau tindak lanjut rapat.
* Menyediakan laporan eksekutif.
* Menyediakan role dan permission.
* Menyiapkan fondasi integrasi eksternal.
* Menyiapkan fondasi AI Assistant.

---

# 4. Target User

## 4.1 Primary User

### Dekan

Dekan adalah pengguna utama aplikasi.

Kebutuhan utama:

* Melihat kondisi fakultas.
* Melihat masalah.
* Melihat deadline.
* Memantau kinerja.
* Melakukan approval.
* Memantau tindak lanjut.
* Mengakses laporan.
* Mengakses arsip.

---

## 4.2 Administrative User

### Operator

Bertugas melakukan input dan pengelolaan data sesuai kewenangan.

---

## 4.3 Program Studi

### Kaprodi

Memiliki akses terhadap data dan monitoring yang berkaitan dengan program studinya.

---

## 4.4 Academic Staff

### Dosen

Mengelola data yang berkaitan dengan dirinya sendiri.

---

## 4.5 Quality User

### GKM/SPMI

Mengelola data mutu sesuai kewenangan.

---

## 4.6 System Administrator

### Super Admin

Mengelola user, role, permission, konfigurasi, dan sistem.

---

## 4.7 Developer

Developer memiliki akses teknis tingkat tinggi untuk:

* System configuration.
* Database configuration.
* Integration configuration.
* Audit log.
* Maintenance.
* System monitoring.

Developer tidak boleh mengubah data bisnis secara sembarangan tanpa audit trail.

---

# 5. Role Architecture

Sistem menggunakan RBAC (Role-Based Access Control).

Role utama:

| Role        | Fungsi                          |
| ----------- | ------------------------------- |
| Dekan       | Executive management            |
| Kaprodi     | Program study management        |
| Dosen       | Personal academic & performance |
| Operator    | Data administration             |
| GKM/SPMI    | Quality management              |
| Super Admin | System administration           |
| Developer   | Technical administration        |

Dekan dapat memiliki dua konteks akses:

### Business Role

**Dekan**

### Technical Role

**Developer / Super Admin**

Keduanya harus tetap dibedakan dalam permission agar aktivitas teknis dapat diaudit.

---

# 6. Prinsip Arsitektur Sistem

## 6.1 Full MVC

Aplikasi wajib menggunakan pola:

```text
Model
View
Controller
```

Struktur dasar:

```text
/app
    /config
    /controllers
    /models
    /views
    /core
    /helpers
    /middlewares
    /services
    /repositories

/public
    /assets
    /uploads

/routes
/storage
```

---

# 7. Sitemap Produk

## 1. Dashboard

### Dashboard Eksekutif

* Ringkasan Fakultas
* KPI Fakultas
* Alert
* Kalender
* Agenda Dekan

### Dashboard Analitik

* Tren Mahasiswa
* Tren Dosen
* Tren Penelitian
* Tren Keuangan
* Tren Kinerja Strategis

---

# 8. Command Center

**Modul wajib dan prioritas utama.**

## 8.1 Critical Alert

* Dosen bermasalah
* Mahasiswa berisiko
* Deadline terlewat
* Dokumen belum lengkap
* Akreditasi bermasalah
* RTL terlambat

## 8.2 Deadline

* Akademik
* BKD
* SPMI
* Akreditasi
* Penelitian
* Kerja sama
* RTL rapat

## 8.3 Pending Approval

* Kegiatan
* Penelitian
* PkM
* Kerja sama
* Anggaran
* Surat

## 8.4 Monitoring Rapat

* Rapat mendatang
* Rapat belum lengkap dokumen
* RTL aktif
* RTL terlambat

## 8.5 Monitoring Kinerja

* Dosen
* Prodi
* Mahasiswa
* SPMI
* Akreditasi

## 8.6 My Attention

Daftar personal item yang membutuhkan perhatian Dekan.

Contoh:

```text
Perhatian Anda: 12

3 Approval
2 RTL terlambat
3 Dosen perlu perhatian
2 Deadline
2 Dokumen
```

---

# 9. Data Master

## Organisasi

* Fakultas
* Program Studi
* Unit
* Laboratorium

## SDM

* Dosen
* Tendik
* Praktisi

## Akademik

* Tahun Akademik
* Semester
* Kurikulum
* Mata Kuliah

## Mahasiswa

* Mahasiswa
* Alumni

---

# 10. Akademik

## Perkuliahan

* Kehadiran Dosen
* Kehadiran Mahasiswa
* Realisasi Perkuliahan
* RPS

## Bimbingan

* DPA
* Skripsi
* Magang
* MBKM

## Evaluasi

* Nilai
* Rekap Nilai
* Mata Kuliah Bermasalah

---

# 11. SDM & Kinerja Dosen

## Profil

* Biodata
* Jabatan Akademik
* Sertifikasi

## Pendidikan

* Beban Mengajar
* BKD
* Bimbingan

## Penelitian

* Penelitian
* Publikasi
* HKI
* Buku

## Pengabdian

* PkM
* Luaran
* Hibah

## KPI

* Dashboard
* Ranking
* Capaian Target

---

# 12. Mahasiswa & Alumni

## Mahasiswa

* Data Mahasiswa
* Prestasi
* Beasiswa
* Organisasi
* Early Warning

## Alumni

* Tracer Study
* Sebaran Alumni
* Masa Tunggu

## Pengguna Lulusan

* Survey Kepuasan
* Feedback Industri

---

# 13. Mutu / SPMI

## Standar

* Pendidikan
* Penelitian
* Pengabdian
* Standar Tambahan

## PPEPP

* Penetapan
* Pelaksanaan
* Evaluasi
* Pengendalian
* Peningkatan

## AMI

* Jadwal
* Auditor
* Temuan
* RTL

## Survei

* Mahasiswa
* Dosen
* Tendik
* Alumni
* Pengguna Lulusan

---

# 14. Akreditasi

## Akreditasi Prodi

* Status
* Timeline
* Self Assessment

## Dokumen

* LED
* LKPS
* Eviden

## Monitoring

* Gap Analysis
* Temuan
* Action Plan

---

# 15. Kinerja Strategis

Modul ini dibuat dinamis agar perubahan indikator regulasi tidak mengharuskan perubahan struktur aplikasi.

## Dashboard

* Target vs Realisasi
* Capaian
* Trend

## Indikator Strategis

* Kode indikator
* Nama indikator
* Kategori
* Formula
* Satuan
* Sumber data
* Tahun berlaku
* Status aktif

## Target

* Target tahunan
* Target periode
* Target prodi

## Realisasi

* Bulanan
* Triwulan
* Semester
* Tahunan

## Eviden

* Dokumen
* Link
* Verifikasi

## Renstra

* Sasaran strategis
* Program kerja
* Indikator
* Target
* Realisasi

---

# 16. Kerja Sama

## Dokumen

* MoU
* MoA
* IA

## Implementasi

* Kegiatan
* Mitra
* Masa Berlaku
* Status

---

# 17. Keuangan

## Anggaran

* RKA
* Realisasi

## Pendapatan

* Mahasiswa
* Hibah
* Kerja Sama

## Analitik

* Serapan
* Efisiensi
* Trend

---

# 18. Data Warehouse

Data Warehouse menjadi fondasi integrasi data.

## Sumber Data

* PDDIKTI
* SISTER
* SINTA
* Scopus
* BKD
* PMB
* Akademik
* Keuangan

## Sinkronisasi

* Import
* Export
* Mapping
* Validasi

## Data Quality

* Data kosong
* Data duplikat
* Data tidak valid
* Data belum sinkron

---

# 19. Laporan Eksekutif

## Fakultas

* Bulanan
* Semester
* Tahunan

## Prodi

* Perbandingan Prodi

## Dosen

* Kinerja Dosen

## Mahasiswa

* Statistik Mahasiswa

## Ekspor

* PDF
* Excel
* CSV

---

# 20. AI Assistant

Modul disiapkan sebagai fase pengembangan lanjutan.

## Executive Assistant

* Tanya Data
* Ringkasan Fakultas
* Ringkasan Rapat

## Smart Insight

* Analisis KPI
* Analisis Risiko
* Analisis Tren
* Early Warning

AI tidak boleh mengubah data secara langsung tanpa approval dan audit trail.

---

# 21. Rapat & Tata Kelola

## Jenis Rapat

### Rapat Kaprodi

Dekan dan Kaprodi.

### Rapat Dosen Prodi

Dekan, Kaprodi, dan dosen.

### Rapat Gabungan

Dekan, beberapa Kaprodi, dan dosen.

---

## Data Rapat

* Nomor
* Judul
* Jenis
* Tanggal
* Waktu
* Tempat
* Pimpinan
* Prodi
* Peserta
* Agenda
* Status

---

## Arsip Rapat

Setiap rapat menjadi satu paket digital:

```text
Rapat
├── Undangan
├── Daftar Hadir
├── Materi
├── Notulensi
├── Foto
└── RTL
```

---

## Tindak Lanjut

Field:

* Rapat
* Uraian RTL
* PIC
* Deadline
* Prioritas
* Status
* Bukti penyelesaian

Status:

* Belum Mulai
* Proses
* Selesai
* Terlambat
* Dibatalkan

---

# 22. Sistem

## User Management

* User
* Role
* Permission

## Workflow

* Approval
* Notification

## Integrasi

* API
* WhatsApp Gateway
* Email

## Configuration

* General Setting
* Academic Setting
* File Setting
* Notification Setting

## Audit Log

* Login
* Create
* Update
* Delete
* Approval
* Export
* Download

---

# 23. Dashboard UI Requirements

Dashboard wajib menggunakan desain modern dan responsive.

## Desktop

Layout:

```text
┌──────────────────────────────────────────────┐
│ Topbar                                       │
├───────────┬──────────────────────────────────┤
│           │                                  │
│ Sidebar   │       Dashboard Content          │
│           │                                  │
│           │                                  │
└───────────┴──────────────────────────────────┘
```

## Mobile

Sidebar berubah menjadi:

* Offcanvas
* Bottom navigation untuk menu prioritas
* Responsive cards
* Responsive tables

---

# 24. Komponen UI

Wajib tersedia:

* KPI Card
* Chart
* Data Table
* Filter
* Search
* Modal
* Drawer
* Tabs
* Badge
* Alert
* Timeline
* Progress Bar
* File Upload
* Date Picker
* Dropdown
* Breadcrumb
* Notification Center

---

# 25. Sistem Warna Status

Status sistem menggunakan semantic status.

### Critical

Merah

### Warning

Oranye

### Attention

Kuning

### Success

Hijau

### Information

Biru

Warna tidak boleh menjadi satu-satunya penanda status; gunakan juga icon/label untuk aksesibilitas.

---

# 26. Notification System

Notifikasi internal:

* Alert
* Reminder
* Approval
* Deadline
* RTL

Channel:

1. In-app notification
2. Email
3. WhatsApp

WhatsApp dibuat sebagai integration layer sehingga provider dapat diganti.

---

# 27. File Management

Dokumen harus disimpan berdasarkan modul.

Contoh:

```text
/storage
    /meetings
    /spmi
    /accreditation
    /strategic
    /research
    /community-service
    /cooperation
    /reports
```

Setiap file memiliki metadata:

* Nama
* Tipe
* Ukuran
* Pemilik
* Modul
* Record ID
* Tanggal upload
* Versi
* Status

---

# 28. Security Requirements

## Authentication

* Login
* Logout
* Session management
* Password hashing
* Password reset

## Authorization

* RBAC
* Permission per menu
* Permission per action

Action permission minimal:

```text
view
create
update
delete
approve
export
download
verify
```

## Security

Wajib menerapkan:

* PDO Prepared Statement
* CSRF Protection
* XSS Protection
* Session Regeneration
* Password Hashing
* Input Validation
* Upload Validation
* MIME Validation
* Access Control
* Audit Trail

---

# 29. Audit Trail

Semua aktivitas kritis harus tercatat.

Contoh:

```text
User:
Dekan

Action:
UPDATE

Module:
Kinerja Dosen

Record:
Dosen #123

Before:
Target = 80

After:
Target = 85

Timestamp:
2026-08-24 20:30
```

Audit log tidak boleh dapat dihapus melalui interface biasa.

---

# 30. Search Global

Sistem memiliki pencarian global.

Dekan dapat mencari:

```text
"Rony"

→ Dosen
→ Rapat
→ Dokumen
→ KPI
→ RTL
→ Laporan
```

Search harus menghormati permission user.

---

# 31. Executive KPI

KPI dashboard minimal:

### Akademik

* Kehadiran dosen
* Kehadiran mahasiswa
* Kelulusan
* IPK

### SDM

* Rasio dosen mahasiswa
* BKD
* Jabatan akademik
* Sertifikasi

### Penelitian

* Publikasi
* SINTA
* Scopus
* HKI

### PkM

* Jumlah PkM
* Luaran

### Mahasiswa

* Prestasi
* Retensi
* Risiko akademik

### Mutu

* Capaian standar
* Temuan AMI
* RTL

### Akreditasi

* Progress eviden
* Gap
* Action Plan

### Strategis

* Target
* Realisasi
* Persentase capaian

---

# 32. Command Center Logic

Command Center menggunakan rule engine sederhana.

Contoh:

```text
IF deadline < today
THEN status = CRITICAL

IF achievement < 70%
THEN status = WARNING

IF RTL.deadline < today
AND RTL.status != selesai
THEN status = OVERDUE
```

Rule tidak ditanam secara hard-code sebanyak mungkin.

Gunakan konfigurasi agar dapat dikembangkan.

---

# 33. Workflow

Contoh workflow RTL:

```text
Created
   ↓
Assigned
   ↓
In Progress
   ↓
Submitted
   ↓
Verified
   ↓
Completed
```

Contoh approval:

```text
Draft
 ↓
Submitted
 ↓
Reviewed
 ↓
Approved / Rejected
```

---

# 34. Integrasi

Tahap awal sistem harus menyediakan interface untuk integrasi.

Target integrasi:

* PDDIKTI
* SISTER
* SINTA
* Scopus
* Sistem akademik
* Sistem keuangan
* WhatsApp Gateway
* Email

Tidak semua integrasi harus tersedia pada MVP.

---

# 35. MVP Scope

MVP tidak boleh langsung mengembangkan seluruh sitemap.

## Prioritas MVP

### Phase 1 — Foundation

* Authentication
* User
* Role
* Permission
* Data Master
* File Management
* Audit Log
* Notification

### Phase 2 — Executive

* Dashboard
* Command Center
* KPI
* Deadline
* Alert
* My Attention

### Phase 3 — Core Monitoring

* SDM
* Kinerja Dosen
* Akademik
* Mahasiswa

### Phase 4 — Governance

* Rapat
* RTL
* SPMI
* Akreditasi

### Phase 5 — Strategic

* Kinerja Strategis
* Renstra
* Laporan

### Phase 6 — Integration

* Data Warehouse
* External API
* WhatsApp

### Phase 7 — Intelligence

* AI Assistant
* Smart Insight
* Predictive Analytics

---

# 36. Non-Functional Requirements

## Performance

Target:

* Page load normal < 3 detik.
* Query dashboard dioptimalkan.
* Pagination untuk data besar.
* Lazy loading untuk data berat.

## Responsive

Support:

* Desktop
* Laptop
* Tablet
* Smartphone

## Availability

Sistem dirancang untuk berjalan pada:

* Shared Hosting
* VPS

tanpa ketergantungan wajib terhadap Node.js.

---

# 37. Database Principle

Database harus mengikuti prinsip:

### Master

Data referensi.

### Transaction

Data aktivitas.

### Document

Data file.

### Measurement

Data indikator dan capaian.

### Audit

Riwayat aktivitas.

Contoh:

```text
users
roles
permissions

faculties
study_programs
lecturers
students

meetings
meeting_participants
meeting_documents
meeting_minutes
meeting_action_items

indicators
indicator_targets
indicator_realizations
indicator_evidences

audit_logs
notifications
files
```

---

# 38. Prinsip Penting Database

Jangan membuat tabel:

```text
iku_1
iku_2
iku_3
iku_4
```

Gunakan:

```text
indicators
indicator_targets
indicator_realizations
```

Dengan demikian indikator dapat berubah tanpa mengubah struktur database.

Hal yang sama berlaku untuk:

* KPI
* SPMI
* Akreditasi
* Renstra

---

# 39. Success Metrics

DEIS dianggap berhasil jika:

1. Dekan dapat mengetahui kondisi fakultas dari satu dashboard.
2. Dekan dapat menemukan masalah prioritas tanpa membuka banyak modul.
3. Deadline penting dapat dimonitor.
4. RTL rapat dapat dilacak sampai selesai.
5. Dokumen rapat dapat ditemukan dengan cepat.
6. Kinerja dosen dapat dibandingkan.
7. Data mutu dapat dimonitor.
8. Data strategis dapat dibandingkan target vs realisasi.
9. Seluruh aktivitas kritis memiliki audit trail.
10. Sistem dapat dikembangkan tanpa mengubah arsitektur utama.

---

# 40. Prinsip Pengembangan

Pengembangan wajib mengikuti prinsip:

> **Modular, Secure, Responsive, Maintainable, Extensible.**

Kode tidak boleh dibuat dengan pendekatan procedural yang bercampur antara:

```text
SQL
HTML
Business Logic
Authentication
```

dalam satu file.

Semua harus mengikuti MVC dan separation of concerns.

---

# 41. Definition of Done

Sebuah fitur dianggap selesai apabila:

* Requirement terpenuhi.
* UI responsive.
* Permission diterapkan.
* Validation diterapkan.
* Error handling tersedia.
* Audit log tersedia untuk aktivitas kritis.
* Database migration tersedia.
* CRUD berjalan.
* Search/filter berjalan bila diperlukan.
* Tidak ada SQL injection.
* Tidak ada akses unauthorized.
* File upload tervalidasi.
* Testing dasar dilakukan.

---

# 42. Roadmap Produk

```text
                    DEIS
                     │
        ┌────────────┴────────────┐
        │                         │
   FOUNDATION                EXECUTIVE
        │                         │
   RBAC / MVC              Dashboard / Command
        │                         │
        └────────────┬────────────┘
                     │
              CORE MONITORING
                     │
          ┌──────────┼──────────┐
          │          │          │
       Akademik    Dosen     Mahasiswa
          │          │          │
          └──────────┼──────────┘
                     │
                GOVERNANCE
                     │
             ┌───────┴───────┐
             │               │
            SPMI        Akreditasi
             │               │
             └───────┬───────┘
                     │
                 STRATEGIC
                     │
              KPI / Renstra
                     │
                 INTEGRATION
                     │
               DATA WAREHOUSE
                     │
               INTELLIGENCE
                     │
                 AI ASSISTANT
```

# 43. Product Vision

DEIS diarahkan menjadi:

> **"Satu layar untuk memahami kondisi fakultas, satu pusat untuk menemukan masalah, dan satu sistem untuk memastikan setiap keputusan Dekan ditindaklanjuti."**

Sistem bukan sekadar aplikasi administrasi fakultas.

Sistem harus menjadi **Executive Decision Support System** bagi Dekan.
