<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefKlaster extends Model
{
    use HasFactory;

    protected $table = 'ref_klaster';

    protected $fillable = [
        'nama_klaster',
    ];

    protected $casts = [];

    public function psn(): HasMany
    {
        return $this->hasMany(Psn::class, 'klaster_id');
    }

    public function kunjunganPerencanaan(): HasMany
    {
        return $this->hasMany(KunjunganPerencanaan::class, 'klaster_id');
    }
}
