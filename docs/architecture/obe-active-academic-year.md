# Tahun Akademik Aktif — Satu Master OBE

Mulai 20 Oktober 2026, OBE adalah satu-satunya master tahun akademik aktif untuk DEIS.

## Perilaku

- DEIS membaca `obe_cpl.tahun_akademik` melalui koneksi read-only.
- Label tahun dan semester pada sidebar berasal langsung dari OBE.
- Pengguna DEIS tidak dapat mengganti semester secara lokal.
- `AcademicYear::getActive()` selalu meminta konteks aktif OBE.
- Jika periode OBE belum ada di tabel lokal, DEIS membuat proyeksi teknis untuk kebutuhan foreign key data operasional.
- Semua periode lokal lain dinonaktifkan; tidak ada keputusan periode aktif yang berasal dari DEIS.
- Mapping `academic_year` mencatat ID OBE dan ID proyeksi DEIS dengan `matched_by=OBE_ACTIVE_PROJECTION`.
- Bila OBE tidak tersedia atau tidak memiliki periode aktif, DEIS gagal secara terbuka dan tidak memakai periode lokal lama.

Proyeksi lokal bukan master dan tidak dapat dikelola melalui menu DEIS. Perubahan periode dilakukan hanya di OBE; akses berikutnya ke DEIS otomatis memproyeksikan periode aktif tersebut.

## Baseline setelah penerapan

- OBE: `2026/2027 Ganjil`, ID OBE `7`.
- DEIS: `2026/2027 Ganjil`, ID proyeksi lokal `4`.
- Periode lama `2025/2026 Genap` telah dinonaktifkan di DEIS.

## Verifikasi

```powershell
php tests\obe_active_academic_year_test.php
```

Tes membandingkan tahun, semester, ID mapping, jumlah periode aktif, dan fingerprint OBE sebelum serta sesudah proyeksi.
