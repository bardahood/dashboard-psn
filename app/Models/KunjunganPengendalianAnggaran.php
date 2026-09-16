<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganPengendalianAnggaran extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_pengendalian_anggaran';

    protected $fillable = [
        'kunjungan_id',
        'ro_id',
        'pembiayaan_rencana_juta_rp',
        'realisasi_anggaran_klaim_juta_rp',
        'realisasi_anggaran_verifikasi_juta_rp',
        'persen_realisasi',
        'bukti_dokumen_tersedia',
        'kesesuaian',
        'catatan',
    ];

    protected $casts = [
        'pembiayaan_rencana_juta_rp' => 'decimal:2',
        'realisasi_anggaran_klaim_juta_rp' => 'decimal:2',
        'realisasi_anggaran_verifikasi_juta_rp' => 'decimal:2',
        'persen_realisasi' => 'decimal:2',
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPengendalian::class, 'kunjungan_id');
    }

    public function ro(): BelongsTo
    {
        return $this->belongsTo(RoProyek::class, 'ro_id');
    }
}
