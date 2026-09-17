<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer generik untuk jejak audit tabel inti (Bagian 8 prompt pengembangan:
 * "setiap perubahan pada tabel inti tercatat ke audit_log, gunakan Model
 * Observer, bukan manual di tiap Controller"). Didaftarkan per model di
 * AppServiceProvider::boot() untuk entitas inti: Psn, RoProyek, RisikoPsn,
 * KebutuhanRegulasi, KunjunganPengendalian, KunjunganPerencanaan.
 */
class AuditLogObserver
{
    public function created(Model $model): void
    {
        $this->catat($model, 'insert', null, $this->bersihkan($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $perubahan = $model->getChanges();
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
        return array_diff_key($attributes, array_flip(['id', 'created_at', 'updated_at']));
    }

    protected function catat(Model $model, string $aksi, ?array $nilaiLama, ?array $nilaiBaru): void
    {
        AuditLog::create([
            'nama_tabel' => $model->getTable(),
            'record_id' => $model->getKey(),
            'aksi' => $aksi,
            'pic_id' => auth()->user()?->pic_id,
            'nilai_lama' => $nilaiLama,
            'nilai_baru' => $nilaiBaru,
        ]);
    }
}
