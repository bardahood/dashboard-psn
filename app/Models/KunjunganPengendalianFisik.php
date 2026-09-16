<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganPengendalianFisik extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_pengendalian_fisik';

    protected $fillable = [
        'kunjungan_id',
        'ro_id',
        'target_periode_ini',
        'realisasi_fisik_klaim',
        'realisasi_fisik_verifikasi',
        'persen_capaian',
        'kesesuaian',
        'catatan',
    ];

    protected $casts = [
        'target_periode_ini' => 'decimal:2',
        'realisasi_fisik_klaim' => 'decimal:2',
        'realisasi_fisik_verifikasi' => 'decimal:2',
        'persen_capaian' => 'decimal:2',
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
