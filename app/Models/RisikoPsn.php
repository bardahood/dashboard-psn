<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RisikoPsn extends Model
{
    use HasFactory;

    protected $table = 'risiko_psn';

    protected $fillable = [
        'psn_id',
        'peristiwa_risiko',
        'kategori_risiko',
        'level_risiko_awal',
        'perlakuan_rencana',
        'risiko_residual_harapan',
    ];

    protected $casts = [];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function statusPeriode(): HasMany
    {
        return $this->hasMany(RisikoStatusPeriode::class, 'risiko_id');
    }

    public function kunjunganPengendalianRisiko(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianRisiko::class, 'risiko_id');
    }
}
