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

    protected const SKOR_KESESUAIAN = ['Sesuai' => 100, 'Sebagian' => 60, 'Tidak Sesuai' => 20];

    protected const SKOR_EVALUASI_RISIKO = ['Sesuai/Lebih Baik' => 100, 'Memburuk' => 30];

    protected const SKOR_KEPATUHAN = ['Sesuai' => 100, 'Tidak Sesuai' => 40];

    /**
     * Skor keseluruhan hasil kunjungan Pengendalian (0-100) -- rata-rata dari:
     * kepatuhan frekuensi pelaporan, kesesuaian capaian fisik per RO,
     * kesesuaian realisasi anggaran per RO, dan evaluasi risiko residual.
     * Komponen yang datanya belum diisi tidak ikut dihitung (bukan dianggap 0),
     * karena instrumen ini bisa diisi bertahap per bagian.
     *
     * Catatan: bobot & ambang batas berikut adalah interpretasi kami atas
     * instruksi "replikasi formula Excel" pada prompt pengembangan (dokumen
     * Excel instrumen aslinya tidak turut dilampirkan) -- tim konsultan dapat
     * menyesuaikan bobot ini bila berbeda dari formula baku yang dimaksud.
     */
    public function skorKeseluruhan(): ?float
    {
        $skorKomponen = [];

        if ($this->kepatuhan_frekuensi_pelaporan) {
            $skorKomponen[] = self::SKOR_KEPATUHAN[$this->kepatuhan_frekuensi_pelaporan] ?? null;
        }

        foreach ($this->fisik as $f) {
            if ($f->kesesuaian) {
                $skorKomponen[] = self::SKOR_KESESUAIAN[$f->kesesuaian] ?? null;
            }
        }

        foreach ($this->anggaran as $a) {
            if ($a->kesesuaian) {
                $skorKomponen[] = self::SKOR_KESESUAIAN[$a->kesesuaian] ?? null;
            }
        }

        foreach ($this->risiko as $r) {
            if ($r->evaluasi_risiko) {
                $skorKomponen[] = self::SKOR_EVALUASI_RISIKO[$r->evaluasi_risiko] ?? null;
            }
        }

        $skorKomponen = array_filter($skorKomponen, fn ($v) => $v !== null);

        return count($skorKomponen) > 0 ? round(array_sum($skorKomponen) / count($skorKomponen), 1) : null;
    }

    /**
     * Rekomendasi status pengendalian otomatis berdasarkan skor keseluruhan --
     * disarankan sebagai draf awal, tetap dapat diubah manual oleh Tim
     * Pengendalian pada Bagian I (Kesimpulan & Pengesahan).
     */
    public function rekomendasiOtomatis(): ?string
    {
        $skor = $this->skorKeseluruhan();

        if ($skor === null) {
            return null;
        }

        return match (true) {
            $skor >= 80 => 'Aktif Dikendalikan Sesuai Rencana',
            $skor >= 50 => 'Perlu Perhatian',
            default => 'Kritis/Perlu Eskalasi',
        };
    }
}
