<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganPengendalianRisiko extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_pengendalian_risiko';

    protected $fillable = [
        'kunjungan_id',
        'risiko_id',
        'progres_pelaksanaan_persen',
        'risiko_residual_aktual',
        'status_perlakuan',
        'evaluasi_risiko',
        'catatan',
    ];

    protected $casts = [
        'progres_pelaksanaan_persen' => 'decimal:2',
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPengendalian::class, 'kunjungan_id');
    }

    public function risiko(): BelongsTo
    {
        return $this->belongsTo(RisikoPsn::class, 'risiko_id');
    }
}
