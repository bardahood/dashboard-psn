<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaManfaatTargetTahunan extends Model
{
    use HasFactory;

    protected $table = 'penerima_manfaat_target_tahunan';

    protected $fillable = [
        'penerima_manfaat_id',
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

    public function penerimaManfaat(): BelongsTo
    {
        return $this->belongsTo(PenerimaManfaatPsn::class, 'penerima_manfaat_id');
    }
}
