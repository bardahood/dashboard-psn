<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganVerifikasiTrisula extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_verifikasi_trisula';

    protected $fillable = [
        'kunjungan_id',
        'dampak_id',
        'indikator_klaim_dokumen',
        'temuan_lapangan_spotcheck',
        'kondisi_awal_terverifikasi',
        'atribusi_masuk_akal',
        'catatan',
    ];

    protected $casts = [];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPerencanaan::class, 'kunjungan_id');
    }

    public function dampak(): BelongsTo
    {
        return $this->belongsTo(RefDampakTrisula::class, 'dampak_id');
    }
}
