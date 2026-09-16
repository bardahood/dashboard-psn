<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganVerifikasiLokasi extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_verifikasi_lokasi';

    protected $fillable = [
        'kunjungan_id',
        'aspek',
        'klaim_dokumen',
        'temuan_lapangan',
        'sesuai',
        'catatan',
    ];

    protected $casts = [];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPerencanaan::class, 'kunjungan_id');
    }
}
