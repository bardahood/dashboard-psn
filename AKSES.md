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

Catatan: `pengguna.manage` sudah terdaftar sebagai permission (dimiliki
Super Admin) tetapi **belum ada halaman admin untuk manajemen user**
(tambah/nonaktifkan user, ubah role) pada versi ini — masih di luar scope
Tier 1/2 prompt pengembangan. Untuk sementara, penambahan user baru atau
perubahan role dilakukan lewat `php artisan tinker` (lihat bawah) atau
menambah entri pada `DatabaseSeeder::AKUN_DEMO` lalu `migrate:fresh --seed`
ulang di lingkungan non-produksi.

## Menambah/Mengubah User Secara Manual

Contoh membuat user baru dan menetapkan role lewat `php artisan tinker`:

```php
$pic = \App\Models\RefPic::create([
    'nama_pic' => 'Nama Pengguna',
    'email' => 'user.baru@bappenas.go.id',
]);

$user = \App\Models\User::create([
    'name' => 'Nama Pengguna',
    'email' => 'user.baru@bappenas.go.id',
    'password' => \Illuminate\Support\Facades\Hash::make('GANTI_PASSWORD_INI'),
    'pic_id' => $pic->id,
]);

$user->assignRole('Admin Pengendalian'); // atau role lain sesuai RoleSeeder
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
