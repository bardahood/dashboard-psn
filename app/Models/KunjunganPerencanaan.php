<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KunjunganPerencanaan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_perencanaan';

    protected $fillable = [
        'nama_usulan_psn',
        'nomor_kode_usulan',
        'klaster_id',
        'pengusul_instansi_id',
        'jenis_pengusul',
        'provinsi_id',
        'lokasi_detail',
        'indikasi_pendanaan',
        'nilai_proyek_rp',
        'kpu_terkait',
        'hasil_pmo_rpjmn_program_prioritas',
        'hasil_pmo_rpjmn_phtc',
        'hasil_pmo_selesai_2029',
        'status_keputusan_saat_ini',
        'catatan_pembahasan_pmo',
        'fokus_verifikasi_lapangan',
        'verifikator_id',
        'pihak_pengusul_ditemui',
        'narasumber_teknis_lain',
        'dasar_justifikasi',
        'dokumen_masih_diperlukan',
        'batas_waktu_pemenuhan',
        'diverifikasi_oleh_id',
        'mengetahui_id',
        'psn_id',
        'skor_pmo_sementara',
        'tanggal_kunjungan',
        'rekomendasi_keseluruhan',
    ];

    protected $casts = [
        'nilai_proyek_rp' => 'decimal:2',
        'batas_waktu_pemenuhan' => 'date',
        'tanggal_kunjungan' => 'date',
    ];

    public function psn(): BelongsTo
    {
        return $this->belongsTo(Psn::class, 'psn_id');
    }

    public function klaster(): BelongsTo
    {
        return $this->belongsTo(RefKlaster::class, 'klaster_id');
    }

    public function pengusulInstansi(): BelongsTo
    {
        return $this->belongsTo(RefInstansi::class, 'pengusul_instansi_id');
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(RefProvinsi::class, 'provinsi_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'verifikator_id');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'diverifikasi_oleh_id');
    }

    public function mengetahui(): BelongsTo
    {
        return $this->belongsTo(RefPic::class, 'mengetahui_id');
    }

    public function verifikasiKriteria(): HasMany
    {
        return $this->hasMany(KunjunganVerifikasiKriteria::class, 'kunjungan_id');
    }

    public function verifikasiLokasi(): HasMany
    {
        return $this->hasMany(KunjunganVerifikasiLokasi::class, 'kunjungan_id');
    }

    public function verifikasiDokumenTeknis(): HasMany
    {
        return $this->hasMany(KunjunganVerifikasiDokumenTeknis::class, 'kunjungan_id');
    }

    public function verifikasiTrisula(): HasMany
    {
        return $this->hasMany(KunjunganVerifikasiTrisula::class, 'kunjungan_id');
    }

    public function indeksBukti(): HasMany
    {
        return $this->hasMany(KunjunganIndeksBukti::class, 'kunjungan_id');
    }
}
