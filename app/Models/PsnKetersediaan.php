<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsnKetersediaan extends Model
{
    use HasFactory;

    protected $table = 'psn_ketersediaan';

    protected $fillable = [
        'psn_id',
        'status_ketersediaan_id',
        'keterangan',
        'periode_pemutakhiran',
    ];

    protected $casts = [
        'periode_pemutakhiran' => 'date',
    ];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function statusKetersediaan(): BelongsTo
    {
        return $this->belongsTo(RefStatusKetersediaan::class, 'status_ketersediaan_id');
    }
}
