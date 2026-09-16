<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefSumberData extends Model
{
    use HasFactory;

    protected $table = 'ref_sumber_data';

    protected $fillable = [
        'nama_sumber',
        'deskripsi',
    ];

    protected $casts = [];

    public function psnSumberData(): HasMany
    {
        return $this->hasMany(PsnSumberData::class, 'sumber_data_id');
    }
}
