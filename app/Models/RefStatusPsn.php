<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefStatusPsn extends Model
{
    use HasFactory;

    protected $table = 'ref_status_psn';

    protected $fillable = [
        'nama_status',
        'urutan',
    ];

    protected $casts = [];

    public function psn(): HasMany
    {
        return $this->hasMany(Psn::class, 'status_psn_id');
    }
}
