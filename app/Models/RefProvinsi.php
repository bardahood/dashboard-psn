<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefProvinsi extends Model
{
    use HasFactory;

    protected $table = 'ref_provinsi';

    protected $fillable = [
        'nama_provinsi',
    ];

    protected $casts = [];

    public function psn(): HasMany
    {
        return $this->hasMany(Psn::class, 'provinsi_id');
    }

    public function kunjunganPerencanaan(): HasMany
    {
        return $this->hasMany(KunjunganPerencanaan::class, 'provinsi_id');
    }
}
