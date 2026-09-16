<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrisulaTargetPeriode extends Model
{
    use HasFactory;

    protected $table = 'trisula_target_periode';

    protected $fillable = [
        'kontribusi_id',
        'tahun',
        'tipe_periode',
        'triwulan',
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

    public function kontribusi(): BelongsTo
    {
        return $this->belongsTo(TrisulaKontribusiPsn::class, 'kontribusi_id');
    }
}
