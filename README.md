# 🏛️ DEIS (Dean Executive Information System)

> **Sistem Informasi Eksekutif Dekan** berbasis arsitektur **Pure PHP Native MVC** dengan desain antarmuka modern terintegrasi tema **Dasher / Tabler**, Bootstrap 5, ApexCharts, dan Tabler Icons.

---

## 🌟 Fitur Utama & Modul Sistem

1. **👑 Pusat Kendali Eksekutif (Executive Dashboard & Command Center)**:
   - **My Attention Panel**: Ringkasan tugas kritis, persetujuan tertunda, dan audit temuan.
   - **Critical & Warning Alerts Radar**: Deteksi dini anomali operasional fakultas.
   - **Deadline Radar**: Peringatan jatuh tempo akreditasi, laporan BKD, dan evaluasi anggaran.
   - **Persetujuan Dekan (Approvals)**: Alur pengajuan proposal dari Kaprodi/Dosen dengan hak persetujuan eksklusif Dekan.

2. **👨‍🏫 Master SDM & Kinerja Dosen**:
   - CRUD Data Dosen & biodata lengkap.
   - Kepatuhan Beban Kerja Dosen (BKD SKS & Presensi).
   - Capaian Publikasi & Skor SINTA.
   - Algoritma Pemeringkatan KPI Dosen Fakultas.

3. **📚 Perkuliahan, Mahasiswa & Early Warning System (EWS)**:
   - Monitoring kelas perkuliahan, kurikulum, dan kesiapan RPS.
   - Deteksi dini mahasiswa berisiko DO / kritis berdasarkan IPK, SKS, dan presensi.
   - Pelacakan Bimbingan TA/Skripsi, Magang Industri, dan MBKM.

4. **📅 Rapat & Tata Kelola Digital (Digital Meeting Packet)**:
   - Manajemen paket digital rapat: Undangan, Absensi, Notulensi, Materi, dan Dokumentasi.
   - Tracking Rencana Tindak Lanjut (RTL) dengan status penugasan PIC dan tenggat waktu.

5. **🛡️ Penjaminan Mutu Internal (SPMI) & Akreditasi**:
   - Pengawalan siklus PPEPP (Penetapan, Pelaksanaan, Evaluasi, Pengendalian, Peningkatan).
   - Audit Mutu Internal (AMI) dan Rekapitulasi Survei Kepuasan.
   - Radar Masa Berlaku Akreditasi BAN-PT/LAM & Countdown Hari Kritis.

6. **🎯 Indikator Kinerja Utama (IKU) & Renstra**:
   - Formulasi dinamis target vs realisasi 8 IKU Dikti & Program Kerja Strategis Dekanat.

7. **🤝 Kemitraan / Kerja Sama & 💰 Realisasi Keuangan**:
   - Pelacakan dokumen MoU, MoA, IA serta realisasi aktivitas nyata mitra.
   - Monitoring pagu anggaran RKA vs realisasi belanja dan persentase serapan.

8. **📊 Laporan Eksekutif & Ekspor**:
   - Pratinjau cetak resmi PDF dengan kop surat dinamis dan tanda tangan pimpinan.
   - Ekspor data capaian kinerja ke format CSV/Excel.

---

## 👥 Matriks Hak Akses Peran (RBAC) & Kredensial Default

Semua akun menggunakan kata sandi default: **`password`**

| Peran (Role) | Username | Password | Konteks Kerja & Cakupan Menu |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin` | `password` | Akses penuh konfigurasi sistem, audit log, RBAC, dan master data. |
| **Dekan** | `dekan` | `password` | Hak otorisasi persetujuan (*Approvals*), eksekutif command center, evaluasi, dan cetak laporan. |
| **Kaprodi TI** | `kaprodi.ti` | `password` | Kurikulum prodi, kelas, bimbingan TA/MBKM, EWS prodi, dan pengajuan ke Dekan. |
| **GKM / SPMI** | `spmi` | `password` | Standar mutu SPMI, siklus PPEPP, audit AMI, survei kepuasan, dan radar akreditasi. |
| **Dosen** | `dosen` | `password` | Profil Tri Dharma, kinerja BKD, SINTA, kelas mengajar, dan pengajuan usulan. |
| **Operator / Tendik** | `operator` | `password` | Administrasi akademik, database mahasiswa, kurikulum, dan pengarsipan rapat. |

---

## 💻 Kebutuhan Sistem & Teknologi

- **Bahasa**: PHP >= 8.0 (Pure Native MVC, tanpa framework vendor berat)
- **Database**: MySQL 5.7+ / MariaDB 10.4+
- **Web Server**: Apache (XAMPP / WampServer / LAMP) dengan `mod_rewrite` aktif
- **Frontend Assets**: Bootstrap 5.3.3, Tabler Icons, ApexCharts, Dasher UI Theme

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Letakkan di Direktori Web Server
Letakkan repositori ini di dalam direktori root web server Anda, misalnya:
```text
C:\xampp\htdocs\deis\
```

### 2. Impor Database
1. Buka **phpMyAdmin** (`http://localhost/phpmyadmin/`) atau MySQL CLI.
2. Buat database baru bernama: `deis_db`
3. Impor berkas SQL terpadu yang ada di:
   ```text
   database/deis_db_full.sql
   ```

*(Alternatif: Anda juga dapat menjalankan migrasi melalui terminal: `php database/migrate.php`)*

### 3. Konfigurasi Koneksi (Jika Diperlukan)
Konfigurasi database berada di berkas `app/config/database.php`:
```php
return [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'database' => 'deis_db',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
];
```

### 4. Akses Melalui Browser
Buka peramban web dan arahkan ke alamat:
```text
http://localhost/deis/
```

---

## 🧪 Pengujian Otomatis (Automated Verification)

Jalankan rangkaian tes bawaan menggunakan terminal untuk memverifikasi integritas sistem:

```bash
# 1. Menjalankan 18 Automated Core Tests
php test_suite.php

# 2. Menjalankan Uji Otorisasi & Alur Kerja RBAC
php test_roles.php

# 3. Menjalankan Uji CRUD & Operasi Database
php test_crud.php
```

---

## 📁 Struktur Direktori Proyek

```text
deis/
├── app/
│   ├── config/          # Konfigurasi aplikasi, basis data, dan rute
│   ├── controllers/     # Controller MVC untuk setiap modul bisnis
│   ├── core/            # Core Engine: Router, Controller, Model, Database, View
│   ├── helpers/         # FormatHelper, CsrfHelper, AuthHelper, dll.
│   ├── middleware/      # AuthMiddleware, RoleMiddleware, CsrfMiddleware
│   ├── models/          # Model data & operasi query PDO
│   └── views/           # Tampilan template & layout berbasis peran
├── database/
│   ├── deis_db_full.sql # Dump basis data lengkap (skema + data awal)
│   ├── schema.sql       # Skema DDL tabel
│   ├── seeds.sql        # Data awal (seeder)
│   └── migrate.php      # Skrip migrasi otomatis via CLI
├── public/
│   ├── assets/          # CSS Dasher kustom, JS interaktif, Tabler Icons, ApexCharts
│   ├── index.php        # Front Controller utama
│   └── .htaccess        # Konfigurasi rewrite Apache
├── storage/             # Penyimpanan arsip rapat, laporan, dan dokumen
├── test_suite.php       # Skrip pengujian terintegrasi (18 test cases)
├── test_roles.php       # Skrip pengujian RBAC
├── test_crud.php        # Skrip pengujian operasi CRUD
└── README.md
```

---

## 📄 Lisensi
Sistem Informasi Eksekutif Dekan (DEIS) dikembangkan untuk tata kelola perguruan tinggi modern.
Semua hak cipta dilindungi undang-undang.
