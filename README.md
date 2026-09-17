# Dashboard PSN

Dashboard Proyek Strategis Nasional untuk Tim Koordinasi Perencanaan dan Pengendalian PSN, Kementerian PPN/Bappenas. Dibangun mengikuti `PROMPT_Pengembangan_Dashboard_PSN.md` dan skema `skema_psn_dashboard_terpadu.sql` (47 tabel, 12 view).

## Stack

Laravel 11 (PHP 8.2+) · Blade + Tailwind + Alpine (via Breeze) · MySQL 8.0+/MariaDB 10.6+ · spatie/laravel-permission · Livewire · maatwebsite/excel · barryvdh/laravel-dompdf · Chart.js.

## Instalasi

```bash
composer install
npm install && npm run build
cp .env.example .env   # lalu isi DB_* sesuai server MySQL Anda
php artisan key:generate
php artisan migrate --seed
```

Login awal setelah seeding: `admin@bappenas.go.id` / `password` (role **Super Admin** — segera ganti password di lingkungan produksi).

## Struktur Basis Data

Seluruh 47 tabel dan 12 view pada `skema_psn_dashboard_terpadu.sql` dikonversi menjadi migration Laravel (`database/migrations/2026_09_16_*`), satu file per tabel, sesuai urutan dependency FK pada file SQL asli. CHECK constraint dan index FULLTEXT dijalankan hanya pada koneksi MySQL/MariaDB (lihat guard `Schema::getConnection()->getDriverName() === 'mysql'` di tiap migration) — aplikasi ini **tidak** didesain untuk SQLite.

Model Eloquent (`app/Models/*.php`) dibuat untuk seluruh 47 tabel lengkap dengan relasi `belongsTo`/`hasMany` berdasarkan foreign key pada skema, termasuk hierarki self-reference `ro_proyek.ro_induk_id` (RO induk → Aktivitas turunan).

Seeder (`database/seeders/`) mengisi seluruh data referensi dari Bagian 8 skema SQL apa adanya, termasuk 36 baris `ref_kriteria_perencanaan` (14 kriteria Permen PPN/Bappenas No. 4/2025), ditambah `RefProvinsiSeeder` (38 provinsi + "Nasional", tidak ada di file SQL sumber tapi diperlukan agar filter lokasi berfungsi).

## Status Pengembangan

**Selesai (Sprint 1–2 sesuai Bagian 9 prompt):**
- Migration + model + seeder lengkap 47 tabel & 12 view
- Autentikasi (Breeze) + RBAC (spatie/laravel-permission) 6 role sesuai Bagian 6 prompt, dengan `PsnPolicy` yang membatasi role **K/L Pelaksana** hanya pada PSN miliknya sendiri (pengusul/pengelola/kontraktor/supervisi + `psn_penanggung_jawab`)
- Halaman publik: Beranda, Daftar PSN (filter klaster/provinsi/status), Detail PSN, Statistik (Chart.js)
- Admin: Executive Dashboard, CRUD Data PSN (tabel `psn` inti), Matriks Sandingan Sumber (dari view `v_psn_sandingan_sumber`)
- **CRUD profil lengkap PSN** via komponen Livewire (`app/Livewire/Admin/`), diakses lewat tab pada halaman Detail PSN:
  - `RoProyekManager` — hierarki RO induk → Aktivitas turunan (penanda RO Kunci/Critical Path) + target/realisasi per periode (`ro_target_periode`, tahunan/triwulanan/bulanan)
  - `RisikoManager` — register risiko (`risiko_psn`) + pelaporan rutin triwulanan (`risiko_status_periode`), sengaja dipisah dari snapshot verifikasi kunjungan pengendalian sesuai prinsip kunci skema
  - `AnnualTargetManager` — pola master + target/realisasi tahunan (2025–2030) yang dipakai bersama oleh Indikator Output/Outcome, Penerima Manfaat, dan Kontribusi Trisula Pembangunan; % realisasi dihitung otomatis
  - `SubResourceManager` — CRUD generik config-driven untuk Dasar Hukum, Stakeholder Mapping, Kebutuhan Regulasi, Isu Lainnya, Evaluasi Status, Info Memo, dan Catatan Monev
- Cache 15 menit pada halaman publik & Executive Dashboard sesuai Bagian 8 prompt

**Selesai (Sprint 3, sesuai Bagian 9 prompt):**
- **Wizard Instrumen Kunjungan Lapangan Pengendalian** (`KunjunganPengendalianWizard`, `/admin/kunjungan-pengendalian`) — 9 langkah mengikuti Bagian A–I formulir:
  - A Identitas (pilih PSN, saran otomatis kepatuhan frekuensi pelaporan dari `Psn::cekKepatuhanFrekuensiPelaporan()`), B Kelembagaan (5 peran, dibandingkan otomatis dgn instansi tercatat pada profil PSN), C Fisik & D Anggaran per RO (kesesuaian **dihitung otomatis** di model — `KunjunganPengendalianFisik`/`Anggaran::hitungKesesuaian()`, toleransi deviasi ≤5%=Sesuai, 5–20%=Sebagian, >20%=Tidak Sesuai), E Risiko (evaluasi Sesuai/Lebih Baik vs Memburuk dihitung dari perbandingan level Risiko Residual Harapan vs Aktual di `KunjunganPengendalianRisiko::hitungEvaluasiRisiko()`), F Regulasi, G Evaluasi, H Dokumentasi (unggah file ke `storage/app/public` via Livewire `WithFileUploads`), I Kesimpulan & Pengesahan (menampilkan **skor & rekomendasi status pengendalian otomatis** dari `KunjunganPengendalian::skorKeseluruhan()`/`rekomendasiOtomatis()`, tetap dapat ditimpa manual)
  - Setiap bagian tersimpan langsung ke tabel masing-masing begitu diisi (bisa dikerjakan bertahap lintas sesi/kunjungan lapangan)
  - **Catatan penting**: bobot & ambang batas formula skor adalah interpretasi kami atas instruksi "replikasi formula Excel" pada prompt — dokumen Excel instrumen aslinya tidak turut dilampirkan ke sesi ini, jadi tim konsultan perlu memverifikasi/menyesuaikan bobot ini terhadap formula baku bila berbeda
**Selesai (Sprint 4 sebagian, sesuai Bagian 9 prompt):**
- **Wizard Instrumen Kunjungan Lapangan Perencanaan** (`KunjunganPerencanaanWizard`, `/admin/kunjungan-perencanaan`) — verifikasi usulan PSN terhadap 14 kriteria Permen PPN/Bappenas No. 4/2025, dirender **dinamis** dari `ref_kriteria_perencanaan` (bukan di-hardcode):
  - Data Usulan & Hasil PMO, lalu Kriteria Utama/Pendukung/Kesiapan dikelompokkan per `kode_kriteria` dengan rubrik penilaian tampil sebagai helper text
  - Kriteria kondisional (P4/P5/P6 mengikuti `jenis_pengusul`; K3/K4 mengikuti penanda "usulan infrastruktur" pada wizard — skema tidak punya kolom jenis-proyek khusus jadi ini disimpan sebagai state UI, bukan kolom DB) otomatis disembunyikan/ditampilkan lewat `KunjunganPerencanaanWizard::kriteriaTerlihat()`
  - **Kriteria Utama sebagai penggugur**: satu jawaban "Tidak" pada U1-U3 membuat `KunjunganPerencanaan::gateUtamaGagal()` bernilai true dan rekomendasi otomatis langsung "Ditolak", terlepas skor komponen lain
  - Verifikasi Lokasi, Dokumen Teknis (9 dokumen tetap), Dampak Trisula (3 dampak tetap), dan Indeks Bukti
  - **Skor keseluruhan berbobot otomatis**: Pendukung 35% + Kesiapan 35% + Lokasi 15% + Trisula 15% (`KunjunganPerencanaan::skorKeseluruhan()`), dipetakan ke rekomendasi Layak Dilanjutkan/Layak dengan Catatan/Perlu Perbaikan Dokumen/Belum Layak — bobot komponen yang belum terisi didistribusikan ulang secara proporsional agar instrumen bisa dinilai bertahap
  - Sama seperti instrumen Pengendalian: **ambang batas skor adalah interpretasi kami**, bukan dari dokumen Excel asli yang tidak turut dilampirkan

**Selesai (Sprint 4 lanjutan, sesuai Bagian 9 prompt):**
- **Audit log otomatis** via `AuditLogObserver` (generic Eloquent observer terdaftar di `AppServiceProvider` untuk `Psn`, `RoProyek`, `RisikoPsn`, `KebutuhanRegulasi`, `KunjunganPengendalian`, `KunjunganPerencanaan`) — mencatat `created`/`updated`/`deleted` beserta PIC pelaku ke tabel `audit_log`; halaman admin **Audit Log** (`/admin/audit-log`) untuk menelusurinya
- **Sinkronisasi data PSI**: command `php artisan psn:sync-psi` + `PsiSyncService` (skeleton — memanggil endpoint API Direktorat PSI via `Http::`, endpoint/token dikonfigurasi lewat `.env` `PSI_API_ENDPOINT`/`PSI_API_TOKEN` karena API sesungguhnya belum tersedia dari Dit. PSI saat pengembangan), setiap percobaan sinkronisasi dicatat ke `sync_log_psi`; halaman admin **Sinkronisasi PSI** (`/admin/sinkronisasi-psi`) menampilkan riwayat & tombol jalankan manual
- **Reporting PDF/Excel** (`LaporanController`, `/admin/laporan`):
  - Excel: `MatriksSandinganExport` (dari view `v_psn_sandingan_sumber`) dan `DaftarPsnExport` (dari view `v_psn_profil_lengkap`, 14 kolom) via maatwebsite/excel
  - PDF: Laporan Ringkasan (`ringkasan-pdf.blade.php`, layout tabel murni tanpa flexbox karena keterbatasan DomPDF) berisi KPI ringkas, rekap per klaster, per status, per level risiko, dan ketersediaan sumber data via barryvdh/laravel-dompdf
- **Tier 2 — Peta Sebaran PSN** (`/peta`, publik): marker per-provinsi (agregat, karena skema tidak menyimpan koordinat presisi lokasi) menggunakan Leaflet.js + `ProvinsiCoordinates` (koordinat 38 provinsi), `PetaController` meng-cache hasil agregasi 15 menit; klik marker menampilkan daftar PSN pada provinsi tsb dengan tautan ke halaman detail publik
- Test otomatis: relasi model & CHECK constraint (`PsnRelationsTest`), middleware role (`AdminAccessTest`), komponen Livewire sub-profil (`LivewireSubResourceTest`), wizard Pengendalian termasuk unggah file (`KunjunganPengendalianWizardTest`), wizard Perencanaan termasuk visibilitas kriteria kondisional & gate Kriteria Utama (`KunjunganPerencanaanWizardTest`), audit log & sinkronisasi PSI (`AuditLogAndSyncPsiTest`), export laporan PDF/Excel (`LaporanExportTest`), dan peta sebaran (`PetaTest`) — 53 test, seluruhnya hijau

**Belum dikerjakan (sisa Sprint 5, sesuai urutan Bagian 9 prompt):**
- Tier 2: manajemen dokumen (repositori dokumen terpusat lintas PSN), filter lanjutan pada halaman publik/admin (multi-kriteria, simpan filter)

**Catatan lingkungan pengembangan:** Chart.js dan Leaflet.js dimuat lewat CDN (`cdn.jsdelivr.net`) — pada sandbox pengembangan ini akses keluar ke CDN tsb diblokir sehingga chart/peta tidak bisa diverifikasi tampil secara visual di sini, namun payload data JSON yang dikirim ke browser (`markers`, dataset chart) sudah diverifikasi benar; pada lingkungan produksi dengan akses internet normal, chart & peta akan tampil seperti biasa.

**Catatan integrasi Breeze + Livewire:** `resources/js/app.js` sengaja **tidak** meng-import/menjalankan Alpine.js sendiri karena Livewire 3 (`@livewireScripts`) sudah membundel dan menjalankan Alpine miliknya sendiri secara otomatis. Menjalankan dua instance Alpine sekaligus akan merusak sinkronisasi `wire:model` (gejala: form edit Livewire tidak ter-prefill, tapi tidak ada error yang terlihat) — jangan menambahkan `import Alpine from 'alpinejs'; Alpine.start();` kembali ke `app.js`.

## Menjalankan Test

```bash
php artisan test
```

Test memakai koneksi MySQL (bukan SQLite) karena migration menggunakan fitur CHECK constraint & FULLTEXT index yang spesifik MySQL/MariaDB. Siapkan database terpisah untuk testing lalu sesuaikan `phpunit.xml` (`DB_DATABASE`).
