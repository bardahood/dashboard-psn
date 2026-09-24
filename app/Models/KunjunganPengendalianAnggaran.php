<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganPengendalianAnggaran extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_pengendalian_anggaran';

    protected $fillable = [
        'kunjungan_id',
        'ro_id',
        'pembiayaan_rencana_juta_rp',
        'realisasi_anggaran_klaim_juta_rp',
        'realisasi_anggaran_verifikasi_juta_rp',
        'persen_realisasi',
        'bukti_dokumen_tersedia',
        'kesesuaian',
        'catatan',
    ];

    protected $casts = [
        'pembiayaan_rencana_juta_rp' => 'decimal:2',
        'realisasi_anggaran_klaim_juta_rp' => 'decimal:2',
        'realisasi_anggaran_verifikasi_juta_rp' => 'decimal:2',
        'persen_realisasi' => 'decimal:2',
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
            $persenRealisasi = $model->hitungPersenRealisasi();
            $model->persen_realisasi = $persenRealisasi === null ? null : (string) $persenRealisasi;
            $model->kesesuaian = $model->hitungKesesuaian();
        });
    }

    /**
     * % realisasi anggaran terverifikasi terhadap pembiayaan rencana.
     */
    public function hitungPersenRealisasi(): ?float
    {
        if (! $this->pembiayaan_rencana_juta_rp || $this->realisasi_anggaran_verifikasi_juta_rp === null) {
            return null;
        }

        return round(((float) $this->realisasi_anggaran_verifikasi_juta_rp / (float) $this->pembiayaan_rencana_juta_rp) * 100, 2);
    }

    /**
     * Kesesuaian klaim vs verifikasi -- formula sama seperti Bagian C (Fisik):
     * toleransi deviasi <=5% = Sesuai, 5-20% = Sebagian, >20% = Tidak Sesuai.
     */
    public function hitungKesesuaian(): ?string
    {
        if (! $this->pembiayaan_rencana_juta_rp || $this->realisasi_anggaran_klaim_juta_rp === null || $this->realisasi_anggaran_verifikasi_juta_rp === null) {
            return null;
        }

        $rencana = (float) $this->pembiayaan_rencana_juta_rp;
        $realisasiKlaim = ((float) $this->realisasi_anggaran_klaim_juta_rp / $rencana) * 100;
        $realisasiVerifikasi = ((float) $this->realisasi_anggaran_verifikasi_juta_rp / $rencana) * 100;
        $deviasi = abs($realisasiKlaim - $realisasiVerifikasi);

        return match (true) {
            $deviasi <= 5 => 'Sesuai',
            $deviasi <= 20 => 'Sebagian',
            default => 'Tidak Sesuai',
        };
    }
}
