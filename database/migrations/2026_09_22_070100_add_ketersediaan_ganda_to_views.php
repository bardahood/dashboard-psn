<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * v_psn_ketersediaan_terkini semula mengambil 1 baris "terkini" per PSN --
 * dengan psn_ketersediaan sekarang menyimpan 2 baris per PSN per periode
 * (jenis_ketersediaan: Gambaran Umum vs Project Profile Lengkap), view ini
 * dipivot ulang jadi 2 kolom terpisah, mengikuti pola MAX(CASE) yang sama
 * dipakai v_psn_sandingan_sumber. Kedua dimensi ini juga ditambahkan sebagai
 * kolom baru pada v_psn_sandingan_sumber supaya langsung tampil berdampingan
 * dengan 5 sumber data yang sudah ada di halaman Matriks Sandingan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE OR REPLACE VIEW `v_psn_ketersediaan_terkini` AS\nSELECT
  p.id AS psn_id,
  p.nama_psn,
  MAX(CASE WHEN pk.jenis_ketersediaan = 'Gambaran Umum' THEN sk.nama_status END) AS gambaran_umum,
  MAX(CASE WHEN pk.jenis_ketersediaan = 'Project Profile Lengkap' THEN sk.nama_status END) AS project_profile_lengkap,
  MAX(pk.keterangan) AS keterangan,
  MAX(pk.periode_pemutakhiran) AS periode_pemutakhiran
FROM `psn` p
LEFT JOIN `psn_ketersediaan` pk ON pk.psn_id = p.id
LEFT JOIN `ref_status_ketersediaan` sk ON sk.id = pk.status_ketersediaan_id
GROUP BY p.id, p.nama_psn");

        DB::statement("CREATE OR REPLACE VIEW `v_psn_sandingan_sumber` AS\nSELECT
  p.id AS psn_id,
  p.nama_psn,
  k.nama_klaster,
  pr.nama_provinsi,
  MAX(CASE WHEN sd.nama_sumber = 'RKP Pemutakhiran 2026 (Perpres 68)' THEN psd.tersedia END) AS rkp_pemutakhiran_2026,
  MAX(CASE WHEN sd.nama_sumber = 'Data PEKS3' THEN psd.tersedia END) AS data_peks3,
  MAX(CASE WHEN sd.nama_sumber = 'Data PSI' THEN psd.tersedia END) AS data_psi,
  MAX(CASE WHEN sd.nama_sumber = 'Permenko' THEN psd.tersedia END) AS permenko,
  MAX(CASE WHEN sd.nama_sumber = 'RKP 2027' THEN psd.tersedia END) AS rkp_2027,
  MAX(CASE WHEN pk.jenis_ketersediaan = 'Gambaran Umum' THEN sk.nama_status END) AS ketersediaan_gambaran_umum,
  MAX(CASE WHEN pk.jenis_ketersediaan = 'Project Profile Lengkap' THEN sk.nama_status END) AS ketersediaan_project_profile
FROM `psn` p
LEFT JOIN `ref_klaster` k ON k.id = p.klaster_id
LEFT JOIN `ref_provinsi` pr ON pr.id = p.provinsi_id
LEFT JOIN `psn_sumber_data` psd ON psd.psn_id = p.id
LEFT JOIN `ref_sumber_data` sd ON sd.id = psd.sumber_data_id
LEFT JOIN `psn_ketersediaan` pk ON pk.psn_id = p.id
LEFT JOIN `ref_status_ketersediaan` sk ON sk.id = pk.status_ketersediaan_id
GROUP BY p.id, p.nama_psn, k.nama_klaster, pr.nama_provinsi");
    }

    public function down(): void
    {
        DB::statement("CREATE OR REPLACE VIEW `v_psn_ketersediaan_terkini` AS\nSELECT
  p.id AS psn_id,
  p.nama_psn,
  sk.nama_status,
  pk.keterangan,
  pk.periode_pemutakhiran
FROM `psn` p
LEFT JOIN `psn_ketersediaan` pk
  ON pk.psn_id = p.id
  AND pk.id = (
    SELECT pk2.id FROM `psn_ketersediaan` pk2
    WHERE pk2.psn_id = p.id
    ORDER BY pk2.periode_pemutakhiran DESC LIMIT 1
  )
LEFT JOIN `ref_status_ketersediaan` sk ON sk.id = pk.status_ketersediaan_id");

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
};
