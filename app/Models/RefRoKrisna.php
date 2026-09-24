<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefRoKrisna extends Model
{
    use HasFactory;

    protected $table = 'ref_ro_krisna';

    protected $fillable = [
        'sektor_psn',
        'project_psn',
        'project_rkp',
        'kementerian',
        'program',
        'kegiatan',
        'kro',
        'ro',
        'lokasi_ro',
        'volume',
        'satuan',
        'alokasi',
        'pn',
        'pp',
        'kp',
        'prop',
        'psn_id',
        'prop_kode_rkp',
        'nama_prop_rkp',
        'alokasi_prop',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
        'alokasi' => 'decimal:2',
        'alokasi_prop' => 'decimal:2',
    ];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }
}
