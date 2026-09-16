<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaManfaatPsn extends Model
{
    use HasFactory;

    protected $table = 'penerima_manfaat_psn';

    protected $fillable = [
        'psn_id',
        'kategori_penerima',
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
        return $this->hasMany(PenerimaManfaatTargetTahunan::class, 'penerima_manfaat_id');
    }
}
