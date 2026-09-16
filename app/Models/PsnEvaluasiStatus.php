<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsnEvaluasiStatus extends Model
{
    use HasFactory;

    protected $table = 'psn_evaluasi_status';

    protected $fillable = [
        'psn_id',
        'tahun_evaluasi',
        'masih_butuh_status_psn',
        'justifikasi',
    ];

    protected $casts = [
        'masih_butuh_status_psn' => 'boolean',
    ];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }
}
