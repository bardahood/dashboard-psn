<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer generik untuk jejak audit tabel inti (Bagian 8 prompt pengembangan:
 * "setiap perubahan pada tabel inti tercatat ke audit_log, gunakan Model
 * Observer, bukan manual di tiap Controller"). Didaftarkan per model di
 * AppServiceProvider::boot() untuk seluruh entitas yang ditulis lewat
 * Controller/Livewire admin (lihat daftar lengkap di sana) -- mencakup PSN
 * beserta seluruh sub-resource-nya, Kunjungan Pengendalian/Perencanaan
 * beserta bagian-bagiannya, dan Pengguna/Hak Akses, agar aksi CRUD siapa pun
 * (apa pun perannya) tercatat di satu tabel yang sama.
 */
class AuditLogObserver
{
    /**
     * Kolom yang TIDAK PERNAH ditulis ke audit_log walau ikut berubah --
     * mencegah hash password (User) bocor ke jejak audit yang bisa dibaca
     * siapa pun dengan izin audit.view.
     */
    protected const KOLOM_SENSITIF = ['password', 'remember_token'];

    public function created(Model $model): void
    {
        $this->catat($model, 'insert', null, $this->bersihkan($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $perubahan = $this->bersihkan($model->getChanges());
        unset($perubahan['updated_at']);

        if (empty($perubahan)) {
            return;
        }

        $nilaiLama = array_intersect_key($model->getOriginal(), $perubahan);

        $this->catat($model, 'update', $nilaiLama, $perubahan);
    }

    public function deleted(Model $model): void
    {
        $this->catat($model, 'delete', $this->bersihkan($model->getAttributes()), null);
    }

    protected function bersihkan(array $attributes): array
    {
        return array_diff_key($attributes, array_flip(array_merge(['id', 'created_at', 'updated_at'], self::KOLOM_SENSITIF)));
    }

    protected function catat(Model $model, string $aksi, ?array $nilaiLama, ?array $nilaiBaru): void
    {
        $user = auth()->user();

        AuditLog::create([
            'nama_tabel' => $model->getTable(),
            'record_id' => $model->getKey(),
            'aksi' => $aksi,
            'pic_id' => $user?->pic_id,
            'user_id' => $user?->id,
            // Snapshot nama peran SAAT aksi terjadi (bukan FK ke tabel roles) --
            // peran user bisa berubah/dicabut kemudian, jejak audit harus tetap
            // mencerminkan peran yang berlaku ketika aksi dilakukan.
            'role' => $user?->getRoleNames()->implode(', ') ?: null,
            'nilai_lama' => $nilaiLama,
            'nilai_baru' => $nilaiBaru,
        ]);
    }
}
