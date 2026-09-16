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
- Cache 15 menit pada halaman publik & Executive Dashboard sesuai Bagian 8 prompt
- Test otomatis (`tests/Feature/PsnRelationsTest.php`, `AdminAccessTest.php`) untuk relasi model & middleware role

**Belum dikerjakan (Sprint 3–5, sesuai urutan Bagian 9 prompt — jangan dikerjakan sebelum Sprint 1–2 stabil di lingkungan nyata):**
- CRUD untuk sub-profil PSN lengkap (dasar hukum, stakeholder, indikator, penerima manfaat, trisula, RO/Aktivitas, risiko, kebutuhan regulasi) — model & relasi sudah siap, tinggal dibuatkan form
- Wizard Livewire Instrumen Kunjungan Lapangan Pengendalian (Bagian A–I) dan Perencanaan (14 kriteria dinamis dari `ref_kriteria_perencanaan`) beserta kalkulasi skor otomatis
- Reporting PDF/Excel (Laporan Presiden/Semester)
- Command `psn:sync-psi` (skeleton sinkronisasi API PSI, endpoint belum tersedia dari Dit. PSI)
- Audit log otomatis via Model Observer
- Tier 2: peta sebaran (Leaflet), manajemen dokumen, filter lanjutan

## Menjalankan Test

```bash
php artisan test
```

Test memakai koneksi MySQL (bukan SQLite) karena migration menggunakan fitur CHECK constraint & FULLTEXT index yang spesifik MySQL/MariaDB. Siapkan database terpisah untuk testing lalu sesuaikan `phpunit.xml` (`DB_DATABASE`).
