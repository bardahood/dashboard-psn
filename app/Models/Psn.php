<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Psn extends Model
{
    use HasFactory;

    protected $table = 'psn';

    protected $fillable = [
        'nama_psn',
        'nama_sub_proyek',
        'urgensi',
        'tujuan_utama',
        'tahun_penyelesaian',
        'bulan_penyelesaian',
        'output_akhir',
        'data_teknis',
        'nilai_investasi_apbn_rp',
        'nilai_investasi_non_apbn_rp',
        'indikasi_sumber_pendanaan',
        'asta_cita',
        'diagram_kelembagaan_path',
        'pengusul_instansi_id',
        'pengelola_instansi_id',
        'kontraktor_instansi_id',
        'supervisi_instansi_id',
        'klaster_id',
        'provinsi_id',
        'status_psn_id',
        'kategori_usulan',
        'tipe_hierarki',
        'kabupaten_kota',
        'kode_rkp',
        'peks',
        'unit_kerja',
        'sumber_input',
        'periode_update',
    ];

    protected $casts = [
        'nilai_investasi_apbn_rp' => 'decimal:2',
        'nilai_investasi_non_apbn_rp' => 'decimal:2',
        'periode_update' => 'date',
    ];

    public function klaster(): BelongsTo
    {
        return $this->belongsTo(RefKlaster::class, 'klaster_id');
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(RefProvinsi::class, 'provinsi_id');
    }

    public function statusPsn(): BelongsTo
    {
        return $this->belongsTo(RefStatusPsn::class, 'status_psn_id');
    }

    public function pengusulInstansi(): BelongsTo
    {
        return $this->belongsTo(RefInstansi::class, 'pengusul_instansi_id');
    }

    public function pengelolaInstansi(): BelongsTo
    {
        return $this->belongsTo(RefInstansi::class, 'pengelola_instansi_id');
    }

    public function kontraktorInstansi(): BelongsTo
    {
        return $this->belongsTo(RefInstansi::class, 'kontraktor_instansi_id');
    }

    public function supervisiInstansi(): BelongsTo
    {
        return $this->belongsTo(RefInstansi::class, 'supervisi_instansi_id');
    }

    public function penanggungJawab(): HasMany
    {
        return $this->hasMany(PsnPenanggungJawab::class, 'psn_id');
    }

    public function sumberData(): HasMany
    {
        return $this->hasMany(PsnSumberData::class, 'psn_id');
    }

    public function ketersediaan(): HasMany
    {
        return $this->hasMany(PsnKetersediaan::class, 'psn_id');
    }

    public function infoMemo(): HasMany
    {
        return $this->hasMany(InfoMemo::class, 'psn_id');
    }

    public function catatanMonev(): HasMany
    {
        return $this->hasMany(CatatanMonev::class, 'psn_id');
    }

    public function dasarHukum(): HasMany
    {
        return $this->hasMany(DasarHukumPsn::class, 'psn_id');
    }

    public function stakeholder(): HasMany
    {
        return $this->hasMany(StakeholderPsn::class, 'psn_id');
    }

    public function indikator(): HasMany
    {
        return $this->hasMany(IndikatorPsn::class, 'psn_id');
    }

    public function penerimaManfaat(): HasMany
    {
        return $this->hasMany(PenerimaManfaatPsn::class, 'psn_id');
    }

    public function trisulaKontribusi(): HasMany
    {
        return $this->hasMany(TrisulaKontribusiPsn::class, 'psn_id');
    }

    public function isuLainnya(): HasMany
    {
        return $this->hasMany(PsnIsuLainnya::class, 'psn_id');
    }

    public function evaluasiStatus(): HasMany
    {
        return $this->hasMany(PsnEvaluasiStatus::class, 'psn_id');
    }

    public function roProyek(): HasMany
    {
        return $this->hasMany(RoProyek::class, 'psn_id');
    }

    public function risiko(): HasMany
    {
        return $this->hasMany(RisikoPsn::class, 'psn_id');
    }

    public function kebutuhanRegulasi(): HasMany
    {
        return $this->hasMany(KebutuhanRegulasi::class, 'psn_id');
    }

    public function kunjunganPengendalian(): HasMany
    {
        return $this->hasMany(KunjunganPengendalian::class, 'psn_id');
    }

    public function kunjunganPerencanaan(): HasMany
    {
        return $this->hasMany(KunjunganPerencanaan::class, 'psn_id');
    }

    /**
     * Cek kepatuhan frekuensi pelaporan RO/Proyek milik PSN ini terhadap
     * aturan Project Profile Final: PKPN wajib diisi bulanan, PSN boleh
     * bulanan atau triwulanan (lihat komentar kolom psn.tipe_hierarki dan
     * Bagian 15 ilustrasi Project Profile). Dipakai sebagai saran otomatis
     * pada Bagian A Instrumen Kunjungan Pengendalian -- tetap dapat diubah
     * manual oleh verifikator berdasarkan temuan lapangan.
     */
    public function cekKepatuhanFrekuensiPelaporan(): ?string
    {
        if (! $this->tipe_hierarki) {
            return null;
        }

        $tipePeriodeTahunIni = RoTargetPeriode::whereIn('ro_id', $this->roProyek()->pluck('id'))
            ->where('tahun', now()->year)
            ->pluck('tipe_periode');

        if ($tipePeriodeTahunIni->isEmpty()) {
            return null;
        }

        if ($this->tipe_hierarki === 'PKPN') {
            return $tipePeriodeTahunIni->every(fn ($tipe) => $tipe === 'BULANAN') ? 'Sesuai' : 'Tidak Sesuai';
        }

        return 'Sesuai';
    }
}
