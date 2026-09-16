<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatatanMonev extends Model
{
    use HasFactory;

    protected $table = 'catatan_monev';

    protected $fillable = [
        'psn_id',
        'catatan',
        'kategori',
        'tanggal_catatan',
        'dicatat_oleh_id',
    ];

    protected $casts = [
        'tanggal_catatan' => 'date',
    ];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'dicatat_oleh_id');
    }
}
