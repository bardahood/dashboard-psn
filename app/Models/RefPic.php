<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefPic extends Model
{
    use HasFactory;

    protected $table = 'ref_pic';

    protected $fillable = [
        'nama_pic',
        'instansi_id',
        'email',
    ];

    protected $casts = [];

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(RefInstansi::class, 'instansi_id');
    }

    public function infoMemo(): HasMany
    {
        return $this->hasMany(InfoMemo::class, 'dibuat_oleh_id');
    }

    public function catatanMonev(): HasMany
    {
        return $this->hasMany(CatatanMonev::class, 'dicatat_oleh_id');
    }

    public function hakAkses(): HasMany
    {
        return $this->hasMany(HakAkses::class, 'pic_id');
    }

    public function auditLog(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'pic_id');
    }

    public function kebutuhanRegulasi(): HasMany
    {
        return $this->hasMany(KebutuhanRegulasi::class, 'penanggung_jawab_id');
    }

    public function kunjunganPengendalian(): HasMany
    {
        return $this->hasMany(KunjunganPengendalian::class, 'verifikator_id');
    }

    public function kunjunganPerencanaan(): HasMany
    {
        return $this->hasMany(KunjunganPerencanaan::class, 'verifikator_id');
    }
}
