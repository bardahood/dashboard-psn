<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefInstansi extends Model
{
    use HasFactory;

    protected $table = 'ref_instansi';

    protected $fillable = [
        'nama_instansi',
    ];

    protected $casts = [];

    public function pic(): HasMany
    {
        return $this->hasMany(RefPic::class, 'instansi_id');
    }

    public function psn(): HasMany
    {
        return $this->hasMany(Psn::class, 'pengusul_instansi_id');
    }

    public function psnPenanggungJawab(): HasMany
    {
        return $this->hasMany(PsnPenanggungJawab::class, 'instansi_id');
    }

    public function hakAkses(): HasMany
    {
        return $this->hasMany(HakAkses::class, 'instansi_id');
    }

    public function roProyek(): HasMany
    {
        return $this->hasMany(RoProyek::class, 'instansi_pelaksana_id');
    }

    public function kunjunganPengendalianKelembagaan(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianKelembagaan::class, 'instansi_tercatat_id');
    }

    public function kunjunganPerencanaan(): HasMany
    {
        return $this->hasMany(KunjunganPerencanaan::class, 'pengusul_instansi_id');
    }
}
