<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefDampakTrisula extends Model
{
    use HasFactory;

    protected $table = 'ref_dampak_trisula';

    protected $fillable = [
        'nama_dampak',
        'contoh_indikator',
    ];

    protected $casts = [];

    public function kunjunganVerifikasiTrisula(): HasMany
    {
        return $this->hasMany(KunjunganVerifikasiTrisula::class, 'dampak_id');
    }
}
