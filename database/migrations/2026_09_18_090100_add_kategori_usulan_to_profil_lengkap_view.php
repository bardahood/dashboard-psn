<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * GAP #1 (lanjutan): menambahkan psn.kategori_usulan ke v_psn_profil_lengkap
 * agar halaman detail publik/admin bisa menampilkannya tanpa query terpisah.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("CREATE OR REPLACE VIEW `v_psn_profil_lengkap` AS\nSELECT
  p.id AS psn_id,
  p.nama_psn,
  k.nama_klaster,
  sp.nama_status AS status_psn,
  p.tipe_hierarki,
  p.kategori_usulan,
  pr.nama_provinsi,
  p.kabupaten_kota,
  p.urgensi,
  p.tujuan_utama,
  p.tahun_penyelesaian,
  p.output_akhir,
  p.nilai_investasi_apbn_rp,
  p.nilai_investasi_non_apbn_rp,
  ip.nama_instansi AS pengusul,
  ig.nama_instansi AS pengelola,
  ik.nama_instansi AS kontraktor,
  is_.nama_instansi AS supervisi
FROM `psn` p
LEFT JOIN `ref_klaster` k ON k.id = p.klaster_id
LEFT JOIN `ref_status_psn` sp ON sp.id = p.status_psn_id
LEFT JOIN `ref_provinsi` pr ON pr.id = p.provinsi_id
LEFT JOIN `ref_instansi` ip ON ip.id = p.pengusul_instansi_id
LEFT JOIN `ref_instansi` ig ON ig.id = p.pengelola_instansi_id
LEFT JOIN `ref_instansi` ik ON ik.id = p.kontraktor_instansi_id
LEFT JOIN `ref_instansi` is_ ON is_.id = p.supervisi_instansi_id");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("CREATE OR REPLACE VIEW `v_psn_profil_lengkap` AS\nSELECT
  p.id AS psn_id,
  p.nama_psn,
  k.nama_klaster,
  sp.nama_status AS status_psn,
  p.tipe_hierarki,
  pr.nama_provinsi,
  p.kabupaten_kota,
  p.urgensi,
  p.tujuan_utama,
  p.tahun_penyelesaian,
  p.output_akhir,
  p.nilai_investasi_apbn_rp,
  p.nilai_investasi_non_apbn_rp,
  ip.nama_instansi AS pengusul,
  ig.nama_instansi AS pengelola,
  ik.nama_instansi AS kontraktor,
  is_.nama_instansi AS supervisi
FROM `psn` p
LEFT JOIN `ref_klaster` k ON k.id = p.klaster_id
LEFT JOIN `ref_status_psn` sp ON sp.id = p.status_psn_id
LEFT JOIN `ref_provinsi` pr ON pr.id = p.provinsi_id
LEFT JOIN `ref_instansi` ip ON ip.id = p.pengusul_instansi_id
LEFT JOIN `ref_instansi` ig ON ig.id = p.pengelola_instansi_id
LEFT JOIN `ref_instansi` ik ON ik.id = p.kontraktor_instansi_id
LEFT JOIN `ref_instansi` is_ ON is_.id = p.supervisi_instansi_id");
    }
};
