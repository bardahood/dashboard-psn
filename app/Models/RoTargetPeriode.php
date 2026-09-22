<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoTargetPeriode extends Model
{
    use HasFactory;

    protected $table = 'ro_target_periode';

    protected $fillable = [
        'ro_id',
        'tahun',
        'tipe_periode',
        'triwulan',
        'bulan',
        'target',
        'target_persen',
        'pembiayaan_rencana_juta_rp',
        'realisasi_fisik',
        'realisasi_anggaran_juta_rp',
        'indikasi_sumber_pendanaan',
        'status',
        'permasalahan',
        'kebutuhan_dukungan',
        'keterangan',
        'bukti_pelaporan_path',
    ];

    protected $casts = [
        'target' => 'decimal:2',
        'target_persen' => 'decimal:2',
        'pembiayaan_rencana_juta_rp' => 'decimal:2',
        'realisasi_fisik' => 'decimal:2',
        'realisasi_anggaran_juta_rp' => 'decimal:2',
    ];

    public function ro(): BelongsTo
    {
        return $this->belongsTo(RoProyek::class, 'ro_id');
    }
}
