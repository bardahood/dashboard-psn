<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrisulaKontribusiPsn extends Model
{
    use HasFactory;

    protected $table = 'trisula_kontribusi_psn';

    protected $fillable = [
        'psn_id',
        'kategori_trisula',
        'sub_kategori_sdm',
        'nama_indikator',
        'satuan',
        'sumber_dana',
        'baseline',
    ];

    protected $casts = [];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function targetPeriode(): HasMany
    {
        return $this->hasMany(TrisulaTargetPeriode::class, 'kontribusi_id');
    }
}
