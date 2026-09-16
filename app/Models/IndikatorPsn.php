<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndikatorPsn extends Model
{
    use HasFactory;

    protected $table = 'indikator_psn';

    protected $fillable = [
        'psn_id',
        'nama_indikator',
        'satuan',
        'baseline',
    ];

    protected $casts = [];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function targetTahunan(): HasMany
    {
        return $this->hasMany(IndikatorPsnTargetTahunan::class, 'indikator_id');
    }
}
