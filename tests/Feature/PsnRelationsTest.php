<?php

namespace Tests\Feature;

use App\Models\Psn;
use App\Models\RefKlaster;
use App\Models\RefProvinsi;
use App\Models\RoProyek;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PsnRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_psn_belongs_to_klaster_dan_provinsi(): void
    {
        $klaster = RefKlaster::create(['nama_klaster' => 'Swasembada Pangan']);
        $provinsi = RefProvinsi::create(['nama_provinsi' => 'Nusa Tenggara Timur']);

        $psn = Psn::create([
            'nama_psn' => 'Pengembangan Budi Daya Udang Terintegrasi',
            'klaster_id' => $klaster->id,
            'provinsi_id' => $provinsi->id,
        ]);

        $this->assertTrue($psn->klaster->is($klaster));
        $this->assertTrue($psn->provinsi->is($provinsi));
        $this->assertTrue($klaster->psn->contains($psn));
    }

    public function test_ro_proyek_hierarki_self_reference(): void
    {
        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);

        $roInduk = RoProyek::create([
            'psn_id' => $psn->id,
            'nama_ro' => 'RO Induk',
            'tipe' => 'RO',
            'is_ro_kunci' => true,
        ]);

        $aktivitas = RoProyek::create([
            'psn_id' => $psn->id,
            'ro_induk_id' => $roInduk->id,
            'nama_ro' => 'Aktivitas Turunan',
            'tipe' => 'Aktivitas',
        ]);

        $this->assertTrue($aktivitas->roInduk->is($roInduk));
        $this->assertCount(1, $roInduk->anak);
        $this->assertTrue($roInduk->anak->first()->is($aktivitas));
        $this->assertTrue($roInduk->is_ro_kunci);
    }

    public function test_sumber_input_check_constraint_ditegakkan_di_database(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Psn::create(['nama_psn' => 'PSN Tidak Valid', 'sumber_input' => 'Invalid']);
    }
}
