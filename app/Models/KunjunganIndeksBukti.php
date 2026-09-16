<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganIndeksBukti extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_indeks_bukti';

    protected $fillable = [
        'kunjungan_id',
        'id_bukti',
        'kriteria_terkait',
        'nama_dokumen',
        'pemilik_data',
        'lokasi_bukti',
        'simpulan_singkat',
        'status_verifikasi',
        'tindak_lanjut',
    ];

    protected $casts = [];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPerencanaan::class, 'kunjungan_id');
    }
}
