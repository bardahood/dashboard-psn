<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefKriteriaPerencanaan extends Model
{
    use HasFactory;

    protected $table = 'ref_kriteria_perencanaan';

    protected $fillable = [
        'kelompok',
        'kode_kriteria',
        'kode_sub',
        'judul_kriteria',
        'sub_butir',
        'rubrik_penilaian',
        'tipe_penilaian',
        'kondisional',
        'syarat_kondisional',
        'urutan',
    ];

    protected $casts = [
        'kondisional' => 'boolean',
    ];

    public function kunjunganVerifikasiKriteria(): HasMany
    {
        return $this->hasMany(KunjunganVerifikasiKriteria::class, 'kriteria_id');
    }
}
