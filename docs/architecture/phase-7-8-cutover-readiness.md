# Fase 7–8 — Cutover Aman dan Kesiapan Operasional

Tanggal implementasi: 19 Oktober 2026

## Fase 7: cutover konsumen akademik

Tujuan fase ini adalah memastikan DEIS tidak kembali menampilkan identitas dosen lokal lama ketika data OBE diminta.

- Nama PJMK pada monitoring berasal dari penugasan aktif OBE atau dokumen RPS OBE.
- Sebelum query OBE dilakukan, nama dosen lokal dikosongkan.
- Jika OBE tidak tersedia, DEIS menampilkan status `OBE tidak tersedia`; tidak ada fallback ke dosen lokal.
- Jika PJMK belum ada, status yang ditampilkan adalah `belum ditetapkan di OBE`.
- Tabel master lokal tetap dipertahankan untuk kompatibilitas FK dan atribut eksekutif, tetapi tidak dianggap sebagai sumber identitas akademik.

Keputusan ini mengikuti prinsip **fail closed**: gangguan integrasi terlihat sebagai gangguan, bukan disamarkan dengan data lama.

## Fase 8: operational readiness

Halaman `/master/obe/status` menyediakan checklist langsung untuk:

1. koneksi dan latensi OBE;
2. penolakan operasi tulis oleh gateway;
3. pemisahan akun database OBE dari akun utama DEIS;
4. blokir mekanisme sinkronisasi lama;
5. cakupan mapping serta jumlah konflik/unmapped; dan
6. ketersediaan snapshot historis.

Status akhir terdiri dari:

- `ready`: seluruh pemeriksaan lulus tanpa catatan;
- `ready_with_warnings`: fungsi kritis aman, tetapi masih ada mapping konflik/unmapped atau snapshot belum dibuat;
- `not_ready`: koneksi, read-only guard, sinkronisasi lama, atau mapping mengalami kegagalan kritis.

Halaman status hanya dapat dibuka oleh `dekan`, `super_admin`, dan `developer`. Halaman tidak menjalankan DML/DDL pada OBE.

## Acceptance test

```powershell
php tests\obe_read_layer_test.php
php tests\obe_mapping_and_master_test.php
php tests\obe_snapshot_and_navigation_test.php
php tests\obe_phase_7_8_readiness_test.php
```

Acceptance test mengambil fingerprint OBE sebelum dan sesudah readiness check. Hasil harus identik.

## Tindak lanjut operasional

- Selesaikan mapping berstatus `CONFLICT` melalui keputusan manual; jangan menebak pasangan berdasarkan kode yang ambigu.
- Buat snapshot pada awal/akhir semester atau sebelum laporan resmi diterbitkan.
- Gunakan akun MySQL khusus dengan privilege `SELECT` saja untuk `obe_username` di lingkungan produksi.
- Pantau halaman status setelah perubahan skema atau deployment OBE.
