<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndikatorPsnTargetTahunan extends Model
{
    use HasFactory;

    protected $table = 'indikator_psn_target_tahunan';

    protected $fillable = [
        'indikator_id',
        'tahun',
        'target_akhir',
        'target',
        'realisasi',
        'persen_realisasi',
        'persen_terhadap_target_akhir',
        'status_capaian',
    ];

    protected $casts = [
        'target' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'persen_realisasi' => 'decimal:2',
        'persen_terhadap_target_akhir' => 'decimal:2',
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(IndikatorPsn::class, 'indikator_id');
    }
}
