<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export profil lengkap seluruh PSN (v_psn_profil_lengkap) ke Excel --
 * bahan pendukung Laporan Presiden/Semester.
 */
class DaftarPsnExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function query()
    {
        return DB::table('v_psn_profil_lengkap')->orderBy('nama_psn');
    }

    public function headings(): array
    {
        return [
            'Nama PSN', 'Klaster', 'Status PSN', 'Tipe Hierarki', 'Provinsi', 'Kabupaten/Kota',
            'Tahun Penyelesaian', 'Output Akhir', 'Nilai Investasi APBN (Rp)', 'Nilai Investasi Non-APBN (Rp)',
            'Pengusul', 'Pengelola', 'Kontraktor', 'Supervisi',
        ];
    }

    public function map($row): array
    {
        return [
            $row->nama_psn,
            $row->nama_klaster,
            $row->status_psn,
            $row->tipe_hierarki,
            $row->nama_provinsi,
            $row->kabupaten_kota,
            $row->tahun_penyelesaian,
            $row->output_akhir,
            $row->nilai_investasi_apbn_rp,
            $row->nilai_investasi_non_apbn_rp,
            $row->pengusul,
            $row->pengelola,
            $row->kontraktor,
            $row->supervisi,
        ];
    }
}
