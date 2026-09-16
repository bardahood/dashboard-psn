<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganVerifikasiKriteria extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_verifikasi_kriteria';

    protected $fillable = [
        'kunjungan_id',
        'kriteria_id',
        'nilai_desk_review_pmo',
        'klaim_atau_temuan_dokumen',
        'temuan_lapangan',
        'nilai_hasil_verifikasi',
        'catatan',
    ];

    protected $casts = [];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPerencanaan::class, 'kunjungan_id');
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(RefKriteriaPerencanaan::class, 'kriteria_id');
    }
}
