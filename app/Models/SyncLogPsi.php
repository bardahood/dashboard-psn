<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLogPsi extends Model
{
    use HasFactory;

    protected $table = 'sync_log_psi';

    protected $fillable = [
        'tanggal_sync',
        'jumlah_psn_diterima',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_sync' => 'datetime',
    ];
}
