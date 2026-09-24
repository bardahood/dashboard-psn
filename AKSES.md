# Akses Sistem — Dashboard PSN

Dokumen ini merangkum cara login, akun demo yang tersedia setelah seeding, dan
hak akses (permission) tiap role. Untuk detail fitur & instalasi, lihat
`README.md`.

> **PERINGATAN KEAMANAN**: seluruh akun & password pada dokumen ini adalah
> data seed untuk lingkungan pengembangan/demo. **Wajib diganti** (atau
> dihapus dan dibuat ulang lewat proses yang aman) sebelum aplikasi ini
> dipakai di lingkungan produksi dengan data PSN yang sesungguhnya. Jangan
> commit file `.env` produksi atau kredensial nyata ke repository ini.

## Cara Login

1. Jalankan aplikasi (`php artisan serve` atau via web server), pastikan
   `php artisan migrate --seed` sudah dijalankan minimal sekali.
2. Buka halaman login: `/login` (mis. `http://127.0.0.1:8000/login`).
3. Login dengan salah satu akun pada tabel di bawah. Setelah login akan
   diarahkan ke Executive Dashboard admin (`/admin`).
4. Menu navigasi admin otomatis menyesuaikan permission masing-masing role
   (mis. role tanpa `laporan.export` tidak akan melihat menu "Reporting").

## Akun Demo (dibuat otomatis oleh `DatabaseSeeder`)

Password **semua akun** di bawah ini sama: `password`.

| Role | Email | Password | Catatan |
|---|---|---|---|
| Super Admin | `admin@bappenas.go.id` | `password` | Akses penuh ke seluruh modul & permission |
| Admin Pengendalian | `admin.pengendalian@bappenas.go.id` | `password` | Kelola RO/Proyek, Risiko, Regulasi, Instrumen Pengendalian, Memo, Reporting |
| Admin Perencanaan | `admin.perencanaan@bappenas.go.id` | `password` | Kelola profil PSN & Instrumen Perencanaan, Reporting |
| Verifikator Lapangan | `verifikator@bappenas.go.id` | `password` | Mengisi Instrumen Kunjungan Pengendalian & Perencanaan |
| K/L Pelaksana | `kl.pelaksana@bappenas.go.id` | `password` | Terhubung ke instansi **Menteri Pekerjaan Umum** — hanya bisa mengubah PSN yang instansi ini terlibat (pengusul/pengelola/kontraktor/supervisi/penanggung jawab) |
| Viewer Internal | `viewer@bappenas.go.id` | `password` | Akses baca (`psn.view`) + Audit Log, tanpa hak ubah data |
| *(contoh nonaktif)* | `nonaktif@bappenas.go.id` | `password` | `hak_akses.is_active = false` — mendemonstrasikan penolakan login oleh middleware `akun.aktif` (lihat bagian Manajemen Pengguna) |

Akun-akun ini dibuat ulang setiap `php artisan migrate:fresh --seed`
dijalankan (idempotent terhadap skema, bukan menambah duplikat karena tabel
`users` ikut dikosongkan oleh `migrate:fresh`).

## Matriks Permission per Role

Didefinisikan di `database/seeders/RoleSeeder.php`. Permission dicek di
level route (`permission:` middleware) maupun di Blade (`@can`) dan
Livewire (`Gate::authorize`).

| Permission | Super Admin | Admin Pengendalian | Admin Perencanaan | Verifikator Lapangan | K/L Pelaksana | Viewer Internal |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| `psn.view` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `psn.manage` | ✓ | | | | | |
| `profil.manage` | ✓ | | ✓ | | | |
| `ro.manage` | ✓ | ✓ | | | ✓ | |
| `risiko.manage` | ✓ | ✓ | | | | |
| `regulasi.manage` | ✓ | ✓ | | | | |
| `pengendalian.manage` | ✓ | ✓ | | ✓ | | |
| `pengendalian.approve` | ✓ | ✓ | | | | |
| `perencanaan.manage` | ✓ | | ✓ | ✓ | | |
| `perencanaan.approve` | ✓ | | ✓ | | | |
| `memo.manage` | ✓ | ✓ | | | ✓ | |
| `pengguna.manage` | ✓ | | | | | |
| `laporan.export` | ✓ | ✓ | ✓ | | | |
| `sinkronisasi.manage` | ✓ | | | | | |
| `audit.view` | ✓ | | | | | ✓ |

## Manajemen Pengguna (`/admin/pengguna`, permission `pengguna.manage`)

Halaman admin **Manajemen Pengguna & Hak Akses** (Bagian 5.2 & 6 prompt
pengembangan) menyediakan CRUD `ref_pic` + `hak_akses` + assign role dalam
satu form, tanpa perlu `tinker`:

- **Tambah Pengguna** — mengisi nama, email, password, instansi (opsional),
  role (spatie/laravel-permission — yang benar-benar menentukan permission
  aplikasi), level akses legacy (`Admin`/`Editor`/`Viewer`, kolom
  `hak_akses.level_akses` bawaan skema PSI), dan status aktif.
- **Ubah Pengguna** — field sama, password dikosongkan jika tidak ingin
  diganti.
- **Tidak ada tombol hapus** secara sengaja: mencabut akses seorang
  pengguna dilakukan dengan **menonaktifkan** (uncheck "Akun aktif"), bukan
  menghapus user/PIC — karena banyak tabel lain mereferensikan `pic_id`
  sebagai histori (audit_log, kunjungan lapangan, dsb) yang harus tetap utuh.
  Middleware `akun.aktif` (`app/Http/Middleware/CekHakAksesAktif.php`,
  didaftarkan pada seluruh route `/admin/*`) memeriksa `hak_akses.is_active`
  pada setiap request; jika PIC yang login memiliki hak_akses dan semuanya
  non-aktif, sesi langsung di-logout dan mendapat HTTP 403 "Akun Anda telah
  dinonaktifkan. Hubungi administrator." — coba sendiri dengan akun contoh
  `nonaktif@bappenas.go.id` pada tabel di atas.
- Super Admin **tidak bisa menonaktifkan akunnya sendiri** (guard di
  `PenggunaController::update()`) untuk mencegah terkunci total dari sistem.

Bila butuh cara terprogram (mis. bulk-import user), pola yang sama berlaku
lewat `php artisan tinker`:

```php
$pic = \App\Models\RefPic::create(['nama_pic' => 'Nama Pengguna', 'email' => 'user.baru@bappenas.go.id']);
$user = \App\Models\User::create([
    'name' => 'Nama Pengguna', 'email' => 'user.baru@bappenas.go.id',
    'password' => \Illuminate\Support\Facades\Hash::make('GANTI_PASSWORD_INI'),
    'pic_id' => $pic->id,
]);
$user->assignRole('Admin Pengendalian'); // role sesuai RoleSeeder
\App\Models\HakAkses::create(['pic_id' => $pic->id, 'level_akses' => 'Editor', 'is_active' => true]);
```

Untuk role **K/L Pelaksana**, tautkan `ref_pic.instansi_id` ke `ref_instansi`
milik K/L yang bersangkutan agar `PsnPolicy` dapat membatasi akses ke PSN
miliknya sendiri.

## Mengganti Password Default

```bash
php artisan tinker --execute="
\$u = \App\Models\User::where('email', 'admin@bappenas.go.id')->first();
\$u->update(['password' => \Illuminate\Support\Facades\Hash::make('PASSWORD_BARU_YANG_KUAT')]);
"
```

Atau lewat UI: login lalu buka menu profil (`/profile`) untuk memperbarui
nama/password akun yang sedang login (fitur bawaan Laravel Breeze).
