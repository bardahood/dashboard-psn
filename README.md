# Dashboard PSN

Dashboard Proyek Strategis Nasional untuk Tim Koordinasi Perencanaan dan Pengendalian PSN, Kementerian PPN/Bappenas. Dibangun mengikuti `PROMPT_Pengembangan_Dashboard_PSN.md` dan skema `skema_psn_dashboard_terpadu.sql` (47 tabel, 12 view).

## Stack

Laravel 11 (PHP 8.2+) · Blade + Tailwind + Alpine (via Breeze) · MySQL 8.0+/MariaDB 10.6+ · spatie/laravel-permission · Livewire · maatwebsite/excel · barryvdh/laravel-dompdf · Chart.js.

## Tema & Branding

Logo dan palet warna aplikasi (publik & admin) mengikuti identitas visual **Kementerian PPN/Bappenas**, diambil dari materi resmi yang dilampirkan pada prompt pengembangan (`Update_Project_Profile_Final_Rapat_9_Sept.pptx`):

- Logo (`public/images/logo-bappenas.png`) menggantikan logo Laravel bawaan Breeze di `resources/views/components/application-logo.blade.php` — dipakai di halaman login/register (`layouts/guest.blade.php`), nav admin (`layouts/navigation.blade.php`), dan header situs publik (`layouts/public.blade.php`, ditaruh di atas chip putih agar kontras dengan header navy).
- Skala warna `blue` bawaan Tailwind di-override di `tailwind.config.js` dengan navy resmi logo (`#346698` sebagai `blue-600`), sehingga seluruh kelas `bg-blue-*`/`text-blue-*` yang sudah dipakai di ~48 file Blade otomatis mengikuti warna korporat tanpa perlu diedit satu per satu. Ditambahkan pula skala `gold` (`#ca9934`, elemen emas pada logo) untuk aksen terbatas: garis atas nav admin dan status tab aktif (menggantikan `indigo` bawaan Breeze).
- Setelah mengubah `tailwind.config.js`, jalankan `npm run build` (atau `npm run dev` saat development) agar CSS terkompilasi ulang.

## Instalasi

```bash
composer install
npm install && npm run build
cp .env.example .env   # lalu isi DB_* sesuai server MySQL Anda
php artisan key:generate
php artisan migrate --seed
```

Login awal setelah seeding: `admin@bappenas.go.id` / `password` (role **Super Admin** — segera ganti password di lingkungan produksi). Seeder juga membuat 5 akun demo lain (satu per role) untuk mencoba tiap tingkat akses — daftar lengkap kredensial & matriks permission per role ada di **[`AKSES.md`](AKSES.md)**.

## Struktur Basis Data

Seluruh 47 tabel dan 12 view pada `skema_psn_dashboard_terpadu.sql` dikonversi menjadi migration Laravel (`database/migrations/2026_09_16_*`), satu file per tabel, sesuai urutan dependency FK pada file SQL asli. CHECK constraint dan index FULLTEXT dijalankan hanya pada koneksi MySQL/MariaDB (lihat guard `Schema::getConnection()->getDriverName() === 'mysql'` di tiap migration) — aplikasi ini **tidak** didesain untuk SQLite.

Model Eloquent (`app/Models/*.php`) dibuat untuk seluruh 47 tabel lengkap dengan relasi `belongsTo`/`hasMany` berdasarkan foreign key pada skema, termasuk hierarki self-reference `ro_proyek.ro_induk_id` (RO induk → Aktivitas turunan).

Seeder (`database/seeders/`) mengisi seluruh data referensi dari Bagian 8 skema SQL apa adanya, termasuk 36 baris `ref_kriteria_perencanaan` (14 kriteria Permen PPN/Bappenas No. 4/2025), ditambah `RefProvinsiSeeder` (38 provinsi + "Nasional", tidak ada di file SQL sumber tapi diperlukan agar filter lokasi berfungsi).

**Data riil PSN**: `MatriksSandinganPsnSeeder` mengimpor **388 PSN** dari file resmi `Matrik Sandingan Data PSN 2026` (dibundel di `database/seeders/data/Matrik_Sandingan_Data_PSN_2026.xlsx`, ikut ter-seed otomatis lewat `php artisan migrate --seed`) via `App\Support\MatriksSandinganImporter` — mengisi tabel `psn`, `psn_penanggung_jawab` (memecah kolom K/L multi-nilai seperti "Menteri Sosial dan Menteri Pekerjaan Umum" menjadi 2 baris), `psn_sumber_data` (4 sumber: RKP Pemutakhiran 2026, PEKS3, PSI, Permenko), dan `psn_ketersediaan`. Kolom lokasi pada file sumber berupa narasi bebas (kadang multi-provinsi mis. "Provinsi Jawa Barat dan Provinsi Jawa Tengah", kadang bukan nama provinsi sama sekali mis. "Ibu Kota Nusantara", "Indonesia Bagian Timur") sehingga dicocokkan best-effort ke 38 provinsi baku (375 dari 388 baris berhasil dipetakan); baris yang tidak cocok dibiarkan `provinsi_id` NULL sesuai prinsip validasi longgar pada prompt, bukan bug importer. Untuk mengimpor ulang atau file matrik versi lain, jalankan `php artisan psn:import-matriks {path-ke-file.xlsx}` (idempotent — menghapus data psn lama sebelum impor).

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
- Test otomatis: relasi model & CHECK constraint (`PsnRelationsTest`), middleware role (`AdminAccessTest`), komponen Livewire sub-profil (`LivewireSubResourceTest`), wizard Pengendalian termasuk unggah file (`KunjunganPengendalianWizardTest`), wizard Perencanaan termasuk visibilitas kriteria kondisional & gate Kriteria Utama (`KunjunganPerencanaanWizardTest`), audit log & sinkronisasi PSI (`AuditLogAndSyncPsiTest`), export laporan PDF/Excel (`LaporanExportTest`), peta sebaran (`PetaTest`), dan import Matrik Sandingan (`MatriksSandinganImporterTest`) — 54 test, seluruhnya hijau

**Selesai (Sprint 5, Tier 2 penutup, sesuai Bagian 9 prompt):**
- **Manajemen Dokumen** (`DokumenController`, `/admin/dokumen`) — repositori lintas PSN atas seluruh bukti dukung yang diunggah pada Bagian H Instrumen Kunjungan Pengendalian (`kunjungan_pengendalian_dokumentasi`), agar dapat ditelusuri per-PSN/kategori tanpa membuka wizard satu per satu. Tidak menambah tabel baru (existing-first) — hanya menyandingkan data yang sudah ada
- **Filter lanjutan**:
  - Admin **Matriks Sandingan** (`/admin/matriks-sandingan`): filter per klaster, per provinsi, dan checkbox **"hanya tampilkan yang ada gap"** (PSN yang bolong di salah satu dari 4 sumber) — inti kegunaan halaman rekonsiliasi sumber setelah 388 PSN riil diimpor
  - **Daftar PSN publik** (`/psn`): tambahan filter **K/L Penanggung Jawab** (join ke `psn_penanggung_jawab`/`ref_instansi`), melengkapi filter klaster/provinsi/status yang sudah ada sejak Sprint 1
- Test otomatis: repositori dokumen & otorisasinya (`DokumenManagementTest`), filter lanjutan Matriks Sandingan & Daftar PSN (`FilterLanjutanTest`) — total 58 test, seluruhnya hijau

**Selesai (Manajemen Pengguna & Hak Akses, Bagian 5.2 & 6 prompt):**
- **`/admin/pengguna`** (`PenggunaController`, permission `pengguna.manage`) — CRUD `ref_pic` + `hak_akses` + assign role spatie/laravel-permission dalam satu form (tambah & ubah pengguna), lengkap dengan pilihan instansi, role, level akses legacy (`Admin`/`Editor`/`Viewer`), dan status aktif
- **Tanpa tombol hapus secara sengaja** — mencabut akses dilakukan dengan menonaktifkan (`hak_akses.is_active = false`), bukan menghapus data, karena `pic_id` direferensikan sebagai histori oleh banyak tabel lain (audit_log, kunjungan lapangan, dst)
- **Middleware `akun.aktif`** (`CekHakAksesAktif`, berlaku di seluruh route `/admin/*`) memeriksa `hak_akses.is_active` tiap request — PIC yang seluruh hak_akses-nya non-aktif otomatis di-logout dengan HTTP 403, sesuai instruksi eksplisit Bagian 6 prompt ("`hak_akses.is_active` dicek di middleware — nonaktifkan akses tanpa hapus histori")
- Guard tambahan: Super Admin tidak bisa menonaktifkan akunnya sendiri (mencegah terkunci total dari sistem)
- Seeder menambahkan 1 akun contoh nonaktif (`nonaktif@bappenas.go.id`) untuk mendemonstrasikan middleware ini langsung setelah `migrate --seed`
- Detail lengkap kredensial & matriks permission ada di **[`AKSES.md`](AKSES.md)**
- Test otomatis: `ManajemenPenggunaTest` (CRUD, guard self-deactivation, blokir middleware, gating permission) — total 64 test, seluruhnya hijau

**Selesai (hasil analisis kesesuaian dengan output KAK konsultansi):**

Selain Bagian 9 prompt pengembangan, dilakukan juga analisis silang terhadap dokumen KAK (Kerangka Acuan Kerja) konsultansi Tim Koordinasi Perencanaan dan Pengendalian PSN untuk mencari kebutuhan yang implisit di KAK tapi belum tercermin di aplikasi. 4 gap prioritas-rendah/cepat berikut sudah ditutup (existing-first, tanpa tabel baru kecuali kolom kecil):

- **Kategori Usulan (Carryover vs Usulan Baru)** — kolom `psn.kategori_usulan` (nullable, CHECK constraint) memenuhi KAK Bagian 3a ("Daftar PSN berjalan/carryover dan usulan baru"). Filter & kolom tampil di Data PSN admin dan Daftar PSN publik, badge di halaman detail publik.
- **Rekomendasi Keluar dari Daftar PSN** (`/admin/evaluasi-keluar`, permission `profil.manage`) — memenuhi KAK Bagian 3c. Menyaring `psn_evaluasi_status` yang `masih_butuh_status_psn = false` beserta justifikasinya; mekanismenya sudah ada sejak awal (Evaluasi Status pada profil PSN), halaman ini hanya merekapnya lintas-PSN. Tertaut dari halaman Data PSN.
- **Rekap Kelengkapan Administrasi & Verifikasi Usulan** (`/admin/verifikasi-usulan`, permission `perencanaan.manage`) — memenuhi KAK Bagian 3d. Merekap lintas seluruh Instrumen Kunjungan Perencanaan: jumlah dokumen Lengkap/Sebagian/Tidak Ada (dari 9 dokumen tetap), status gate Kriteria Utama, skor keseluruhan, dan rekomendasi otomatis. Tertaut dari halaman Kunjungan Perencanaan.
- **Filter "Fokus Klaster" pada Reporting** — KAK menyoroti klaster berbeda per periode laporan (mis. Laporan Awal: Energi & Pangan; Interim: Konektivitas/Hilirisasi/SDA). Ketiga unduhan di `/admin/laporan` (Ringkasan PDF, Matriks Sandingan Excel, Daftar PSN Excel) kini menerima filter klaster opsional lewat checkbox, tanpa perlu konsep "periode laporan" tersimpan di database.
- **Ringkasan Debottlenecking per Klaster** (`/admin/debottlenecking`, permission `pengendalian.manage`) — memenuhi KAK Bagian 2b-2c ("identifikasi masalah ... faktor penghambat, isu dan risiko" dan "rekomendasi percepatan ... ringkasan hasil debottlenecking"). Menyatukan 3 sumber yang sebelumnya tersebar: register risiko + status triwulanan terkini (`risiko_psn`/`risiko_status_periode`), kebutuhan regulasi + status verifikasi lapangan terkini (`kebutuhan_regulasi`/`kunjungan_pengendalian_regulasi`, dengan penanda "Terlambat" bila target tahun sudah lewat & belum selesai), dan isu/tindak lanjut terbaru per PSN dari Instrumen Kunjungan Pengendalian — semua filterable per klaster fokus. Tertaut dari halaman Kunjungan Pengendalian.
- Test otomatis: `AnalisisKakGapTest` (kategori usulan + filter, evaluasi keluar + gating, rekap verifikasi usulan, filter klaster pada reporting, ringkasan debottlenecking + gating) — total 74 test, seluruhnya hijau.

**Belum dikerjakan / perlu klarifikasi lebih lanjut sebelum dikerjakan** (sisa hasil analisis KAK, lihat riwayat percakapan untuk detail lengkap tiap poin):
- Instrumen penilaian manfaat PSN terhadap Trisula Pembangunan (KAK 1a/2a/5f) — saat ini `trisula_kontribusi_psn` hanya pencatatan data, bukan instrumen skoring seperti Kunjungan Perencanaan/Pengendalian
- Pemisahan format Laporan Presiden vs Laporan Semester (KAK 4b) — butuh contoh/template resmi sebelum dibuat dua versi terpisah
- Konsep "status keberlanjutan PSN" pasca-konstruksi (KAK 5b) — perlu klarifikasi definisi, apakah beda dari `status_psn_id` (lifecycle) yang sudah ada
- Penanda "sampel penilaian trisula tahun ini" (KAK 5f) dan versioning instrumen kriteria (KAK 5g) — prioritas rendah, ditunda

**Tidak dikerjakan (di luar scope aplikasi):** rencana kerja/timeline konsultan, evaluasi kinerja dukungan konsultan — ini deliverable administratif proses konsultansi, bukan fitur dashboard.

## Kesesuaian dengan Pedoman Project Profile PSN

Dilakukan analisis silang terhadap dokumen resmi **Pedoman Project Profile PSN** (dilampirkan terpisah dari KAK) untuk memastikan struktur pengisian data selaras dengan tata cara pengisian Project Profile yang berlaku. Sebagian besar komponen pedoman (Struktur Kerangka Kerja Logis via `kode_rkp`, Status PSN 6 tahap, Stakeholder Mapping & Kerangka Kelembagaan 4-level via `stakeholder_psn.level_kelembagaan`, Lokasi per-RO via `ro_proyek.lokasi`, Indikasi Sumber Pendanaan per periode via `ro_target_periode.indikasi_sumber_pendanaan` — 5 kategori sesuai Permen PPN 4/2025 Pasal 18, target Trisula tahunan **dan** triwulanan via `trisula_target_periode`) **sudah tercermin persis di skema sejak awal** — cek ini menemukan skema jauh lebih matang dari dugaan awal.

Field yang benar-benar hilang dan sudah ditambahkan (existing-first, memperluas tabel yang ada):

- **PJ Risiko & tenggat perlakuan** — `risiko_psn` sebelumnya tidak punya penanggung jawab maupun target mulai/selesai perlakuan risiko, padahal pedoman mensyaratkan keduanya pada Profil Risiko. Ditambahkan `penanggung_jawab_id` (FK `ref_pic`), `target_mulai`, `target_selesai`.
- **Critical Path berbasis Risiko** — pedoman meminta risiko dikelompokkan ke Proyek/RO tertentu dan ditandai titik kritis; `risiko_psn` sebelumnya tidak tertaut ke `ro_proyek` sama sekali. Ditambahkan `ro_id` (FK `ro_proyek`), `is_titik_kritis`, `tahun_pelaksanaan_perlakuan` — ditampilkan juga di Ringkasan Debottlenecking (GAP #3 di atas).
- **Sub-Kategori Indeks Modal Manusia** — pedoman mensyaratkan pemilihan kategori Pendidikan atau Kesehatan sebelum indikator bebas teks diisi untuk Trisula SDM. Ditambahkan `trisula_kontribusi_psn.sub_kategori_sdm` (CHECK IN Pendidikan/Kesehatan).
- **Label kategori Trisula** diperjelas mengikuti nomenklatur resmi pedoman (Pertumbuhan Ekonomi Berkualitas, Penurunan Kemiskinan dan Ketimpangan, Peningkatan Kualitas SDM) — nilai kolom tetap kode singkat lama agar tidak mengubah data existing, hanya label UI yang diperkaya.
- **Visualisasi Kerangka Kelembagaan** — diagram skematik hubungan antar pihak yang secara eksplisit disyaratkan pedoman, sebelumnya tidak punya tempat penyimpanan sama sekali. Ditambahkan `psn.diagram_kelembagaan_path` + upload gambar di form Data PSN (admin), ditampilkan di halaman detail admin & publik.
- **Target Fisik vs Target Persentase RO** — pedoman eksplisit meminta dua jenis target terpisah per RO per periode ("target fisik" dalam satuan RO dan "target persentase penyelesaian"); `ro_target_periode` sebelumnya hanya punya satu kolom `target` generik. Ditambahkan `target_persen` (decimal, 0-100) sebagai pasangan kolom `target` yang sudah ada (kolom `target` dipertahankan sebagai "target fisik", tidak diganti nama untuk menghindari migrasi data). Form & tabel periode RO menampilkan keduanya sebagai kolom terpisah, dengan label satuan RO ditampilkan dinamis di label Target Fisik.
- Test otomatis: `PedomanProjectProfileTest` (PJ & tenggat risiko, Critical Path, sub-kategori IMM, upload/hapus/tolak-non-gambar diagram kelembagaan, target fisik+persentase RO terpisah) — total 80 test, seluruhnya hijau.

**Perlu klarifikasi lebih lanjut sebelum dikerjakan** (sisa hasil analisis pedoman ini):
- Diagram Kerangka Kerja Logis (cascading PN→PP→KP→Proyek/RO) — belum ada representasi terstruktur, hanya `psn.kode_rkp` sebagai kode acuan
- Pedoman Work Breakdown Structure (5 pendekatan: Linear/Spasial, Deliverable, Trade/EPC, Phased, Geographical) — ini panduan penamaan RO yang sudah bisa diterapkan lewat field teks bebas yang ada, belum ada tooltip/rujukan pedoman di form RO/Proyek

## Analisis Carryover RKP 2027

Lampiran resmi **"Daftar PSN dalam RKP 2027"** (.docx, dibundel di `database/seeders/data/Daftar_PSN_RKP_2027.docx`) disandingkan dengan data PSN dashboard (hasil impor Matrik Sandingan, bersumber dari RKP Pemutakhiran 2026/Perpres 68 -- dashboard belum memiliki flag sumber data terpisah untuk "RKP 2025") untuk mengidentifikasi proyek yang berlanjut (carryover) ke RKP 2027.

- **`App\Support\DocxTableParser`** — parser generik `.docx` tanpa dependensi pustaka pihak ketiga (unzip + baca `word/document.xml` via DOMDocument/XPath). Sel tabel yang di-merge horizontal (`w:gridSpan`) diduplikasi teksnya sebanyak span agar jumlah kolom konsisten per baris, meniru perilaku `python-docx` yang dipakai saat membuat prototipe fitur ini.
- **`App\Support\Rkp2027CarryoverAnalyzer`** — mem-parsing 24 tabel/346 baris lampiran (baris "judul grup" yang di-merge penuh 1 kolom dikenali sebagai nama Program payung, bukan baris proyek), lalu mencocokkan tiap nama proyek terhadap `psn.nama_psn` memakai fuzzy matching (kombinasi `similar_text()` dan Jaccard token, ambang skor 0.55 — dikalibrasi manual terhadap sampel data). Hasil dikelompokkan 3 kategori:
  - **Carryover** (skor &ge; 0.55): PSN existing yang muncul lagi di RKP 2027.
  - **Perlu ditinjau manual**: proyek RKP 2027 tanpa kecocokan meyakinkan — kemungkinan redaksional berbeda dari proyek existing (mis. "RDMP RU V Balikpapan" belum tentu proyek baru, bisa jadi penamaan ulang) atau benar-benar usulan baru. Sengaja **tidak** auto-diputuskan/auto-insert karena berisiko salah pada data pemerintah.
  - **Tidak ditemukan lagi di RKP 2027**: PSN existing yang tidak terdeteksi di lampiran — kandidat untuk ditindaklanjuti lewat mekanisme Evaluasi Status/Rekomendasi Keluar dari Daftar PSN yang sudah ada.
- **`php artisan psn:analisis-rkp2027 [--terapkan]`** — jalankan analisis dari CLI; `--terapkan` mengisi `psn.kategori_usulan = 'Carryover'` untuk PSN yang cocok dan **belum** berkategori (tidak menimpa isian manual).
- **`/admin/analisis-rkp2027`** (permission `profil.manage`) — halaman ringkasan (3 kartu jumlah), tabel "Perlu Ditinjau Manual", dan tabel "Tidak Ditemukan Lagi di RKP 2027", plus tombol untuk menerapkan kategori Carryover. Tertaut dari halaman Data PSN.
- **Kolom "RKP 2027" pada Matriks Sandingan** — hasil pencocokan dituliskan sebagai sumber ke-5 di `ref_sumber_data`/`psn_sumber_data` (mengikuti pola 4 sumber lain: RKP 2026, PEKS3, PSI, Permenko), sehingga langsung tampil sebagai kolom &check;/&times; di `/admin/matriks-sandingan` (view `v_psn_sandingan_sumber` diperluas) tanpa perlu halaman terpisah. Filter "Hanya tampilkan yang ada gap" ikut memperhitungkan kolom ini. `Rkp2027CarryoverSeeder` menjalankan analisis & penerapan ini otomatis setelah `MatriksSandinganPsnSeeder` pada setiap `migrate:fresh --seed`, agar hasilnya konsisten terlihat tanpa langkah manual.
- Hasil pada data terkini (380 PSN, lihat pembaruan Matrik Sandingan 17 Sept 2026 di bawah): dari 303 proyek/program pada lampiran RKP 2027, 288 cocok (282 PSN unik) diklasifikasikan Carryover & ditandai tersedia di kolom RKP 2027, 15 perlu ditinjau manual (belum diputuskan otomatis), dan 98 dari 380 PSN existing tidak terdeteksi lagi di RKP 2027 (ditandai tidak tersedia di kolom RKP 2027).
- Test otomatis: `Rkp2027CarryoverAnalyzerTest` (parser docx, klasifikasi 3 kategori, penerapan kategori tanpa menimpa isian manual, penerapan ke kolom RKP 2027 pada Matriks Sandingan, halaman admin + gating permission).

**Catatan lingkungan pengembangan:** Chart.js dan Leaflet.js dimuat lewat CDN (`cdn.jsdelivr.net`) — pada sandbox pengembangan ini akses keluar ke CDN tsb diblokir sehingga chart/peta tidak bisa diverifikasi tampil secara visual di sini, namun payload data JSON yang dikirim ke browser (`markers`, dataset chart) sudah diverifikasi benar; pada lingkungan produksi dengan akses internet normal, chart & peta akan tampil seperti biasa.

**Catatan integrasi Breeze + Livewire:** `resources/js/app.js` sengaja **tidak** meng-import/menjalankan Alpine.js sendiri karena Livewire 3 (`@livewireScripts`) sudah membundel dan menjalankan Alpine miliknya sendiri secara otomatis. Menjalankan dua instance Alpine sekaligus akan merusak sinkronisasi `wire:model` (gejala: form edit Livewire tidak ter-prefill, tapi tidak ada error yang terlihat) — jangan menambahkan `import Alpine from 'alpinejs'; Alpine.start();` kembali ke `app.js`.

## Pembaruan Master Data PSN Kode & Matrik Sandingan (17 Sept 2026)

Dua file resmi dianalisis dan diterapkan langsung ke database: **`Master_Data_PSN_Kode.xlsx`** (380 baris `Kode_PSI` + nama PSN) dan **`Matrik_Sandingan_Data_PSN_2026_17_Sept_2026.xlsx`** (380 baris, menggantikan Matrik Sandingan lama yang 388 baris). Kedua file terbukti selaras nama-per-baris 100% terhadap satu sama lain (sumber yang sama), tapi berbeda dari Matrik lama: 43 nama proyek pada Matrik lama ternyata **terpotong** (mis. `"Infrastruktur Kereta Api Logistik di Kalimantan Ti"`) dan sudah diperbaiki lengkap pada versi 17 Sept, plus beberapa proyek baru/dipecah-gabung ulang (bukan penghapusan data, melainkan koreksi & pemutakhiran redaksional).

- **`psn.kode_rkp` terisi** — sebelumnya kolom ini ada di skema & form admin tapi tidak pernah diisi importer manapun. `MatriksSandinganImporter::importKodeRkp()` mencocokkan `Kode_PSI` ke `psn.nama_psn` persis sama (378 dari 380 cocok; 2 baris di Master Kode memang tidak punya kode pada sumbernya, dibiarkan null sesuai prinsip validasi longgar). Dipanggil otomatis dari `MatriksSandinganPsnSeeder` dan tersedia sebagai opsi `--kode=` pada `php artisan psn:import-matriks`.
- **Ketersediaan Data dipecah jadi 2 dimensi** — Matrik 17 Sept memisahkan "Ketersediaan Data" (1 kolom Ada/Tidak Ada + Keterangan) yang lama menjadi 2 kolom independen: **Data Gambaran Umum Proyek** dan **Data Project Profile Lengkap untuk Kebutuhan Evaluasi**. `psn_ketersediaan` diperluas dengan `jenis_ketersediaan` (CHECK IN 'Gambaran Umum','Project Profile Lengkap'; unique constraint diperluas jadi `psn_id + periode_pemutakhiran + jenis_ketersediaan` agar 2 baris per PSN per periode valid). Kedua dimensi ini sebelumnya tidak tertampil di UI manapun -- sekarang ditambahkan sebagai 2 kolom baru di `/admin/matriks-sandingan` (`v_psn_sandingan_sumber` diperluas, konsisten dengan pola kolom RKP 2027).
- File bundel lama (388 baris, nama terpotong) diganti isinya dengan file 17 Sept di path yang sama (`database/seeders/data/Matrik_Sandingan_Data_PSN_2026.xlsx`) supaya seluruh referensi (`psn:import-matriks`, seeder, test) tidak perlu diubah path-nya.
- Test otomatis: `MatriksSandinganImporterTest` (angka-angka disesuaikan ke 380 PSN, ditambah test `importKodeRkp`).

## Verifikasi Menu "Penjabaran Tahunan" (4 kebutuhan)

Permintaan verifikasi terhadap 4 menu "Penjabaran Tahunan" berikut dicek langsung terhadap kode & skema (bukan asumsi):

1. **Kontribusi Terhadap Trisula Pembangunan (Target TW)** — **GAP NYATA, sudah diperbaiki.** Skema `trisula_target_periode` sudah mendukung `tipe_periode` TRIWULANAN sejak awal, tapi `AnnualTargetManager` (komponen Livewire yang mengelola tab Trisula) selama ini **hardcode** `tipe_periode = 'TAHUNAN'` -- tidak ada jalur kode manapun yang pernah menulis baris TRIWULANAN. Diperbaiki: ditambahkan panel "Target/Realisasi Triwulanan" terpisah (tahun + triwulan + target + realisasi + status capaian, dengan daftar & hapus per baris) di bawah grid tahunan pada tab Trisula, hanya muncul untuk type `trisula`. Kunci pencocokan `updateOrCreate` pada grid tahunan turut diperbaiki agar menyertakan `tipe_periode` -- sebelumnya berisiko salah timpa baris triwulanan bila kebetulan tahun yang sama diisi lewat grid tahunan.
2. **RO/Proyek/Aktivitas/Critical Path (Target Bulanan/Triwulanan)** — **sudah ada.** `RoProyekManager` + `ro_target_periode` sudah mendukung `tipe_periode` TAHUNAN/TRIWULANAN/BULANAN sejak fitur RO/Proyek dibangun; Critical Path direpresentasikan lewat `risiko_psn.is_titik_kritis` (ditautkan ke RO via `ro_id`, hasil analisis Pedoman Project Profile PSN) yang tampil di tab Risiko & halaman Ringkasan Debottlenecking.
3. **Deskripsi/Isu Lainnya dan Kebutuhan Dukungan** — **sudah ada.** Tab "Isu Lainnya" pada profil PSN (tabel `psn_isu_lainnya`: `deskripsi_isu` + `kebutuhan_dukungan`) sudah tersedia sejak build awal.
4. **Kebutuhan Status PSN Tahun Selanjutnya/Justifikasi Kebutuhan** — **sudah ada.** Tab "Evaluasi Status" pada profil PSN (tabel `psn_evaluasi_status`: `tahun_evaluasi` + `masih_butuh_status_psn` + `justifikasi`) sudah tersedia sejak build awal, juga jadi basis halaman Rekomendasi Keluar dari Daftar PSN.

Item #2-4 di atas sudah tersedia sejak awal namun tersebar di tab-tab terpisah pada profil PSN (RO/Proyek, Risiko, Isu Lainnya, Evaluasi Status), bukan dikelompokkan literal di bawah satu label "Penjabaran Tahunan" -- tidak diubah pengelompokannya karena berfungsi sama, hanya penamaan/navigasi yang berbeda dari dokumen sumber.

- Test otomatis: `PedomanProjectProfileTest` (ditambah test Target Triwulanan Trisula, memverifikasi baris TAHUNAN & TRIWULANAN tersimpan terpisah tanpa saling menimpa) -- total 89 test, seluruhnya hijau.

## Pembaruan UI/UX (Tampilan Modern)

Refresh visual menyeluruh terhadap seluruh halaman admin & publik, tanpa mengubah struktur data/rute/logika (murni kelas Tailwind & markup) -- 70 file Blade tersentuh.

- **Kartu (card)** — pola lama `bg-white shadow rounded-lg` diganti konsisten di seluruh aplikasi jadi `bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl` (bayangan lebih halus + garis tepi tipis + sudut lebih membulat, gaya dashboard modern).
- **Navigasi admin** (`layouts/navigation.blade.php`) — sticky header dengan backdrop-blur, tab aktif bergaya pill (`bg-blue-50 text-blue-800`) menggantikan garis bawah lama, avatar inisial pengguna menggantikan teks polos. Menu sekunder (Dokumen, Pengguna, Audit Log, Sinkronisasi PSI, Reporting) dikelompokkan ke dropdown **"Lainnya"** supaya nav bar tidak overflow di lebar 1440px (align dengan kebijakan "jangan menambah item baru ke nav utama" dari sesi sebelumnya) -- tetap tampil satu per satu di menu mobile.
- **Situs publik** (`layouts/public.blade.php`) — header sticky, footer 3 kolom (Tautan/Sumber Data), hero Beranda bergaya gradient gelap dengan CTA emas.
- **Login/Register** (`layouts/guest.blade.php`) — latar gradient biru tua dengan pola titik halus, kartu form terangkat dengan shadow lebih tegas.
- **Komponen bersama** — `primary-button`/`secondary-button`/`danger-button` (drop gaya "UPPERCASE tracking-widest" lama, jadi rounded-lg + shadow-sm + transisi halus), `text-input` (rounded-lg), `dropdown`/`modal` (rounded-xl/2xl, ring tipis, backdrop-blur pada overlay modal).
- **Dashboard Executive** — kartu KPI diberi ikon badge berwarna sesuai konteks (biru/emas/merah) dan efek hover shadow.
- Test otomatis: seluruh 89 test tetap hijau (perubahan murni presentasional, tidak menyentuh route/controller/logika).

## Tindak Lanjut Risalah Rapat 21 September 2026

Risalah rapat resmi (dilampirkan terpisah, `Risalah_Rapat_21_September_2026_Update_Uj.docx`) memuat catatan evaluasi lintas-menu Project Profile PSN. Butir-butir yang berupa perubahan aplikasi (existing-first: tidak ada kolom/tabel lama yang diganti nama atau dihapus, hanya ditambah/disembunyikan dari UI) sudah diterapkan:

- **Gambaran Umum** — ditambahkan `psn.nama_sub_proyek` (subjudul di bawah nama PSN, untuk PSN yang terdiri dari beberapa sub-proyek/paket), `psn.data_teknis` (uraian spesifikasi teknis, terpisah dari Output Akhir), dan `psn.bulan_penyelesaian` (dropdown Januari-Desember, tampil digabung dengan Tahun Penyelesaian, mis. "Juni 2028"). Validasi minimal 20 karakter ditambahkan pada Output Akhir, Tujuan Utama, dan Urgensi & Dasar Hukum agar isian tidak asal-asalan (permintaan rapat: hindari jawaban satu-dua kata).
- **Indikator Output/Outcome** — ditambahkan `indikator_psn.baseline_tahun` ("Tahun Baseline (khusus proyek berjalan sebelum 2026)"), kolom Status Capaian disembunyikan dari grid tahunan (dinilai membingungkan tanpa definisi baku), dan input Realisasi untuk tahun yang belum berjalan (> tahun berjalan) dikunci `disabled` di form.
- **Kontribusi Trisula Pembangunan** — label field diperjelas mengikuti istilah rapat ("Trisula" untuk kategori), ditambahkan `trisula_kontribusi_psn.satuan`. Untuk kategori **Kemiskinan** dan **Pertumbuhan Ekonomi**, indikator sudah baku (masing-masing "Penyerapan Tenaga Kerja" dan "Capex dan Opex") sehingga field Indikator otomatis terkunci `readonly` begitu kategori dipilih (`AnnualTargetManager::updatedParentFormKategoriTrisula()`), dengan penegakan ulang di server (`saveParent()`) agar tidak bisa diakali lewat manipulasi form; kategori **Sumber Daya Manusia** tetap bebas diisi karena indikatornya bervariasi per PSN.
- **RO/Proyek/Aktivitas** — form diurutkan ulang mengikuti alur pengisian rapat (Jenis → Nama → Lokasi → Pelaksana → Target Akhir → Satuan → RO Kunci → Baseline). `target_akhir` dan `lokasi` kini **wajib diisi**. Ditambahkan `ro_proyek.baseline_tahun`, `satuan` dijadikan dropdown (Unit/Persentase), `indikasi_sumber_pendanaan` pada target periode dijadikan dropdown (APBN/APBD/BUMN/BU/Swasta/Lainnya), kolom bebas teks "Status" dihapus dari form periode. Field **Pelaksana** kini disaring dari Stakeholder Mapping PSN yang sama (bila belum ada data stakeholder, seluruh instansi referensi ditampilkan sebagai fallback). Ditambahkan upload **Bukti Pelaporan** (`ro_target_periode.bukti_pelaporan_path`, file &le; 8MB, disimpan per RO) tertaut sebagai link unduh di tabel periode, dan dihapus otomatis dari storage saat baris periode dihapus. Realisasi Fisik/Anggaran untuk tahun mendatang dikunci `disabled`. RO/Aktivitas bertanda **RO Kunci** yang belum punya data periode ditandai badge peringatan "⚠ Belum dijabarkan per periode". Realisasi **TAHUNAN untuk tahun berjalan** kini dihitung otomatis sebagai jumlah realisasi TRIWULANAN/BULANAN tahun tsb setiap kali baris periode baru ditambahkan (permintaan rapat: "Realisasi & real. Anggaran tahun berjalan langsung terisi dari TW"), tidak berlaku untuk tahun lampau agar tidak menimpa data final yang sudah diaudit manual.
- **Register Risiko** — form diurutkan ulang (Peristiwa Risiko → PJ Risiko → Perlakuan/Rencana → PJ Perlakuan → Kategori/Aspek → Level Risiko). Kategori/Aspek Risiko yang semula bebas teks dijadikan dropdown baku (Perizinan/Pembebasan Lahan/Pendanaan/Regulasi/Teknis/Lingkungan/Sosial/Lainnya). Ditambahkan `risiko_psn.pelaksana_perlakuan_id` (FK `ref_pic`) untuk membedakan PJ Risiko (risk owner) dari pihak yang benar-benar melaksanakan perlakuan/mitigasi -- disederhanakan dari usulan rapat "bisa lebih dari satu pelaksana" menjadi satu field tambahan (lihat catatan "belum dikerjakan" di bawah untuk versi penuhnya). Field "Risiko Residual Harapan" dihapus dari form (dinilai membingungkan/jarang diisi konsisten di lapangan) -- kolom database dipertahankan (tidak di-drop) untuk data yang sudah terlanjur terisi.
- **Stakeholder Mapping** — label "Level" diganti "Pengelompokan Fungsi" (istilah lama dinilai ambigu), pilihannya diperjelas menjadi deskripsi fungsi (Kebijakan/Regulasi/Pengarah, Fasilitator Wilayah, Operator/Investor/Off-taker, Partisipan/Penerima Manfaat/Riset) -- nilai kolom (1-4) dipertahankan agar data existing tidak berubah. Field "Aktor/Instansi" yang semula bebas teks dijadikan dropdown dari `ref_instansi` agar penamaan instansi konsisten antar-PSN.
- **Navigasi lintas-tab** — ditambahkan tombol "Lanjutkan ke {tab berikutnya} →" di bagian bawah setiap tab profil PSN (Detail → RO/Proyek → Risiko → Indikator → Penerima Manfaat → Trisula → Dasar Hukum → Stakeholder → Kebutuhan Regulasi → Isu Lainnya → Evaluasi Status → Info Memo → Catatan Monev), agar pengisian project profile bisa dituntaskan berurutan tanpa harus kembali ke bar navigasi tab tiap kali (permintaan rapat: alur pengisian yang lebih terarah). Tidak muncul pada tab terakhir atau pada "Kunjungan Pengendalian" (bukan bagian dari alur pengisian linear).
- Test otomatis: `RisalahRapat21SeptTest` (Sub Proyek/Data Teknis/Bulan Penyelesaian, validasi minimal karakter, baseline tahun Indikator, preset & penguncian Indikator Trisula, validasi wajib RO, batas total target periode, upload/hapus bukti pelaporan, agregasi realisasi otomatis, kategori risiko + PJ Perlakuan, dropdown Stakeholder, filter Pelaksana RO dari Stakeholder, tombol Lanjutkan) -- total 105 test, seluruhnya hijau.

**Belum dikerjakan / diblokir, perlu tindak lanjut di luar aplikasi:**
- **Daftar referensi SDGs baku** — rapat menyebut kebutuhan dropdown SDGs resmi, menunggu daftar dari Pak Rafli; belum ada field/tabel SDGs sama sekali di skema saat ini, akan dibangun begitu daftarnya tersedia.
- **Integrasi RO dari sistem Krisna** — rapat menyebut RO idealnya bisa ditarik dari Krisna (sistem eksternal Bappenas); tidak ada API/akses Krisna yang tersedia untuk aplikasi ini, RO tetap diisi manual.
- **Penyelarasan istilah pedoman pengisian vs pedoman aplikasi** (disebutkan sebagai tugas Pak Ujang & Pak Febri) — ini pekerjaan organisasional/dokumentasi antar-tim, bukan perubahan kode.
- **Perlakuan risiko dengan lebih dari satu pelaksana, masing-masing dengan PJ sendiri** — rapat menyebut kemungkinan satu risiko butuh beberapa tindakan perlakuan paralel; disederhanakan pada iterasi ini jadi satu field `pelaksana_perlakuan_id` tambahan (lihat di atas). Versi penuh (tabel anak perlakuan-risiko berulang, masing-masing dengan PJ & tenggat sendiri) belum dibangun karena mengubah `risiko_psn` dari model "1 risiko = 1 perlakuan" menjadi "1 risiko = banyak perlakuan" adalah perubahan struktural yang lebih besar dan sebaiknya dikonfirmasi dulu polanya (mirip `ro_target_periode` atau tabel terpisah?) sebelum dibangun.
- **Rename "Kebutuhan Justifikasi" → "Kebutuhan Status PSN Tahun Selanjutnya"** — penyebutan di risalah rapat ambigu apakah ini sekadar ganti label tab "Evaluasi Status" yang sudah ada atau field yang berbeda; tidak diubah sampai dikonfirmasi.
- Field/menu publik (`public/psn/show.blade.php`, `public/psn/index.blade.php`) **belum** diperbarui menampilkan Sub Proyek/Data Teknis/Bulan Penyelesaian -- perubahan risalah rapat difokuskan ke sisi pengisian data (admin) dahulu; tampilan publik menyusul bila diperlukan.

## Halaman "Project Profile" (Matriks & Detail Lengkap per PSN)

Ditambahkan halaman baca-saja (read-only) baru, `/admin/project-profile`, yang menyatukan seluruh muatan Project Profile satu PSN ke dalam satu dokumen -- berbeda dari tab-tab `admin.psn.*` yang berorientasi pengisian/edit per-bagian. Strukturnya mengikuti persis slide **"Struktur Project Profile"** pada paparan resmi **"Update Project Profile"** (`Update_Project_Profile_Final_Rapat_9_Sept.pptx`, dilampirkan terpisah), yang membagi Project Profile menjadi dua bagian besar:

- **Perencanaan** — Gambaran Umum (Klaster PKPN/PSN, Status PSN, Diagram Kerangka Kerja Logis via Kode RKP, Tujuan Utama, Urgensi & Dasar Hukum, Lokasi, Tahun & Output Akhir, Data Teknis, Nilai Investasi, Pengusul/Pengelola/Kontraktor/Supervisi, Visualisasi Kerangka Kelembagaan), Dasar Hukum, Stakeholder Mapping & Kerangka Kelembagaan, Indikator Output/Outcome (target tahunan 2025-2030), Kontribusi Terhadap Trisula Pembangunan (target tahunan), Penerima Manfaat (target tahunan), Register Risiko, Kebutuhan Regulasi, dan RO/Proyek/Non RO (ringkasan tahunan).
- **Penjabaran Tahunan** — Kontribusi Trisula (Target TW, per tahun terpilih), RO/Proyek/Aktivitas & Critical Path (Target Bulanan/Triwulanan, per tahun terpilih, satu blok per RO dengan tabel periode + link Bukti Pelaporan), Deskripsi/Isu Lainnya dan Kebutuhan Dukungan, serta Kebutuhan Status PSN Tahun Selanjutnya/Justifikasi Kebutuhan. Tahun penjabaran dipilih lewat dropdown (2025-2030, default tahun berjalan) tanpa reload halaman penuh (form GET sederhana).

Dua mode tampilan:

- **Matriks** (`/admin/project-profile`) — satu baris per PSN (seluruh 380 PSN, dipaginasi), dengan kolom ringkasan (Klaster, Status, Provinsi, jumlah RO/Proyek, jumlah Risiko) dan dua **skor kelengkapan**: "Kelengkapan Perencanaan" (X/6: Gambaran Umum, RO/Proyek, Risiko, Indikator, Trisula, Penerima Manfaat) dan "Kelengkapan Penjabaran {tahun berjalan}" (X/4: Trisula TW, RO Bulanan/TW, Isu Lainnya, Evaluasi Status tahun berjalan) -- dipilih lewat pertimbangan performa & keterbacaan dibanding satu tabel raksasa berpuluh-puluh kolom untuk 380 baris sekaligus. Filter: cari nama, Klaster, Status, Provinsi, PKPN/PSN. Klik nama PSN atau tombol "Lihat" untuk membuka Detail.
- **Detail** (`/admin/project-profile/{psn}`) — dokumen lengkap satu PSN seperti dijabarkan di atas, dengan anchor nav "Perencanaan"/"Penjabaran Tahunan" di bagian atas, dan tautan timbal-balik ke/dari halaman edit (`admin.psn.show`/`admin.psn.edit`) serta dari tabel Data PSN dan setiap baris Matriks.

Otorisasi mengikuti `PsnPolicy` yang sudah ada (`viewAny`/`view`, permission `psn.view`) -- tidak ada gate baru, halaman ini murni membaca data existing lewat relasi Eloquent (tidak ada tabel/kolom baru). Ditautkan dari dropdown navigasi "Lainnya" dan dari halaman Data PSN.

- Test otomatis: `ProjectProfileTest` (matriks + filter klaster, detail menampilkan seluruh bagian Perencanaan, filter tahun Penjabaran Tahunan menampilkan periode yang sesuai dan menyembunyikan tahun lain, gating akses tanpa `psn.view`) -- total 110 test, seluruhnya hijau.

## Modul Audit Log: Pantau CRUD Lintas Peran

Modul Audit Log (`/admin/audit-log`, permission `audit.view`) sudah ada sejak build awal (satu tabel `audit_log`, `App\Observers\AuditLogObserver` generik didaftarkan per model di `AppServiceProvider::boot()`), tapi cakupannya terbatas pada 6 model inti dan hanya menautkan `pic_id` (kontak instansi) -- banyak user login (terutama peran internal Bappenas) tidak punya `ref_pic` sama sekali, sehingga kolom "oleh" sering kosong dan sebagian besar aksi CRUD nyata (semua sub-resource profil PSN yang ditulis lewat komponen Livewire, Instrumen Kunjungan, Pengguna) sama sekali tidak tercatat. Diperluas jadi:

- **Cakupan model diperluas dari 6 menjadi 32** (`AppServiceProvider::modelDiaudit()`) -- mencakup seluruh sub-resource profil PSN (Dasar Hukum, Stakeholder, Indikator + target tahunan, Trisula + target periode, Penerima Manfaat + target tahunan, Isu Lainnya, Evaluasi Status, Info Memo, Catatan Monev, RO/Proyek + target periode, Risiko + status periode, Kebutuhan Regulasi), seluruh bagian Instrumen Kunjungan Pengendalian & Perencanaan (kelembagaan, fisik, anggaran, risiko, regulasi, dokumentasi, verifikasi kriteria/lokasi/dokumen teknis/trisula, indeks bukti), serta Manajemen Pengguna (`User`, `RefPic`, `HakAkses`). Model pipeline/impor massal (mis. `MatriksSandinganImporter`, seeder) **sengaja tidak diaudit** -- itu bukan aksi CRUD pengguna, dan bulk `->update()` lewat query builder pula tidak memicu event Eloquent yang dibutuhkan observer.
- **Kolom `user_id` ditambahkan** (`audit_log`, FK ke `users`, existing-first: `pic_id` dipertahankan untuk baris lama) -- menautkan langsung ke akun yang login saat aksi terjadi, tidak lagi bergantung pada apakah user tsb kebetulan punya `ref_pic`.
- **Kolom `role` ditambahkan** (snapshot nama peran spatie/laravel-permission SAAT aksi terjadi, disimpan sebagai teks bukan FK) -- inti dari permintaan "pantau user dengan peran apa saja": jejak audit harus tetap mencerminkan peran yang berlaku ketika aksi dilakukan, bukan peran user itu sekarang (yang bisa sudah berubah/dicabut).
- **Password tidak pernah tercatat** -- `AuditLogObserver` menyaring kolom `password`/`remember_token` dari `nilai_lama`/`nilai_baru` sebelum disimpan, khusus untuk model `User` yang kini ikut diaudit (mencegah hash password bocor ke tabel yang bisa dibaca siapa pun dengan izin `audit.view`).
- **Filter halaman diperluas**: selain per Tabel (sudah ada), ditambahkan filter per Aksi (insert/update/delete), per Peran (dropdown dari `spatie/laravel-permission` Role), dan pencarian nama pengguna. Kolom baru "Peran" ditambahkan di tabel, dan kolom "Oleh" kini mengutamakan nama akun (`user_id`) dengan fallback ke nama PIC lama untuk baris historis yang dibuat sebelum migrasi ini.
- **Halaman Detail Audit Log baru** (`/admin/audit-log/{id}`, tombol "Lihat Detail" di tiap baris) -- menjawab kebutuhan "bisa melihat isi datanya yang dirubah, data apa saja": pratinjau di halaman daftar tetap ringkas (dipotong 25 karakter, maksimal 3 kolom pertama, sisanya diringkas jadi "+N kolom lainnya") supaya tabel tetap mudah dipindai, tapi halaman detail menampilkan **seluruh kolom yang berubah tanpa potongan sama sekali** -- untuk `update` ditampilkan berdampingan Nilai Lama vs Nilai Baru per kolom, untuk `insert` seluruh isi data baru, untuk `delete` seluruh isi data terakhir sebelum dihapus. Nilai boolean ditampilkan "Ya"/"Tidak", nilai kosong "-", dan nilai berbentuk array (jarang terjadi tapi mungkin) di-format JSON rapi.
- Test otomatis: `AuditLogModulTest` (user + peran tercatat pada setiap aksi, cakupan meluas sampai ke sub-resource bukan cuma tabel induk, password tidak pernah tercatat, filter per peran/aksi/nama pengguna, halaman detail menampilkan isi lengkap untuk insert/update/delete tanpa terpotong, gating akses) -- total 119 test, seluruhnya hijau.

## Menjalankan Test

```bash
php artisan test
```

Test memakai koneksi MySQL (bukan SQLite) karena migration menggunakan fitur CHECK constraint & FULLTEXT index yang spesifik MySQL/MariaDB. Siapkan database terpisah untuk testing lalu sesuaikan `phpunit.xml` (`DB_DATABASE`).
