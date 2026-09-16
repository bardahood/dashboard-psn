<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganPengendalianDokumentasi extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_pengendalian_dokumentasi';

    protected $fillable = [
        'kunjungan_id',
        'nomor',
        'deskripsi',
        'nama_file_tautan',
        'kategori',
    ];

    protected $casts = [];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(KunjunganPengendalian::class, 'kunjungan_id');
    }
}
