<?php

namespace Tests\Feature;

use App\Models\Psn;
use App\Support\MatriksSandinganImporter;
use Database\Seeders\RefKlasterSeeder;
use Database\Seeders\RefProvinsiSeeder;
use Database\Seeders\RefStatusKetersediaanSeeder;
use Database\Seeders\RefSumberDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MatriksSandinganImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_matrik_sandingan_mengisi_psn_dan_tabel_normalisasinya(): void
    {
        $this->seed(RefKlasterSeeder::class);
        $this->seed(RefProvinsiSeeder::class);
        $this->seed(RefSumberDataSeeder::class);
        $this->seed(RefStatusKetersediaanSeeder::class);

        $path = database_path('seeders/data/Matrik_Sandingan_Data_PSN_2026.xlsx');
        $this->assertFileExists($path);

        $hasil = app(MatriksSandinganImporter::class)->import($path, '2026-09-11');

        $this->assertSame(380, $hasil['psn']);
        $this->assertSame(380, Psn::count());
        $this->assertSame(380 * 4, DB::table('psn_sumber_data')->count());
        // 2 baris ketersediaan per PSN (Gambaran Umum + Project Profile Lengkap).
        $this->assertSame(380 * 2, DB::table('psn_ketersediaan')->count());
        $this->assertSame(305, DB::table('psn_ketersediaan')->where('status_ketersediaan_id', 1)->count());
        $this->assertSame(455, DB::table('psn_ketersediaan')->where('status_ketersediaan_id', 2)->count());

        $mbg = Psn::where('nama_psn', 'Makan Bergizi Gratis')->with('klaster', 'provinsi')->first();
        $this->assertNotNull($mbg);
        $this->assertSame('Direktif Presiden', $mbg->klaster->nama_klaster);
        $this->assertSame('Nasional', $mbg->provinsi->nama_provinsi);
        $this->assertSame('2026-09-11', $mbg->periode_update->toDateString());

        $sumberMbg = DB::table('psn_sumber_data')
            ->join('ref_sumber_data', 'ref_sumber_data.id', '=', 'psn_sumber_data.sumber_data_id')
            ->where('psn_sumber_data.psn_id', $mbg->id)
            ->pluck('psn_sumber_data.tersedia', 'ref_sumber_data.nama_sumber');
        $this->assertTrue((bool) $sumberMbg['RKP Pemutakhiran 2026 (Perpres 68)']);
        $this->assertTrue((bool) $sumberMbg['Data PEKS3']);
        $this->assertTrue((bool) $sumberMbg['Data PSI']);
        $this->assertTrue((bool) $sumberMbg['Permenko']);

        $ketersediaanMbg = DB::table('psn_ketersediaan')
            ->join('ref_status_ketersediaan', 'ref_status_ketersediaan.id', '=', 'psn_ketersediaan.status_ketersediaan_id')
            ->where('psn_ketersediaan.psn_id', $mbg->id)
            ->pluck('ref_status_ketersediaan.nama_status', 'psn_ketersediaan.jenis_ketersediaan');
        $this->assertSame('Ada', $ketersediaanMbg['Gambaran Umum']);
        $this->assertSame('Ada', $ketersediaanMbg['Project Profile Lengkap']);

        // Baris multi-K/L: "Menteri Sosial dan Menteri Pekerjaan Umum" harus terpecah 2 instansi.
        $sekolahRakyat = Psn::where('nama_psn', 'Pembangunan Sekolah Rakyat')->first();
        $this->assertSame(2, DB::table('psn_penanggung_jawab')->where('psn_id', $sekolahRakyat->id)->count());

        // Import ulang harus idempoten (tidak dobel).
        app(MatriksSandinganImporter::class)->import($path, '2026-09-11');
        $this->assertSame(380, Psn::count());
    }

    public function test_import_kode_rkp_mengisi_kolom_kode_rkp_dari_master_data_psn_kode(): void
    {
        $this->seed(RefKlasterSeeder::class);
        $this->seed(RefProvinsiSeeder::class);
        $this->seed(RefSumberDataSeeder::class);
        $this->seed(RefStatusKetersediaanSeeder::class);

        $path = database_path('seeders/data/Matrik_Sandingan_Data_PSN_2026.xlsx');
        $importer = app(MatriksSandinganImporter::class);
        $importer->import($path, '2026-09-17');

        $kodePath = database_path('seeders/data/Master_Data_PSN_Kode.xlsx');
        $this->assertFileExists($kodePath);

        $hasil = $importer->importKodeRkp($kodePath);

        $this->assertSame(379, $hasil['cocok']);
        $this->assertSame(1, $hasil['tidak_cocok']);
        $this->assertSame(379, Psn::whereNotNull('kode_rkp')->count());

        $mbg = Psn::where('nama_psn', 'Makan Bergizi Gratis')->firstOrFail();
        $this->assertSame('DP.1-2026.1-01', $mbg->kode_rkp);
        $this->assertSame('PEKS 4', $mbg->peks);
        $this->assertSame('Direktorat Kesehatan dan Gizi Masyarakat', $mbg->unit_kerja);
    }

    /**
     * Kolom C ("Nama PSN") pada pembaruan 24 Sept 2026 ditemukan terpotong
     * (truncated) untuk nama PSN yang sangat panjang -- pencocokan HARUS
     * memakai kolom B ("PSN") yang berisi nama lengkap, bukan kolom C.
     */
    public function test_import_kode_rkp_mencocokkan_lewat_kolom_psn_bukan_nama_psn_yang_terpotong(): void
    {
        $namaPanjang = str_repeat('Program Pembangunan Smelter Sangat Panjang ', 10);
        Psn::create(['nama_psn' => $namaPanjang]);

        $sheet = new Spreadsheet;
        $ws = $sheet->getActiveSheet();
        $ws->fromArray(['Kode_PSI', 'PSN', 'Nama PSN', 'Peks', 'Unit_Kerja'], null, 'A1');
        $ws->fromArray([null, null, null, null, null], null, 'A2');
        $ws->fromArray(['J1-2026.1-01', $namaPanjang, mb_substr($namaPanjang, 0, 50), 'PEKS 2 dan PEKS 4', 'Direktorat Industri'], null, 'A3');

        $path = tempnam(sys_get_temp_dir(), 'kode_psn_').'.xlsx';
        (new Xlsx($sheet))->save($path);

        $hasil = app(MatriksSandinganImporter::class)->importKodeRkp($path);

        $this->assertSame(1, $hasil['cocok']);
        $this->assertSame(0, $hasil['tidak_cocok']);

        $psn = Psn::where('nama_psn', $namaPanjang)->firstOrFail();
        $this->assertSame('J1-2026.1-01', $psn->kode_rkp);
        $this->assertSame('PEKS 2 dan PEKS 4', $psn->peks);
        $this->assertSame('Direktorat Industri', $psn->unit_kerja);
    }
}
