<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tambahkan kolom "RKP 2027" ke v_psn_sandingan_sumber -- hasil analisis
 * carryover (App\Support\Rkp2027CarryoverAnalyzer) menuliskan satu baris
 * psn_sumber_data per PSN existing untuk sumber "RKP 2027" (tersedia=true
 * bila cocok/carryover, false bila tidak ditemukan lagi di lampiran RKP
 * 2027), mengikuti pola pivot 4 sumber lain yang sudah ada di view ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE OR REPLACE VIEW `v_psn_sandingan_sumber` AS\nSELECT
  p.id AS psn_id,
  p.nama_psn,
  k.nama_klaster,
  pr.nama_provinsi,
  MAX(CASE WHEN sd.nama_sumber = 'RKP Pemutakhiran 2026 (Perpres 68)' THEN psd.tersedia END) AS rkp_pemutakhiran_2026,
  MAX(CASE WHEN sd.nama_sumber = 'Data PEKS3' THEN psd.tersedia END) AS data_peks3,
  MAX(CASE WHEN sd.nama_sumber = 'Data PSI' THEN psd.tersedia END) AS data_psi,
  MAX(CASE WHEN sd.nama_sumber = 'Permenko' THEN psd.tersedia END) AS permenko,
  MAX(CASE WHEN sd.nama_sumber = 'RKP 2027' THEN psd.tersedia END) AS rkp_2027
FROM `psn` p
LEFT JOIN `ref_klaster` k ON k.id = p.klaster_id
LEFT JOIN `ref_provinsi` pr ON pr.id = p.provinsi_id
LEFT JOIN `psn_sumber_data` psd ON psd.psn_id = p.id
LEFT JOIN `ref_sumber_data` sd ON sd.id = psd.sumber_data_id
GROUP BY p.id, p.nama_psn, k.nama_klaster, pr.nama_provinsi");
    }

    public function down(): void
    {
        DB::statement("CREATE OR REPLACE VIEW `v_psn_sandingan_sumber` AS\nSELECT
  p.id AS psn_id,
  p.nama_psn,
  k.nama_klaster,
  pr.nama_provinsi,
  MAX(CASE WHEN sd.nama_sumber = 'RKP Pemutakhiran 2026 (Perpres 68)' THEN psd.tersedia END) AS rkp_pemutakhiran_2026,
  MAX(CASE WHEN sd.nama_sumber = 'Data PEKS3' THEN psd.tersedia END) AS data_peks3,
  MAX(CASE WHEN sd.nama_sumber = 'Data PSI' THEN psd.tersedia END) AS data_psi,
  MAX(CASE WHEN sd.nama_sumber = 'Permenko' THEN psd.tersedia END) AS permenko
FROM `psn` p
LEFT JOIN `ref_klaster` k ON k.id = p.klaster_id
LEFT JOIN `ref_provinsi` pr ON pr.id = p.provinsi_id
LEFT JOIN `psn_sumber_data` psd ON psd.psn_id = p.id
LEFT JOIN `ref_sumber_data` sd ON sd.id = psd.sumber_data_id
GROUP BY p.id, p.nama_psn, k.nama_klaster, pr.nama_provinsi");
    }
};
