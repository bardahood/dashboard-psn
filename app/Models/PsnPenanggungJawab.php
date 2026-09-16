<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsnPenanggungJawab extends Model
{
    use HasFactory;

    protected $table = 'psn_penanggung_jawab';

    protected $fillable = [
        'psn_id',
        'instansi_id',
    ];

    protected $casts = [];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(RefInstansi::class, 'instansi_id');
    }
}
