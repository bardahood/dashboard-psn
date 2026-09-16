<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsnSumberData extends Model
{
    use HasFactory;

    protected $table = 'psn_sumber_data';

    protected $fillable = [
        'psn_id',
        'sumber_data_id',
        'tersedia',
    ];

    protected $casts = [
        'tersedia' => 'boolean',
    ];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function sumberData(): BelongsTo
    {
        return $this->belongsTo(RefSumberData::class, 'sumber_data_id');
    }
}
