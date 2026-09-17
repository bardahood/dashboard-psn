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

        $this->assertSame(388, $hasil['psn']);
        $this->assertSame(388, Psn::count());
        $this->assertSame(388 * 4, DB::table('psn_sumber_data')->count());
        $this->assertSame(296, DB::table('psn_ketersediaan')->where('status_ketersediaan_id', 1)->count());
        $this->assertSame(92, DB::table('psn_ketersediaan')->where('status_ketersediaan_id', 2)->count());

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

        // Baris multi-K/L: "Menteri Sosial dan Menteri Pekerjaan Umum" harus terpecah 2 instansi.
        $sekolahRakyat = Psn::where('nama_psn', 'Pembangunan Sekolah Rakyat')->first();
        $this->assertSame(2, DB::table('psn_penanggung_jawab')->where('psn_id', $sekolahRakyat->id)->count());

        // Import ulang harus idempoten (tidak dobel).
        app(MatriksSandinganImporter::class)->import($path, '2026-09-11');
        $this->assertSame(388, Psn::count());
    }
}
