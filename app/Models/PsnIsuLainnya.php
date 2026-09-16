<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsnIsuLainnya extends Model
{
    use HasFactory;

    protected $table = 'psn_isu_lainnya';

    protected $fillable = [
        'psn_id',
        'nomor',
        'deskripsi_isu',
        'kebutuhan_dukungan',
    ];

    protected $casts = [];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }
}
