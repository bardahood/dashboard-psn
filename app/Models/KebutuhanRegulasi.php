<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KebutuhanRegulasi extends Model
{
    use HasFactory;

    protected $table = 'kebutuhan_regulasi';

    protected $fillable = [
        'psn_id',
        'nama_regulasi',
        'justifikasi_kebutuhan',
        'target_tahun_penyelesaian',
        'penanggung_jawab_id',
    ];

    protected $casts = [];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'penanggung_jawab_id');
    }

    public function kunjunganPengendalianRegulasi(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianRegulasi::class, 'regulasi_id');
    }
}
