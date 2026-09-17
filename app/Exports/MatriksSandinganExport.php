<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export Matriks Sandingan Sumber (v_psn_sandingan_sumber) ke Excel --
 * selaras kebiasaan pengguna existing yang berbasis Excel (Bagian 3 prompt).
 */
class MatriksSandinganExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function query()
    {
        return DB::table('v_psn_sandingan_sumber')->orderBy('nama_psn');
    }

    public function headings(): array
    {
        return [
            'Nama PSN', 'Klaster', 'Provinsi',
            'RKP Pemutakhiran 2026', 'Data PEKS3', 'Data PSI', 'Permenko',
        ];
    }

    protected function tanda($nilai): string
    {
        return match ($nilai) {
            1, '1' => 'V',
            0, '0' => 'X',
            default => '-',
        };
    }

    public function map($row): array
    {
        return [
            $row->nama_psn,
            $row->nama_klaster,
            $row->nama_provinsi,
            $this->tanda($row->rkp_pemutakhiran_2026),
            $this->tanda($row->data_peks3),
            $this->tanda($row->data_psi),
            $this->tanda($row->permenko),
        ];
    }
}
