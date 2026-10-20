# Fase 5–6 — Navigasi Ringkas, Realtime OBE, dan Snapshot DEIS

Tanggal implementasi: 19 Oktober 2026

## Tujuan

Menyederhanakan navigasi DEIS, menjadikan OBE sebagai sumber master akademik yang dibaca secara realtime, serta menyediakan jejak historis tanpa menulis ke database OBE.

## Fase 5: penyederhanaan menu

- Menu kurikulum lama diarahkan ke `/master/obe?section=courses` untuk superadmin dan kaprodi.
- Shortcut yang bertumpuk (`Ranking KPI Dosen` dan `Tracking RTL Rapat`) dihapus dari sidebar; rute lama tetap tersedia dari halaman induknya.
- Menu tahun akademik dan program studi lokal dihapus dari sidebar superadmin karena datanya sekarang dirujuk dari master OBE.
- Dekan tetap memperoleh menu monitoring RPS, perkuliahan, praktikum, bimbingan, dan mahasiswa tanpa menu salinan kurikulum.
- `/academic/courses` dipertahankan sebagai rute kompatibilitas dan mengalihkan pengguna ke master OBE.

## Fase 6: snapshot historis

Tabel `obe_snapshots` berada hanya di database DEIS. Snapshot menyimpan:

1. tahun akademik;
2. program studi;
3. dosen;
4. mahasiswa;
5. kurikulum;
6. aturan kurikulum;
7. profil lulusan;
8. CPL;
9. mata kuliah;
10. rubrik; dan
11. ringkasan status RPS.

Setiap snapshot memiliki label, jumlah record, SHA-256 checksum, pengguna pembuat, dan waktu pembuatan. Tombol **Simpan Snapshot Historis** tersedia di `/academic/obe-live` dan mengirim POST dengan proteksi CSRF ke `/academic/obe-snapshot`.

Snapshot adalah arsip audit, bukan sumber data operasional. Tampilan master harian tetap membaca OBE secara realtime.

## Pengamanan read-only

- `ObeReadConnection` hanya menerima SQL `SELECT` dan menolak operasi tulis/DDL.
- Endpoint `/academic/sync-obe` dipertahankan hanya untuk kompatibilitas, tetapi tidak lagi menyalin data.
- `ObeSyncService::syncCoursesFromObe()` melempar `LogicException` jika dipanggil dari kode lama.
- Semua INSERT snapshot diarahkan ke koneksi database DEIS.
- Tidak ada file pada proyek `C:\xampp\htdocs\obe` yang diubah pada fase ini.

Untuk produksi, akun `obe_username` tetap disarankan memiliki privilege MySQL `SELECT` saja pada schema OBE sebagai pertahanan di tingkat database.

## Verifikasi

Jalankan dari folder DEIS:

```powershell
php database\migrations\20261019_create_obe_snapshots.php
php tests\obe_read_layer_test.php
php tests\obe_mapping_and_master_test.php
php tests\obe_snapshot_and_navigation_test.php
```

Tes terakhir membuat snapshot di dalam transaksi DEIS lalu melakukan rollback. Sebelum dan sesudah proses, fingerprint seluruh tabel OBE yang berkaitan dibandingkan untuk memastikan tidak ada perubahan.
