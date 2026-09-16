<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RisikoStatusPeriode extends Model
{
    use HasFactory;

    protected $table = 'risiko_status_periode';

    protected $fillable = [
        'risiko_id',
        'tahun',
        'triwulan',
        'progres_pelaksanaan_persen',
        'risiko_residual_aktual',
        'status_perlakuan',
        'bukti_dukung',
        'catatan',
    ];

    protected $casts = [
        'progres_pelaksanaan_persen' => 'decimal:2',
    ];

    public function risiko(): BelongsTo
    {
        return $this->belongsTo(RisikoPsn::class, 'risiko_id');
    }
}
