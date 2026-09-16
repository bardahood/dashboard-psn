<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefStatusKetersediaan extends Model
{
    use HasFactory;

    protected $table = 'ref_status_ketersediaan';

    protected $fillable = [
        'nama_status',
    ];

    protected $casts = [];

    public function psnKetersediaan(): HasMany
    {
        return $this->hasMany(PsnKetersediaan::class, 'status_ketersediaan_id');
    }
}
