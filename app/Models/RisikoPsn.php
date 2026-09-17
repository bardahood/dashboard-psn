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
        'penanggung_jawab_id',
        'target_mulai',
        'target_selesai',
        'ro_id',
        'is_titik_kritis',
        'tahun_pelaksanaan_perlakuan',
    ];

    protected $casts = [
        'target_mulai' => 'date',
        'target_selesai' => 'date',
        'is_titik_kritis' => 'boolean',
    ];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'penanggung_jawab_id');
    }

    public function ro(): BelongsTo
    {
        return $this->belongsTo(RoProyek::class, 'ro_id');
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
