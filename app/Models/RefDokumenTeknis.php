<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefDokumenTeknis extends Model
{
    use HasFactory;

    protected $table = 'ref_dokumen_teknis';

    protected $fillable = [
        'nama_dokumen',
        'urutan',
    ];

    protected $casts = [];

    public function kunjunganVerifikasiDokumenTeknis(): HasMany
    {
        return $this->hasMany(KunjunganVerifikasiDokumenTeknis::class, 'dokumen_id');
    }
}
