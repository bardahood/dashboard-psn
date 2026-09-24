<?php

namespace Tests\Feature;

use App\Livewire\Admin\KunjunganPerencanaanWizard;
use App\Models\KunjunganPerencanaan;
use App\Models\RefKriteriaPerencanaan;
use App\Models\User;
use Database\Seeders\RefKriteriaPerencanaanSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KunjunganPerencanaanWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdminPerencanaan(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RefKriteriaPerencanaanSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin Perencanaan');
        $this->actingAs($user);

        return $user;
    }

    public function test_wizard_bisa_membuat_usulan_dan_mengisi_kriteria_utama(): void
    {
        $this->actingAsAdminPerencanaan();

        $component = Livewire::test(KunjunganPerencanaanWizard::class)
            ->set('usulan.nama_usulan_psn', 'Usulan Uji Coba')
            ->set('usulan.tanggal_kunjungan', '2026-09-16')
            ->set('usulan.jenis_pengusul', 'KL')
            ->call('saveUsulan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kunjungan_perencanaan', ['nama_usulan_psn' => 'Usulan Uji Coba']);

        $kunjungan = KunjunganPerencanaan::firstOrFail();
        $u1 = RefKriteriaPerencanaan::where('kode_kriteria', 'U1')->firstOrFail();

        $component
            ->set("kriteriaJawaban.{$u1->id}.nilai_hasil_verifikasi", 'Ya')
            ->call('saveUtama')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kunjungan_verifikasi_kriteria', [
            'kunjungan_id' => $kunjungan->id,
            'kriteria_id' => $u1->id,
            'nilai_hasil_verifikasi' => 'Ya',
        ]);
    }

    public function test_kriteria_kondisional_p4_p5_p6_mengikuti_jenis_pengusul(): void
    {
        $this->actingAsAdminPerencanaan();

        $p4 = RefKriteriaPerencanaan::where('kode_kriteria', 'P4')->firstOrFail();
        $p5 = RefKriteriaPerencanaan::where('kode_kriteria', 'P5')->firstOrFail();

        $component = Livewire::test(KunjunganPerencanaanWizard::class)
            ->set('usulan.jenis_pengusul', 'KL');

        $this->assertTrue($component->instance()->kriteriaTerlihat($p4));
        $this->assertFalse($component->instance()->kriteriaTerlihat($p5));

        $component->set('usulan.jenis_pengusul', 'Pemda');
        $this->assertFalse($component->instance()->kriteriaTerlihat($p4));
        $this->assertTrue($component->instance()->kriteriaTerlihat($p5));
    }

    public function test_kriteria_kondisional_k3_k4_mengikuti_penanda_infrastruktur(): void
    {
        $this->actingAsAdminPerencanaan();

        $k3 = RefKriteriaPerencanaan::where('kode_kriteria', 'K3')->firstOrFail();

        $component = Livewire::test(KunjunganPerencanaanWizard::class);
        $this->assertFalse($component->instance()->kriteriaTerlihat($k3));

        $component->set('isInfrastruktur', true);
        $this->assertTrue($component->instance()->kriteriaTerlihat($k3));
    }

    public function test_gate_kriteria_utama_menggugurkan_rekomendasi_menjadi_ditolak(): void
    {
        $this->actingAsAdminPerencanaan();

        $kunjungan = KunjunganPerencanaan::create(['nama_usulan_psn' => 'Usulan Uji', 'tanggal_kunjungan' => now()]);
        $u1 = RefKriteriaPerencanaan::where('kode_kriteria', 'U1')->firstOrFail();
        $p1a = RefKriteriaPerencanaan::where('kode_kriteria', 'P1')->where('kode_sub', 'a')->firstOrFail();

        $kunjungan->verifikasiKriteria()->create(['kriteria_id' => $u1->id, 'nilai_hasil_verifikasi' => 'Tidak']);
        $kunjungan->verifikasiKriteria()->create(['kriteria_id' => $p1a->id, 'nilai_hasil_verifikasi' => '3']);

        $this->assertTrue($kunjungan->gateUtamaGagal());
        $this->assertEquals('Ditolak', $kunjungan->rekomendasiOtomatis());
    }

    public function test_skor_keseluruhan_berbobot_pendukung_kesiapan_lokasi_trisula(): void
    {
        $this->actingAsAdminPerencanaan();

        $kunjungan = KunjunganPerencanaan::create(['nama_usulan_psn' => 'Usulan Uji', 'tanggal_kunjungan' => now()]);
        $p1a = RefKriteriaPerencanaan::where('kode_kriteria', 'P1')->where('kode_sub', 'a')->firstOrFail();
        $k1a = RefKriteriaPerencanaan::where('kode_kriteria', 'K1')->where('kode_sub', 'a')->firstOrFail();

        $kunjungan->verifikasiKriteria()->create(['kriteria_id' => $p1a->id, 'nilai_hasil_verifikasi' => '3']);
        $kunjungan->verifikasiKriteria()->create(['kriteria_id' => $k1a->id, 'nilai_hasil_verifikasi' => '2']);
        $dampak = \App\Models\RefDampakTrisula::create(['nama_dampak' => 'Uji']);
        $kunjungan->verifikasiLokasi()->create(['aspek' => 'RTRW', 'sesuai' => 'Sesuai']);
        $kunjungan->verifikasiTrisula()->create(['dampak_id' => $dampak->id, 'kondisi_awal_terverifikasi' => 'Ya']);

        // Pendukung 100 (3/3) * .35 + Kesiapan 66.7 (2/3) * .35 + Lokasi 100 * .15 + Trisula 100 * .15
        $this->assertEqualsWithDelta(88.3, $kunjungan->skorKeseluruhan(), 0.1);
        $this->assertEquals('Layak Dilanjutkan', $kunjungan->rekomendasiOtomatis());
    }

    public function test_viewer_internal_tidak_punya_akses_ke_instrumen_perencanaan(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Viewer Internal');
        $this->actingAs($user);

        $this->get('/admin/kunjungan-perencanaan')->assertForbidden();
        $this->get('/admin/kunjungan-perencanaan/create')->assertForbidden();
    }
}
