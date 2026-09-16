<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefKriteriaPerencanaanSeeder extends Seeder
{
    /**
     * 36 baris kriteria + sub-butir Permen PPN/Bappenas No. 4/2025 Pasal 5.
     * Disalin apa adanya dari skema_psn_dashboard_terpadu.sql (Bagian 8) -- JANGAN diringkas.
     */
    public function run(): void
    {
        $rows = [
            ['Utama', 'U1', 'a', 'Dukungan terhadap sasaran RPJMN 2025-2029', 'Bagaimana proyek mendukung sasaran RPJMN 2025-2029, terutama program prioritas Presiden dan Program Hasil Terbaik Cepat (PHTC)?', 'Penilai/evaluator memberikan expert judgement berdasarkan kesesuaian dengan 17 program prioritas dan/atau 8 PHTC.', 'YaTidak', false, null, 1],
            ['Utama', 'U2', 'a', 'Kebutuhan konsentrasi, keterpaduan, dan sinergi sumber daya', 'Mengapa proyek memerlukan konsentrasi, keterpaduan, dan sinergi sumber daya lintas pemangku kepentingan?', 'Verifikator memastikan penjelasan lapangan mencakup: (1) tahapan proyek saat ini, (2) permasalahan/isu yang dihadapi, (3) alasan kebutuhan status PSN, (4) dukungan yang dibutuhkan dari tiap pemangku kepentingan, (5) bukti bahwa proyek bersifat strategis -- bukan proyek rutin.', 'YaTidak', false, null, 2],
            ['Utama', 'U3', 'a', 'Target fungsi/manfaat 2029', 'Bagian apa yang selesai atau berfungsi pada 2029? Untuk proyek besar (kontribusi >0,1% PDB nasional/>1% PDRB daerah atau economic IRR >20%), apakah konstruksi dimulai sebelum 2029?', 'Verifikator mengecek kesesuaian klaim jadwal/jalur kritis dengan kondisi fisik lapangan saat ini (progres aktual vs klaim dokumen).', 'YaTidak', false, null, 3],
            ['Pendukung', 'P1', 'a', 'Kaidah tematik, holistik, integratif, spasial', 'Tematik (selaras 8 Prioritas Nasional RPJMN)', '3: prioritas nasional terkait + indikator kuantitatif | 2: kualitatif | 1: pointers singkat | 0: tidak diisi', 'Skor0-3', false, null, 10],
            ['Pendukung', 'P1', 'b', 'Kaidah tematik, holistik, integratif, spasial', 'Holistik (perencanaan seluruh tahap life cycle: perencanaan, penyiapan, pengadaan, konstruksi, operasional, pengelolaan aset)', '3: seluruh tahapan dijelaskan | 2: lebih dari satu tahap | 1: satu tahap | 0: tidak diisi', 'Skor0-3', false, null, 10],
            ['Pendukung', 'P1', 'c', 'Kaidah tematik, holistik, integratif, spasial', 'Integratif (keterpaduan lintas sektor/subsektor)', '3: sektor terkait + indikator kuantitatif | 2: kualitatif | 1: pointers singkat | 0: tidak diisi', 'Skor0-3', false, null, 10],
            ['Pendukung', 'P1', 'd', 'Kaidah tematik, holistik, integratif, spasial', 'Spasial (keterkaitan antarwilayah, kesesuaian lokasi & pola ruang)', '3: wilayah terkait + indikator kuantitatif | 2: kualitatif | 1: pointers singkat | 0: tidak diisi', 'Skor0-3', false, null, 10],
            ['Pendukung', 'P2', 'a', 'Persebaran kegiatan untuk percepatan dan pemerataan pembangunan', 'Prinsip pemerataan pembangunan (bukan hanya pusat pertumbuhan eksisting)', '3: mengedepankan pemerataan | 2: di pusat pertumbuhan eksisting | 1: tidak mempertimbangkan pemerataan | 0: melenceng dari panduan/tidak diisi', 'Skor0-3', false, null, 20],
            ['Pendukung', 'P3', 'a', 'Multi-kontribusi pada SDM, pertumbuhan ekonomi, dan pengurangan kemiskinan', 'Kontribusi pada kualitas SDM (kesehatan, pendidikan, keterampilan, daya saing)', '3: kontribusi >=4 indikator | 2: 2-3 indikator | 1: 1 indikator | 0: tidak ada kontribusi', 'Skor0-3', false, null, 30],
            ['Pendukung', 'P3', 'b', 'Multi-kontribusi pada SDM, pertumbuhan ekonomi, dan pengurangan kemiskinan', 'Kontribusi pada pertumbuhan ekonomi', '3: kontribusi >=5 indikator | 2: 3-4 indikator | 1: 1-2 indikator | 0: tidak ada kontribusi', 'Skor0-3', false, null, 30],
            ['Pendukung', 'P3', 'c', 'Multi-kontribusi pada SDM, pertumbuhan ekonomi, dan pengurangan kemiskinan', 'Kontribusi pada pengurangan kemiskinan', '3: kontribusi >=4 indikator | 2: 2-3 indikator | 1: 1 indikator | 0: tidak ada kontribusi', 'Skor0-3', false, null, 30],
            ['Pendukung', 'P4', 'a', 'Tercantum dalam Renstra K/L dan mendukung RPJMN', 'Kesesuaian Renstra K/L dan dukungan terhadap RPJMN 2025-2029', '3: sesuai Renstra & mendukung RPJMN | 2: sesuai Renstra, tidak mendukung RPJMN | 1: tidak sesuai tapi ada komitmen pencantuman | 0: tidak sesuai Renstra', 'Skor0-3', true, 'Usulan K/L', 40],
            ['Pendukung', 'P5', 'a', 'Tercantum dalam RPJMD, berkontribusi RPJMN, memerlukan dukungan Pemerintah', 'Kesesuaian RPJMD dan dukungan terhadap RPJMN 2025-2029', '3: sesuai RPJMD & mendukung RPJMN | 2: sesuai RPJMD, tidak mendukung RPJMN | 1: tidak sesuai tapi ada komitmen pencantuman | 0: tidak sesuai RPJMD', 'Skor0-3', true, 'Usulan Pemda', 50],
            ['Pendukung', 'P6', 'a', 'Tercantum dalam RKAP, berkontribusi RPJMN, memerlukan dukungan Pemerintah', 'Kesesuaian RKAP dan dukungan terhadap RPJMN 2025-2029', '3: sesuai RKAP & mendukung RPJMN | 2: sesuai RKAP, tidak mendukung RPJMN | 1: tidak sesuai tapi ada komitmen pencantuman | 0: tidak sesuai RKAP', 'Skor0-3', true, 'Usulan BUMN/Swasta', 60],
            ['Kesiapan', 'K1', 'a', 'Jalur kritis, strategi pelaksanaan, rencana pembiayaan, dan profil risiko', 'Analisis jalur kritis (hingga 2029; titik pentingnya status PSN; tanggal logis; urutan logis; kedalaman cukup; berkodefikasi)', '3: seluruh 6 komponen terpenuhi | 2: maks. 1 komponen tak terpenuhi | 1: maks. 3 komponen tak terpenuhi | 0: tidak ada jalur kritis/>3 komponen tak terpenuhi', 'Skor0-3', false, null, 70],
            ['Kesiapan', 'K1', 'b', 'Jalur kritis, strategi pelaksanaan, rencana pembiayaan, dan profil risiko', 'Strategi pelaksanaan & pencapaian sasaran per kegiatan jalur kritis', '3: memahami masalah & strategi | 2: lengkap tapi normatif | 1: tidak lengkap/tidak paham | 0: tidak dibuat', 'Skor0-3', false, null, 70],
            ['Kesiapan', 'K1', 'c', 'Jalur kritis, strategi pelaksanaan, rencana pembiayaan, dan profil risiko', 'Sumber dan skema pembiayaan (APBN/APBD/BUMN/swasta/loan/hibah/KPBU/dst.)', '3: ada + penjelasan + dinilai layak | 2: ada + tanpa penjelasan tapi layak | 1: ada tanpa penjelasan, dinilai sulit | 0: tidak diisi', 'Skor0-3', false, null, 70],
            ['Kesiapan', 'K1', 'd', 'Jalur kritis, strategi pelaksanaan, rencana pembiayaan, dan profil risiko', 'Skema pembiayaan mempertimbangkan biaya operasional & pemeliharaan', '3: ada + penjelasan + layak | 2: ada tanpa penjelasan tapi layak | 1: ada tanpa penjelasan, dinilai sulit | 0: tidak diisi', 'Skor0-3', false, null, 70],
            ['Kesiapan', 'K1', 'e', 'Jalur kritis, strategi pelaksanaan, rencana pembiayaan, dan profil risiko', 'Kelayakan ekonomi (identifikasi manfaat, kuantifikasi, economic IRR, economic NPV, dokumen pendukung)', '3: lengkap + dokumen pendukung | 2: IRR/NPV tanpa keterangan tambahan | 1: hanya keuntungan umum | 0: tidak diisi', 'Skor0-3', false, null, 70],
            ['Kesiapan', 'K1', 'f', 'Jalur kritis, strategi pelaksanaan, rencana pembiayaan, dan profil risiko', 'Kelayakan finansial (IRR>discount rate, NPV>0, atau utk KPBU: Project IRR>WACC, Equity IRR>Cost of Equity)', '3: lengkap sesuai skema + dokumen pendukung | 2: perhitungan tanpa keterangan tambahan | 1: informasi umum saja | 0: tidak diisi', 'Skor0-3', false, null, 70],
            ['Kesiapan', 'K1', 'g', 'Jalur kritis, strategi pelaksanaan, rencana pembiayaan, dan profil risiko', 'Profil risiko (minimal 5 risiko utama: teknis, legal, ekonomi/finansial, lahan/lingkungan/sosial, operasional)', '3: 5 profil risiko lengkap | 2: 3-4 profil risiko | 1: <3 profil risiko | 0: tidak diisi', 'Skor0-3', false, null, 70],
            ['Kesiapan', 'K2', 'a', 'Struktur kelembagaan pelaksana proyek', 'Struktur & arah pengambilan keputusan antar pihak', '3: struktur & arah keputusan jelas | 2: jelas tapi tidak komprehensif | 1: kurang jelas | 0: tidak diisi', 'Skor0-3', false, null, 80],
            ['Kesiapan', 'K3', 'a', 'Tata ruang, master plan, FS/pra-FS, analisis sosial-lingkungan, manajemen risiko', 'Kesesuaian dengan RTRW (PENGGUGUR bila tidak terpenuhi)', '3: sesuai + penjelasan | 2: sesuai tanpa penjelasan lengkap | 1: tidak sesuai + ada komitmen | 0: tidak sesuai, alasan tak dapat dibenarkan', 'Skor0-3', true, 'Infrastruktur Ekonomi', 90],
            ['Kesiapan', 'K3', 'b', 'Tata ruang, master plan, FS/pra-FS, analisis sosial-lingkungan, manajemen risiko', 'Rencana induk sektor (PENGGUGUR bila tidak terpenuhi)', '3: sesuai + penjelasan | 2: sesuai tanpa penjelasan/sektor tak punya rencana induk | 1: tidak sesuai + komitmen | 0: tidak sesuai, tak dapat dibenarkan', 'Skor0-3', true, 'Infrastruktur Ekonomi', 90],
            ['Kesiapan', 'K3', 'c', 'Tata ruang, master plan, FS/pra-FS, analisis sosial-lingkungan, manajemen risiko', 'Dokumen studi kelayakan/pra-FS (PENGGUGUR bila tidak terpenuhi)', '3: ada FS/dokumen setara | 2: dokumen di bawah level FS | 1: dokumen perencanaan sederhana | 0: tidak ada dokumen', 'Skor0-3', true, 'Infrastruktur Ekonomi', 90],
            ['Kesiapan', 'K3', 'd', 'Tata ruang, master plan, FS/pra-FS, analisis sosial-lingkungan, manajemen risiko', 'Gambar teknik (visual proyek)', '3: DED atau setara | 2: basic design atau setara | 1: gambar sederhana | 0: tidak ada', 'Skor0-3', true, 'Infrastruktur Ekonomi', 90],
            ['Kesiapan', 'K3', 'e', 'Tata ruang, master plan, FS/pra-FS, analisis sosial-lingkungan, manajemen risiko', 'Studi AMDAL dan sosial', '3: dokumen AMDAL + kesimpulan rinci | 2: sedang disusun | 1: baru direncanakan | 0: belum ada', 'Skor0-3', true, 'Infrastruktur Ekonomi', 90],
            ['Kesiapan', 'K3', 'f', 'Tata ruang, master plan, FS/pra-FS, analisis sosial-lingkungan, manajemen risiko', 'Manajemen risiko/risk register (identifikasi, penyebab, keterkaitan jalur kritis, nilai risiko, penanganan)', '3: lengkap & komprehensif | 2: ada, tidak komprehensif | 1: ada, sangat tidak komprehensif | 0: tidak diisi', 'Skor0-3', true, 'Infrastruktur Ekonomi', 90],
            ['Kesiapan', 'K4', 'a', 'Rencana Pengadaan Tanah dan Pemukiman Kembali', 'Kebutuhan luas pengadaan lahan (ha) vs kondisi lapangan', '3: rencana lengkap sesuai 7 komponen panduan | 2: sedang disusun, sebagian sudah ada | 1: baru direncanakan | 0: tidak ada dokumen sama sekali', 'Skor0-3', true, 'Usulan Infrastruktur', 100],
            ['Kesiapan', 'K4', 'b', 'Rencana Pengadaan Tanah dan Pemukiman Kembali', 'Status lahan (clear / bermasalah) -- CEK FISIK & DOKUMEN KEPEMILIKAN', null, 'Skor0-3', true, 'Usulan Infrastruktur', 100],
            ['Kesiapan', 'K4', 'c', 'Rencana Pengadaan Tanah dan Pemukiman Kembali', 'Jumlah bidang/KK terdampak', null, 'Skor0-3', true, 'Usulan Infrastruktur', 100],
            ['Kesiapan', 'K4', 'd', 'Rencana Pengadaan Tanah dan Pemukiman Kembali', 'Estimasi nilai/biaya lahan per satuan luas', null, 'Skor0-3', true, 'Usulan Infrastruktur', 100],
            ['Kesiapan', 'K4', 'e', 'Rencana Pengadaan Tanah dan Pemukiman Kembali', 'Tahapan pengadaan lahan saat ini (sesuai klaim jadwal)', null, 'Skor0-3', true, 'Usulan Infrastruktur', 100],
            ['Kesiapan', 'K4', 'f', 'Rencana Pengadaan Tanah dan Pemukiman Kembali', 'Potensi isu/kendala lahan (sengketa, okupasi, adat, dll.) -- TANYAKAN LANGSUNG KE MASYARAKAT/APARAT SETEMPAT', null, 'Skor0-3', true, 'Usulan Infrastruktur', 100],
            ['Kesiapan', 'K4', 'g', 'Rencana Pengadaan Tanah dan Pemukiman Kembali', 'Jenis lahan (pemukiman/hutan/lahan kosong, masing-masing berapa ha)', null, 'Skor0-3', true, 'Usulan Infrastruktur', 100],
            ['Kesiapan', 'K5', 'a', 'Analisis dampak pada kualitas SDM, kemiskinan, pertumbuhan/ketahanan ekonomi, pemerataan', 'Metodologi dampak, kondisi awal, target, model atribusi, peta penerima manfaat', 'Lihat detail verifikasi pada modul Verifikasi Dampak Trisula (kunjungan_verifikasi_trisula)', 'Skor0-3', false, null, 110],
        ];

        $now = now();
        $data = array_map(function ($r) use ($now) {
            return [
                'kelompok' => $r[0],
                'kode_kriteria' => $r[1],
                'kode_sub' => $r[2],
                'judul_kriteria' => $r[3],
                'sub_butir' => $r[4],
                'rubrik_penilaian' => $r[5],
                'tipe_penilaian' => $r[6],
                'kondisional' => $r[7],
                'syarat_kondisional' => $r[8],
                'urutan' => $r[9],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $rows);

        DB::table('ref_kriteria_perencanaan')->insert($data);
    }
}
