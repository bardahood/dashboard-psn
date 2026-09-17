<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_log';

    /**
     * Tabel ini hanya punya kolom created_at (DEFAULT CURRENT_TIMESTAMP di
     * database, lihat migration) -- tidak ada updated_at.
     */
    public $timestamps = false;

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
        // $timestamps=false menonaktifkan auto-cast created_at bawaan Eloquent
        // (lihat Model::getDates()), jadi ditambahkan eksplisit di sini.
        'created_at' => 'datetime',
    ];

    public function pic(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'pic_id');
    }
}
