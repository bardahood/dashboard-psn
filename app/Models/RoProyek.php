<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoProyek extends Model
{
    use HasFactory;

    protected $table = 'ro_proyek';

    protected $fillable = [
        'psn_id',
        'ro_induk_id',
        'nama_ro',
        'tipe',
        'is_ro_kunci',
        'satuan',
        'baseline',
        'baseline_tahun',
        'target_akhir',
        'lokasi',
        'instansi_pelaksana_id',
    ];

    protected $casts = [
        'is_ro_kunci' => 'boolean',
    ];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function roInduk(): BelongsTo
    {
        return $this->belongsTo(RoProyek::class, 'ro_induk_id');
    }

    public function instansiPelaksana(): BelongsTo
    {
        return $this->belongsTo(RefInstansi::class, 'instansi_pelaksana_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(RoProyek::class, 'ro_induk_id');
    }

    public function targetPeriode(): HasMany
    {
        return $this->hasMany(RoTargetPeriode::class, 'ro_id');
    }

    public function kunjunganPengendalianFisik(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianFisik::class, 'ro_id');
    }

    public function kunjunganPengendalianAnggaran(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianAnggaran::class, 'ro_id');
    }
}
