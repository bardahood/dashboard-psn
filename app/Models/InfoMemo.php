<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoMemo extends Model
{
    use HasFactory;

    protected $table = 'info_memo';

    protected $fillable = [
        'psn_id',
        'judul_memo',
        'isi_memo',
        'tanggal_memo',
        'dibuat_oleh_id',
    ];

    protected $casts = [
        'tanggal_memo' => 'date',
    ];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'dibuat_oleh_id');
    }
}
