<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefDokumenTeknisSeeder extends Seeder
{
    public function run(): void
    {
        $dokumen = [
            'Studi Kelayakan (FS)/Pra-FS',
            'Master Plan/Rencana Induk Sektor',
            'Basic Design/DED',
            'Dokumen AMDAL/UKL-UPL',
            'Penetapan Lokasi (Penlok)',
            'Inventarisasi Bidang Tanah/LARAP',
            'Surat Pengajuan Resmi (Gubernur/Menteri/Direksi)',
            'Rekomendasi Kemendagri (khusus usulan Pemda)',
            'Risk Register/Manajemen Risiko',
        ];

        $now = now();
        DB::table('ref_dokumen_teknis')->insert(
            collect($dokumen)->values()->map(fn ($nama, $i) => [
                'nama_dokumen' => $nama,
                'urutan' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }
}
