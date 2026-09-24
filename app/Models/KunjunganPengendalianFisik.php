<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganPengendalianFisik extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_pengendalian_fisik';

    protected $fillable = [
        'kunjungan_id',
        'ro_id',
        'target_periode_ini',
        'realisasi_fisik_klaim',
        'realisasi_fisik_verifikasi',
        'persen_capaian',
        'kesesuaian',
        'catatan',
    ];

    protected $casts = [
        'target_periode_ini' => 'decimal:2',
        'realisasi_fisik_klaim' => 'decimal:2',
        'realisasi_fisik_verifikasi' => 'decimal:2',
        'persen_capaian' => 'decimal:2',
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPengendalian::class, 'kunjungan_id');
    }

    public function ro(): BelongsTo
    {
        return $this->belongsTo(RoProyek::class, 'ro_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $persenCapaian = $model->hitungPersenCapaian();
            $model->persen_capaian = $persenCapaian === null ? null : (string) $persenCapaian;
            $model->kesesuaian = $model->hitungKesesuaian();
        });
    }

    /**
     * % capaian fisik terverifikasi terhadap target periode berjalan.
     */
    public function hitungPersenCapaian(): ?float
    {
        if (! $this->target_periode_ini || $this->realisasi_fisik_verifikasi === null) {
            return null;
        }

        return round(((float) $this->realisasi_fisik_verifikasi / (float) $this->target_periode_ini) * 100, 2);
    }

    /**
     * Kesesuaian klaim Pelaksana vs hasil verifikasi lapangan -- replikasi
     * formula Excel Bagian C: toleransi deviasi <=5% = Sesuai, 5-20% =
     * Sebagian, >20% = Tidak Sesuai. Deviasi dihitung dalam poin persentase
     * capaian (bukan persentase relatif), agar konsisten dengan skala %
     * capaian yang sama-sama dibandingkan terhadap target_periode_ini.
     */
    public function hitungKesesuaian(): ?string
    {
        if (! $this->target_periode_ini || $this->realisasi_fisik_klaim === null || $this->realisasi_fisik_verifikasi === null) {
            return null;
        }

        $target = (float) $this->target_periode_ini;
        $capaianKlaim = ((float) $this->realisasi_fisik_klaim / $target) * 100;
        $capaianVerifikasi = ((float) $this->realisasi_fisik_verifikasi / $target) * 100;
        $deviasi = abs($capaianKlaim - $capaianVerifikasi);

        return match (true) {
            $deviasi <= 5 => 'Sesuai',
            $deviasi <= 20 => 'Sebagian',
            default => 'Tidak Sesuai',
        };
    }
}
