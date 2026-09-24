<?php

namespace Tests\Feature;

use App\Models\Psn;
use App\Models\RefRoKrisna;
use App\Models\RoProyek;
use App\Support\LaporanPsnImporter;
use Database\Seeders\MatriksSandinganPsnSeeder;
use Database\Seeders\RefKlasterSeeder;
use Database\Seeders\RefProvinsiSeeder;
use Database\Seeders\RefStatusKetersediaanSeeder;
use Database\Seeders\RefSumberDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Impor katalog RO/Output resmi Krisna (laporan_PSN.xlsx), pengayaan jalur
 * PN/PP/KP/ProP dari matrix_pembangunan_rkp2026.xlsx, dan pengisian awal
 * RO/Proyek dari katalog tsb untuk PSN yang profilnya masih kosong
 * (Risalah Rapat 21 Sept 2026: "RO pilihannya dropdown, pilihan ditarik
 * dari krisna"; "Contoh PSN jalan tol wajib terisi progress per-ruas jalan
 * ... karena sudah ada datanya").
 */
class LaporanPsnImporterTest extends TestCase
{
    use RefreshDatabase;

    protected function seedPsn(): void
    {
        $this->seed(RefKlasterSeeder::class);
        $this->seed(RefProvinsiSeeder::class);
        $this->seed(RefSumberDataSeeder::class);
        $this->seed(RefStatusKetersediaanSeeder::class);
        $this->seed(MatriksSandinganPsnSeeder::class);
    }

    public function test_import_katalog_ro_mengisi_ref_ro_krisna_dan_menautkan_ke_psn(): void
    {
        $this->seedPsn();
        $path = database_path('seeders/data/Laporan_PSN.xlsx');
        $this->assertFileExists($path);

        $hasil = app(LaporanPsnImporter::class)->importKatalogRo($path);

        $this->assertSame(423, $hasil['baris_diimpor']);
        $this->assertSame(423, RefRoKrisna::count());
        $this->assertGreaterThan(400, $hasil['baris_tertaut_psn']);

        $tolSemarangDemak = Psn::where('nama_psn', 'Jalan Tol Semarang - Demak')->firstOrFail();
        $baris = RefRoKrisna::where('psn_id', $tolSemarangDemak->id)->get();
        $this->assertGreaterThanOrEqual(2, $baris->count());
        $this->assertTrue($baris->contains(fn ($r) => str_contains((string) $r->lokasi_ro, 'DEMAK 1B')));
    }

    /**
     * Regresi untuk false-positive nyata yang ditemukan saat pengembangan:
     * "Pembangunan Jaringan Gas Kota" pernah salah tertaut ke PSN Irigasi
     * Lematang, dan "Ketenagalistrikan" pernah salah tertaut ke PSN
     * "Pengembangan Peternakan" -- keduanya hanya mirip di kata generik
     * ("Pembangunan", "Jaringan"/"Infrastruktur"), bukan proyek yang sama.
     */
    public function test_import_katalog_ro_tidak_salah_tautkan_proyek_yang_hanya_mirip_kata_generik(): void
    {
        $this->seedPsn();
        $path = database_path('seeders/data/Laporan_PSN.xlsx');

        app(LaporanPsnImporter::class)->importKatalogRo($path);

        $gasKota = RefRoKrisna::where('project_psn', 'like', '%Jaringan Gas Kota%')->first();
        $this->assertNotNull($gasKota);
        $this->assertNull($gasKota->psn_id);

        $ketenagalistrikan = RefRoKrisna::where('project_psn', 'like', '%Ketenagalistrikan: Pembangunan Infrastruktur%')->first();
        $this->assertNotNull($ketenagalistrikan);
        $this->assertNull($ketenagalistrikan->psn_id);
    }

    public function test_pengayaan_matrix_rkp_mengisi_jalur_prop_untuk_baris_yang_cocok(): void
    {
        $this->seedPsn();
        $importer = app(LaporanPsnImporter::class);
        $importer->importKatalogRo(database_path('seeders/data/Laporan_PSN.xlsx'));

        $matrixPath = database_path('seeders/data/Matrix_Pembangunan_RKP2026.xlsx');
        $this->assertFileExists($matrixPath);

        $hasil = $importer->pengayaanMatrixRkp($matrixPath);

        $this->assertGreaterThan(100, $hasil['baris_diperkaya']);
        $this->assertGreaterThan(0, RefRoKrisna::whereNotNull('prop_kode_rkp')->count());
    }

    public function test_seed_ro_proyek_dari_katalog_mengisi_ro_untuk_psn_kosong_dan_tidak_menimpa_yang_sudah_ada(): void
    {
        $this->seedPsn();
        $importer = app(LaporanPsnImporter::class);
        $importer->importKatalogRo(database_path('seeders/data/Laporan_PSN.xlsx'));

        // Simulasikan satu PSN yang sudah punya RO manual sebelum seeding Krisna
        // dijalankan -- harus TIDAK ditimpa/diduplikasi (existing-first).
        $patimban = Psn::where('nama_psn', 'Jalan Tol Akses Pelabuhan Patimban')->firstOrFail();
        RoProyek::create(['psn_id' => $patimban->id, 'nama_ro' => 'RO Manual Sudah Ada', 'tipe' => 'RO']);

        $hasil = $importer->seedRoProyekDariKatalog();

        $this->assertGreaterThan(0, $hasil['ro_dibuat']);
        $this->assertGreaterThan(0, $hasil['psn_diisi']);

        // PSN yang sudah punya RO manual tetap hanya 1 baris (tidak ditambah dari Krisna).
        $this->assertSame(1, RoProyek::where('psn_id', $patimban->id)->count());
        $this->assertSame('RO Manual Sudah Ada', RoProyek::where('psn_id', $patimban->id)->first()->nama_ro);

        // PSN yang profilnya kosong (toll Semarang-Demak) terisi per-ruas dari Krisna.
        $semarangDemak = Psn::where('nama_psn', 'Jalan Tol Semarang - Demak')->firstOrFail();
        $roSemarangDemak = RoProyek::where('psn_id', $semarangDemak->id)->get();
        $this->assertGreaterThanOrEqual(2, $roSemarangDemak->count());
        $ruas1b = $roSemarangDemak->firstWhere('lokasi', 'TOL SEMARANG - DEMAK 1B');
        $this->assertNotNull($ruas1b);
        $this->assertSame('km', $ruas1b->satuan);
        $this->assertSame('2.6', $ruas1b->target_akhir);
    }

    public function test_lokasi_multi_provinsi_yang_sangat_panjang_diringkas_bukan_dipotong_paksa(): void
    {
        $this->seedPsn();
        $importer = app(LaporanPsnImporter::class);
        $importer->importKatalogRo(database_path('seeders/data/Laporan_PSN.xlsx'));
        $importer->seedRoProyekDariKatalog();

        $kartuUsaha = Psn::where('nama_psn', 'Kartu Usaha Afirmatif')->firstOrFail();
        $roMultiLokasi = RoProyek::where('psn_id', $kartuUsaha->id)->where('lokasi', 'like', 'Multi-lokasi%')->first();

        $this->assertNotNull($roMultiLokasi);
        $this->assertLessThan(255, strlen($roMultiLokasi->lokasi));
    }

    /**
     * @return string path file .xlsx sementara (dihapus otomatis oleh OS/tmp)
     */
    private function buatFixtureSandingan(array $baris): string
    {
        $sheet = new Spreadsheet;
        $ws = $sheet->getActiveSheet();
        $ws->fromArray(['sektor_psn', 'nama_psn', 'ppn', 'ro', 'kode_output_or_ro', 'PN_PP_KP_ProP', 'lokasi_ro'], null, 'A1');
        $ws->fromArray($baris, null, 'A2');

        $path = tempnam(sys_get_temp_dir(), 'sandingan_').'.xlsx';
        (new Xlsx($sheet))->save($path);

        return $path;
    }

    public function test_import_hasil_sandingan_menautkan_persis_dan_prefiks_lalu_mengisi_ref_ro_krisna(): void
    {
        $bendungan = Psn::create(['nama_psn' => 'Bendungan Tiga Dihaji']);
        $gasKota = Psn::create(['nama_psn' => 'Pembangunan Jaringan Gas Kota Provinsi DKI Jakarta, Provinsi Kepulauan Riau']);

        $path = $this->buatFixtureSandingan([
            ['E-Swasembada Air', 'Bendungan Tiga Dihaji', '01-Pembangunan tampungan air', 'Bendungan Tiga Dihaji', '02.10.03.01.015', 'Bendungan Tiga Dihaji', 'Pusat'],
            // nama_psn di sumber adalah prefiks dari nama_psn asli (tanpa daftar provinsi di belakang) -- harus tetap tertaut.
            ['A-Direktif Presiden', 'Pembangunan Jaringan Gas Kota', '02-Distribusi Gas', 'Jaringan Distribusi Gas Kota', '02.09.01.02.010', 'Jaringan Distribusi Gas Kota', 'Provinsi DKI Jakarta'],
            // nama_psn tidak dikenal sama sekali -- harus dilewati, tidak membuat baris & tidak error.
            ['X-Tidak Dikenal', 'PSN Yang Tidak Ada Di Database Ini', '01-Entah', 'RO Entah', '99.99.99.99.999', 'RO Entah', 'Pusat'],
        ]);

        $hasil = app(LaporanPsnImporter::class)->importHasilSandingan($path);

        $this->assertSame(2, $hasil['baris_diimpor']);
        $this->assertSame(2, $hasil['psn_tertaut']);
        $this->assertSame(['PSN Yang Tidak Ada Di Database Ini'], $hasil['psn_tidak_ditemukan']);

        $this->assertDatabaseHas('ref_ro_krisna', ['psn_id' => $bendungan->id, 'ro' => 'Bendungan Tiga Dihaji', 'prop_kode_rkp' => '02.10.03.01.015']);
        $this->assertDatabaseHas('ref_ro_krisna', ['psn_id' => $gasKota->id, 'ro' => 'Jaringan Distribusi Gas Kota']);
        $this->assertDatabaseMissing('ref_ro_krisna', ['ro' => 'RO Entah']);
    }

    public function test_import_hasil_sandingan_melewati_baris_duplikat_terhadap_katalog_yang_sudah_ada(): void
    {
        $psn = Psn::create(['nama_psn' => 'Bendungan Tiga Dihaji']);
        $existing = RefRoKrisna::create(['psn_id' => $psn->id, 'ro' => '001-Bendungan Tiga Dihaji', 'project_rkp' => '001-Bendungan Tiga Dihaji', 'lokasi_ro' => 'Pusat']);

        $path = $this->buatFixtureSandingan([
            // RO + lokasi sama persis (setelah normalisasi & kode depan dibuang) dengan yang sudah ada -- tidak membuat
            // baris baru, tapi baris lama diperkaya kode RKP-nya karena sebelumnya masih kosong.
            ['E-Swasembada Air', 'Bendungan Tiga Dihaji', '01-Pembangunan tampungan air', '  bendungan tiga dihaji  ', '02.10.03.01.015', 'Bendungan Tiga Dihaji', 'Pusat'],
            // RO sama tapi lokasi BEDA -- harus tetap diimpor sebagai entri katalog terpisah, bukan dianggap duplikat.
            ['E-Swasembada Air', 'Bendungan Tiga Dihaji', '01-Pembangunan tampungan air', 'Bendungan Tiga Dihaji', '02.10.03.01.017', 'Bendungan Tiga Dihaji', 'Kab. Ogan Komering Ulu Selatan'],
            ['E-Swasembada Air', 'Bendungan Tiga Dihaji', '01-Pembangunan tampungan air', 'Pengadaan Lahan Bendungan Tiga Dihaji', '02.10.03.01.016', 'Pengadaan Lahan Bendungan Tiga Dihaji', 'Pusat'],
        ]);

        $hasil = app(LaporanPsnImporter::class)->importHasilSandingan($path);

        $this->assertSame(2, $hasil['baris_diimpor']);
        $this->assertSame(1, $hasil['baris_dilewati_duplikat']);
        $this->assertSame(1, $hasil['baris_diperkaya_kode']);
        $this->assertSame(3, RefRoKrisna::where('psn_id', $psn->id)->count());
        $this->assertDatabaseHas('ref_ro_krisna', ['psn_id' => $psn->id, 'ro' => 'Bendungan Tiga Dihaji', 'lokasi_ro' => 'Kab. Ogan Komering Ulu Selatan']);
        $this->assertSame('02.10.03.01.015', $existing->refresh()->prop_kode_rkp);
    }
}
