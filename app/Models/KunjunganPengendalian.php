<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KunjunganPengendalian extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_pengendalian';

    protected $fillable = [
        'tipe_hierarki',
        'status_psn_tercatat',
        'lokasi_kunjungan',
        'tanggal_kunjungan',
        'verifikator_id',
        'tim_verifikator_tambahan',
        'kepatuhan_frekuensi_pelaporan',
        'isu_tantangan',
        'kebutuhan_tindak_lanjut',
        'status_pengendalian',
        'rekomendasi_kelanjutan_status',
        'mengetahui_id',
        'tanggal_pengesahan',
        'psn_id',
        'hasil_evaluasi_proyek',
        'kesimpulan_umum',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
        'tanggal_pengesahan' => 'date',
    ];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'verifikator_id');
    }

    public function mengetahui(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'mengetahui_id');
    }

    public function kelembagaan(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianKelembagaan::class, 'kunjungan_id');
    }

    public function fisik(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianFisik::class, 'kunjungan_id');
    }

    public function anggaran(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianAnggaran::class, 'kunjungan_id');
    }

    public function risiko(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianRisiko::class, 'kunjungan_id');
    }

    public function regulasi(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianRegulasi::class, 'kunjungan_id');
    }

    public function dokumentasi(): HasMany
    {
        return $this->hasMany(KunjunganPengendalianDokumentasi::class, 'kunjungan_id');
    }
}
