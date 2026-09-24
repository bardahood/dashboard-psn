<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLogPsi extends Model
{
    use HasFactory;

    protected $table = 'sync_log_psi';

    /**
     * Tabel ini hanya punya kolom created_at (DEFAULT CURRENT_TIMESTAMP di
     * database, lihat migration) -- tidak ada updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'tanggal_sync',
        'jumlah_psn_diterima',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_sync' => 'datetime',
        // $timestamps=false menonaktifkan auto-cast created_at bawaan Eloquent
        // (lihat Model::getDates()), jadi ditambahkan eksplisit di sini.
        'created_at' => 'datetime',
    ];
}
