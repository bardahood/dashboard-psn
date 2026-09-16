<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StakeholderPsn extends Model
{
    use HasFactory;

    protected $table = 'stakeholder_psn';

    protected $fillable = [
        'psn_id',
        'nama_pemangku_kepentingan',
        'kategori_aktor',
        'level_kelembagaan',
        'peran_deskripsi',
    ];

    protected $casts = [];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }
}
