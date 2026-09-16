<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HakAkses extends Model
{
    use HasFactory;

    protected $table = 'hak_akses';

    protected $fillable = [
        'pic_id',
        'instansi_id',
        'level_akses',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pic(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'pic_id');
    }

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(RefInstansi::class, 'instansi_id');
    }
}
