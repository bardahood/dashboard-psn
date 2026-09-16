<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganPengendalianRegulasi extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_pengendalian_regulasi';

    protected $fillable = [
        'kunjungan_id',
        'regulasi_id',
        'status_klaim',
        'status_temuan_lapangan',
        'bukti_dukung_ditemukan',
        'kesesuaian',
        'catatan',
    ];

    protected $casts = [];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPengendalian::class, 'kunjungan_id');
    }

    public function regulasi(): BelongsTo
    {
        return $this->belongsTo(KebutuhanRegulasi::class, 'regulasi_id');
    }
}
