<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganPengendalianKelembagaan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_pengendalian_kelembagaan';

    protected $fillable = [
        'kunjungan_id',
        'peran',
        'instansi_tercatat_id',
        'instansi_aktual',
        'sesuai',
        'catatan',
    ];

    protected $casts = [];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPengendalian::class, 'kunjungan_id');
    }

    public function instansiTercatat(): BelongsTo
    {
        return $this->belongsTo(RefInstansi::class, 'instansi_tercatat_id');
    }
}
