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

    protected const SKOR_SESUAI = ['Sesuai' => 100, 'Sebagian' => 60, 'Tidak Sesuai' => 20];

    protected const SKOR_YA_TIDAK = ['Ya' => 100, 'Sebagian' => 60, 'Tidak' => 20];

    /**
     * Kriteria Utama (U1-U3) sebagai PENGGUGUR -- jika ada jawaban "Tidak",
     * rekomendasi otomatis = "Ditolak" terlepas skor komponen lain (Bagian 7.2
     * prompt pengembangan).
     */
    public function gateUtamaGagal(): bool
    {
        return $this->verifikasiKriteria()
            ->whereHas('kriteria', fn ($q) => $q->where('kelompok', 'Utama'))
            ->where('nilai_hasil_verifikasi', 'Tidak')
            ->exists();
    }

    protected function rataSkor0Sampai3(string $kelompok): ?float
    {
        $nilai = $this->verifikasiKriteria()
            ->whereHas('kriteria', fn ($q) => $q->where('kelompok', $kelompok))
            ->whereNotNull('nilai_hasil_verifikasi')
            ->pluck('nilai_hasil_verifikasi')
            ->filter(fn ($v) => is_numeric($v))
            ->map(fn ($v) => ((float) $v / 3) * 100);

        return $nilai->isEmpty() ? null : round($nilai->avg(), 1);
    }

    public function skorPendukung(): ?float
    {
        return $this->rataSkor0Sampai3('Pendukung');
    }

    public function skorKesiapan(): ?float
    {
        return $this->rataSkor0Sampai3('Kesiapan');
    }

    public function skorLokasi(): ?float
    {
        $nilai = $this->verifikasiLokasi->pluck('sesuai')->filter()->map(fn ($v) => self::SKOR_SESUAI[$v] ?? null)->filter(fn ($v) => $v !== null);

        return $nilai->isEmpty() ? null : round($nilai->avg(), 1);
    }

    public function skorTrisula(): ?float
    {
        $nilai = $this->verifikasiTrisula->pluck('kondisi_awal_terverifikasi')->filter()->map(fn ($v) => self::SKOR_YA_TIDAK[$v] ?? null)->filter(fn ($v) => $v !== null);

        return $nilai->isEmpty() ? null : round($nilai->avg(), 1);
    }

    /**
     * Skor keseluruhan berbobot: Pendukung 35% + Kesiapan 35% + Lokasi 15% +
     * Trisula 15% (Bagian 7.2 prompt pengembangan). Bobot komponen yang belum
     * ada datanya didistribusikan ulang secara proporsional ke komponen lain
     * yang sudah terisi, agar instrumen bisa dinilai bertahap.
     *
     * Catatan: seperti pada instrumen Pengendalian, bobot & ambang batas ini
     * adalah interpretasi kami atas instruksi "replikasi formula Excel" --
     * dokumen Excel instrumen aslinya tidak turut dilampirkan ke sesi ini.
     */
    public function skorKeseluruhan(): ?float
    {
        $komponen = [
            'pendukung' => [0.35, $this->skorPendukung()],
            'kesiapan' => [0.35, $this->skorKesiapan()],
            'lokasi' => [0.15, $this->skorLokasi()],
            'trisula' => [0.15, $this->skorTrisula()],
        ];

        $tersedia = array_filter($komponen, fn ($k) => $k[1] !== null);

        if (empty($tersedia)) {
            return null;
        }

        $totalBobot = array_sum(array_column($tersedia, 0));
        $totalSkor = array_sum(array_map(fn ($k) => $k[0] * $k[1], $tersedia));

        return round($totalSkor / $totalBobot, 1);
    }

    public function rekomendasiOtomatis(): ?string
    {
        if ($this->gateUtamaGagal()) {
            return 'Ditolak';
        }

        $skor = $this->skorKeseluruhan();

        if ($skor === null) {
            return null;
        }

        return match (true) {
            $skor >= 80 => 'Layak Dilanjutkan',
            $skor >= 65 => 'Layak dengan Catatan',
            $skor >= 50 => 'Perlu Perbaikan Dokumen',
            default => 'Belum Layak',
        };
    }
}
