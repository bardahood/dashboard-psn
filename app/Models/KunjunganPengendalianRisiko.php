<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganPengendalianRisiko extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_pengendalian_risiko';

    protected $fillable = [
        'kunjungan_id',
        'risiko_id',
        'progres_pelaksanaan_persen',
        'risiko_residual_aktual',
        'status_perlakuan',
        'evaluasi_risiko',
        'catatan',
    ];

    protected $casts = [
        'progres_pelaksanaan_persen' => 'decimal:2',
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPengendalian::class, 'kunjungan_id');
    }

    public function risiko(): BelongsTo
    {
        return $this->belongsTo(RisikoPsn::class, 'risiko_id');
    }

    public const LEVEL_RANK = ['Rendah' => 1, 'Sedang' => 2, 'Tinggi' => 3, 'Sangat Tinggi' => 4];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->evaluasi_risiko = $model->hitungEvaluasiRisiko();
        });
    }

    /**
     * Bandingkan risiko residual Harapan (risiko_psn.risiko_residual_harapan,
     * ditetapkan saat perencanaan) vs Aktual (temuan verifikasi lapangan).
     * Level risiko lebih rendah atau sama dengan harapan = "Sesuai/Lebih Baik",
     * lebih tinggi dari harapan = "Memburuk".
     */
    public function hitungEvaluasiRisiko(): ?string
    {
        $harapan = $this->relationLoaded('risiko') ? $this->risiko?->risiko_residual_harapan : $this->risiko()->value('risiko_residual_harapan');

        if (! $harapan || ! $this->risiko_residual_aktual) {
            return null;
        }

        $rankHarapan = self::LEVEL_RANK[$harapan] ?? null;
        $rankAktual = self::LEVEL_RANK[$this->risiko_residual_aktual] ?? null;

        if ($rankHarapan === null || $rankAktual === null) {
            return null;
        }

        return $rankAktual <= $rankHarapan ? 'Sesuai/Lebih Baik' : 'Memburuk';
    }
}
