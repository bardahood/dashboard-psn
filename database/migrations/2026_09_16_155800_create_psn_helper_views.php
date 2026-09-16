<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

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
        DB::statement("CREATE OR REPLACE VIEW `v_psn_penanggung_jawab` AS\nSELECT
  p.id AS psn_id,
  p.nama_psn,
  GROUP_CONCAT(i.nama_instansi SEPARATOR ' dan ') AS daftar_penanggung_jawab
FROM `psn` p
LEFT JOIN `psn_penanggung_jawab` pj ON pj.psn_id = p.id
LEFT JOIN `ref_instansi` i ON i.id = pj.instansi_id
GROUP BY p.id, p.nama_psn");
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
        DB::statement("CREATE OR REPLACE VIEW `v_ro_untuk_kunjungan` AS\nSELECT
  ro.id AS ro_id,
  ro.psn_id AS psn_id,
  p.nama_psn AS nama_psn,
  ro.nama_ro AS nama_ro,
  ro.tipe AS tipe,
  ro.ro_induk_id AS ro_induk_id,
  ro.is_ro_kunci AS is_ro_kunci,
  ro.satuan AS satuan
FROM `ro_proyek` ro
JOIN `psn` p ON p.id = ro.psn_id");
        DB::statement("CREATE OR REPLACE VIEW `v_risiko_untuk_kunjungan` AS\nSELECT
  rk.id AS risiko_id,
  rk.psn_id AS psn_id,
  p.nama_psn AS nama_psn,
  rk.peristiwa_risiko AS peristiwa_risiko,
  rk.kategori_risiko AS kategori_risiko,
  rk.level_risiko_awal AS level_risiko_awal,
  rk.risiko_residual_harapan AS risiko_residual_harapan
FROM `risiko_psn` rk
JOIN `psn` p ON p.id = rk.psn_id");
        DB::statement("CREATE OR REPLACE VIEW `v_ringkasan_kunjungan_pengendalian` AS\nSELECT
  kp.id AS kunjungan_id,
  kp.tanggal_kunjungan,
  p.nama_psn,
  kp.tipe_hierarki,
  kp.status_pengendalian,
  pic.nama_pic AS verifikator,
  (SELECT COUNT(*) FROM `kunjungan_pengendalian_fisik` f WHERE f.kunjungan_id = kp.id) AS jumlah_ro_diverifikasi,
  (SELECT COUNT(*) FROM `kunjungan_pengendalian_risiko` r WHERE r.kunjungan_id = kp.id) AS jumlah_risiko_diverifikasi,
  (SELECT COUNT(*) FROM `kunjungan_pengendalian_regulasi` g WHERE g.kunjungan_id = kp.id) AS jumlah_regulasi_diverifikasi
FROM `kunjungan_pengendalian` kp
JOIN `psn` p ON p.id = kp.psn_id
LEFT JOIN `ref_pic` pic ON pic.id = kp.verifikator_id");
        DB::statement("CREATE OR REPLACE VIEW `v_kriteria_untuk_form` AS\nSELECT
  id, kelompok, kode_kriteria, kode_sub, judul_kriteria, sub_butir,
  rubrik_penilaian, tipe_penilaian, kondisional, syarat_kondisional, urutan
FROM `ref_kriteria_perencanaan`
ORDER BY urutan, kode_kriteria, kode_sub");
        DB::statement("CREATE OR REPLACE VIEW `v_skor_kunjungan_perencanaan` AS\nSELECT
  kv.kunjungan_id,
  rk.kelompok,
  AVG(CASE WHEN rk.tipe_penilaian = 'Skor0-3' THEN CAST(kv.nilai_hasil_verifikasi AS DECIMAL(3,1)) END) AS rata_rata_skor_0_3,
  SUM(CASE WHEN kv.nilai_hasil_verifikasi = 'Tidak' THEN 1 ELSE 0 END) AS jumlah_tidak_terpenuhi
FROM `kunjungan_verifikasi_kriteria` kv
JOIN `ref_kriteria_perencanaan` rk ON rk.id = kv.kriteria_id
GROUP BY kv.kunjungan_id, rk.kelompok");
        DB::statement("CREATE OR REPLACE VIEW `v_ringkasan_kunjungan_perencanaan` AS\nSELECT
  kp.id AS kunjungan_id,
  kp.tanggal_kunjungan,
  kp.nama_usulan_psn,
  kl.nama_klaster,
  kp.jenis_pengusul,
  kp.rekomendasi_keseluruhan,
  pic.nama_pic AS verifikator
FROM `kunjungan_perencanaan` kp
LEFT JOIN `ref_klaster` kl ON kl.id = kp.klaster_id
LEFT JOIN `ref_pic` pic ON pic.id = kp.verifikator_id");
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
        DB::statement("CREATE OR REPLACE VIEW `v_indikator_capaian_terkini` AS\nSELECT
  ip.psn_id,
  ip.nama_indikator,
  ip.satuan,
  ip.baseline,
  itt.tahun,
  itt.target,
  itt.realisasi,
  itt.persen_realisasi,
  itt.status_capaian
FROM `indikator_psn` ip
JOIN `indikator_psn_target_tahunan` itt
  ON itt.indikator_id = ip.id
  AND itt.tahun = (SELECT MAX(t2.tahun) FROM `indikator_psn_target_tahunan` t2 WHERE t2.indikator_id = ip.id)");
        DB::statement("CREATE OR REPLACE VIEW `v_risiko_status_terkini` AS\nSELECT
  rk.psn_id,
  rk.peristiwa_risiko,
  rk.level_risiko_awal,
  rk.risiko_residual_harapan,
  rsp.tahun,
  rsp.triwulan,
  rsp.progres_pelaksanaan_persen,
  rsp.risiko_residual_aktual,
  rsp.status_perlakuan
FROM `risiko_psn` rk
LEFT JOIN `risiko_status_periode` rsp
  ON rsp.risiko_id = rk.id
  AND rsp.id = (SELECT t2.id FROM `risiko_status_periode` t2 WHERE t2.risiko_id = rk.id ORDER BY t2.tahun DESC, t2.triwulan DESC LIMIT 1)");

    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("DROP VIEW IF EXISTS `v_psn_sandingan_sumber`");
        DB::statement("DROP VIEW IF EXISTS `v_psn_penanggung_jawab`");
        DB::statement("DROP VIEW IF EXISTS `v_psn_ketersediaan_terkini`");
        DB::statement("DROP VIEW IF EXISTS `v_ro_untuk_kunjungan`");
        DB::statement("DROP VIEW IF EXISTS `v_risiko_untuk_kunjungan`");
        DB::statement("DROP VIEW IF EXISTS `v_ringkasan_kunjungan_pengendalian`");
        DB::statement("DROP VIEW IF EXISTS `v_kriteria_untuk_form`");
        DB::statement("DROP VIEW IF EXISTS `v_skor_kunjungan_perencanaan`");
        DB::statement("DROP VIEW IF EXISTS `v_ringkasan_kunjungan_perencanaan`");
        DB::statement("DROP VIEW IF EXISTS `v_psn_profil_lengkap`");
        DB::statement("DROP VIEW IF EXISTS `v_indikator_capaian_terkini`");
        DB::statement("DROP VIEW IF EXISTS `v_risiko_status_terkini`");

    }
};
