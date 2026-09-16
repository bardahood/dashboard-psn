<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganVerifikasiDokumenTeknis extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_verifikasi_dokumen_teknis';

    protected $fillable = [
        'kunjungan_id',
        'dokumen_id',
        'tersedia',
        'tanggal_versi_dokumen',
        'kesesuaian_kondisi_lapangan',
        'catatan',
    ];

    protected $casts = [];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPerencanaan::class, 'kunjungan_id');
    }

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(RefDokumenTeknis::class, 'dokumen_id');
    }
}
