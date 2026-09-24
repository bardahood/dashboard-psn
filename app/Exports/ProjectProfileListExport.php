<?php

namespace App\Exports;

use App\Models\Psn;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export tombol "Download Xls" pada halaman daftar Project Profile
 * (admin.project-profile.index) -- kolom persis sama dengan tabel yang
 * ditampilkan di layar, memakai koleksi Eloquent yang sudah difilter &
 * di-eager-load oleh controller (bukan query Excel terpisah) supaya hasil
 * unduhan selalu selaras dengan filter yang sedang aktif di halaman.
 */
class ProjectProfileListExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * Nomor urut baris -- properti instance (BUKAN `static` lokal di
     * map()), supaya tidak ikut bertahan lintas instance/proses PHP yang
     * sama bila lebih dari satu export dijalankan berurutan (kelas bug
     * yang sama seperti pernah ditemukan pada LaporanPsnImporter).
     */
    private int $nomor = 0;

    /** @param  Collection<int, Psn>  $daftarPsn */
    public function __construct(private Collection $daftarPsn)
    {
    }

    public function collection(): Collection
    {
        return $this->daftarPsn;
    }

    public function headings(): array
    {
        return [
            'No', 'Kode PSN', 'Nama PSN', 'Sub Proyek', 'Lokasi',
            'Klaster PSN', 'Klaster PKPN', 'Status PSN', 'Pendanaan',
            'Pengusul', 'Penanggung Jawab', 'Pengelola', 'Kontraktor', 'Supervisi',
            'Tahun Selesai',
        ];
    }

    public function map($psn): array
    {
        $this->nomor++;

        return [
            $this->nomor,
            $psn->kode_rkp,
            $psn->nama_psn,
            $psn->nama_sub_proyek,
            $this->lokasi($psn),
            $psn->klaster?->nama_klaster,
            $psn->tipe_hierarki,
            $psn->statusPsn?->nama_status,
            $psn->indikasi_sumber_pendanaan,
            $psn->pengusulInstansi?->nama_instansi,
            $psn->penanggungJawab->pluck('instansi.nama_instansi')->filter()->implode(', '),
            $psn->pengelolaInstansi?->nama_instansi,
            $psn->kontraktorInstansi?->nama_instansi,
            $psn->supervisiInstansi?->nama_instansi,
            $psn->tahun_penyelesaian,
        ];
    }

    private function lokasi(Psn $psn): ?string
    {
        return collect([$psn->provinsi?->nama_provinsi, $psn->kabupaten_kota])->filter()->implode(' - ') ?: null;
    }
}
