<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_log';

    protected $fillable = [
        'nama_tabel',
        'record_id',
        'aksi',
        'pic_id',
        'nilai_lama',
        'nilai_baru',
    ];

    protected $casts = [
        'nilai_lama' => 'array',
        'nilai_baru' => 'array',
    ];

    public function pic(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'pic_id');
    }
}
