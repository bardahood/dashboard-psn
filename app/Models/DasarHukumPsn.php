<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DasarHukumPsn extends Model
{
    use HasFactory;

    protected $table = 'dasar_hukum_psn';

    protected $fillable = [
        'psn_id',
        'nama_regulasi',
        'nomor_regulasi',
        'tahun',
        'keterangan',
    ];

    protected $casts = [];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }
}
