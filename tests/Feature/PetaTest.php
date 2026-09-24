<?php

namespace Tests\Feature;

use App\Models\Psn;
use App\Models\RefProvinsi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_peta_mengelompokkan_psn_per_provinsi_dengan_koordinat(): void
    {
        $jabar = RefProvinsi::create(['nama_provinsi' => 'Jawa Barat']);
        $ntt = RefProvinsi::create(['nama_provinsi' => 'Nusa Tenggara Timur']);
        $nasional = RefProvinsi::create(['nama_provinsi' => 'Nasional']);

        Psn::create(['nama_psn' => 'PSN A', 'provinsi_id' => $jabar->id]);
        Psn::create(['nama_psn' => 'PSN B', 'provinsi_id' => $jabar->id]);
        Psn::create(['nama_psn' => 'PSN C', 'provinsi_id' => $ntt->id]);
        Psn::create(['nama_psn' => 'PSN D', 'provinsi_id' => $nasional->id]);

        $response = $this->get('/peta');

        $response->assertOk();
        $response->assertViewHas('markers', function ($markers) {
            $jabarMarker = $markers->firstWhere('provinsi', 'Jawa Barat');
            $nttMarker = $markers->firstWhere('provinsi', 'Nusa Tenggara Timur');

            return $markers->count() === 2 // "Nasional" tidak boleh muncul di peta
                && $jabarMarker['jumlah'] === 2
                && $jabarMarker['lat'] === -6.914
                && $nttMarker['jumlah'] === 1;
        });
    }
}
