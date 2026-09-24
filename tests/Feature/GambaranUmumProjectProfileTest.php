<?php

namespace Tests\Feature;

use App\Livewire\Admin\AnnualTargetManager;
use App\Livewire\Admin\SubResourceManager;
use App\Models\IndikatorPsn;
use App\Models\Psn;
use App\Models\PsnPenanggungJawab;
use App\Models\RefInstansi;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cek kesesuaian tab "Gambaran Umum" terhadap diagram resmi Struktur Project
 * Profile yang dilampirkan (Status PSN, Klaster PSN, Klaster PKPN, Nama,
 * Urgensi & Dasar Hukum, Tujuan Utama, Diagram Kerangka Kerja Logis,
 * Indikator PP (Khusus PKPN level PP), Pengusul/Penanggung Jawab/Stakeholders
 * Mapping/Kerangka Kelembagaan & Visualisasi Kerangka Kelembagaan, Lokasi,
 * Indikasi Sumber Pendanaan, Tahun Penyelesaian & Output Akhir, Nilai
 * Investasi). Dua field murni baru (Indikasi Sumber Pendanaan level PSN,
 * Indikator PP khusus PKPN), satu relasi yang sudah ada di skema tapi belum
 * pernah punya UI (Penanggung Jawab), dan satu bagian yang dipindah dari tab
 * Upload Dokumen ke Gambaran Umum (Visualisasi Kerangka Kelembagaan).
 */
class GambaranUmumProjectProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsSuperAdmin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        return $user;
    }

    public function test_indikasi_sumber_pendanaan_level_psn_bisa_disimpan(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->put("/admin/psn/{$psn->id}", [
            'nama_psn' => 'Bendungan Contoh',
            'sumber_input' => 'Manual',
            'indikasi_sumber_pendanaan' => 'APBN',
        ])->assertRedirect();

        $this->assertSame('APBN', $psn->refresh()->indikasi_sumber_pendanaan);

        $this->get(route('admin.psn.gambaran-umum', $psn))
            ->assertOk()
            ->assertSee('Indikasi Sumber Pendanaan');
    }

    public function test_indikasi_sumber_pendanaan_ditolak_bila_di_luar_5_kategori(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->put("/admin/psn/{$psn->id}", [
            'nama_psn' => 'Bendungan Contoh',
            'sumber_input' => 'Manual',
            'indikasi_sumber_pendanaan' => 'Kategori Ngawur',
        ])->assertSessionHasErrors('indikasi_sumber_pendanaan');
    }

    public function test_indikator_pp_hanya_tampil_untuk_psn_bertipe_hierarki_pkpn(): void
    {
        $this->actingAsSuperAdmin();
        $pkpn = Psn::create(['nama_psn' => 'PKPN Contoh', 'tipe_hierarki' => 'PKPN']);
        $psnBiasa = Psn::create(['nama_psn' => 'PSN Biasa', 'tipe_hierarki' => 'PSN']);

        $this->get(route('admin.psn.gambaran-umum', $pkpn))
            ->assertOk()
            ->assertSee('Indikator PP');

        $this->get(route('admin.psn.gambaran-umum', $psnBiasa))
            ->assertOk()
            ->assertDontSee('Indikator PP (Khusus PKPN level PP)', false)
            ->assertDontSee('Nama Indikator PP');
    }

    public function test_indikator_pp_tersimpan_terpisah_dari_indikator_output_outcome(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'PKPN Contoh', 'tipe_hierarki' => 'PKPN']);

        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'indikator_pp'])
            ->set('parentForm.nama_indikator', 'Indikator PP Contoh')
            ->call('saveParent')
            ->assertHasNoErrors();

        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'indikator'])
            ->set('parentForm.nama_indikator', 'Indikator Output Biasa')
            ->call('saveParent')
            ->assertHasNoErrors();

        $this->assertSame(2, IndikatorPsn::where('psn_id', $psn->id)->count());

        $indikatorPp = IndikatorPsn::where('nama_indikator', 'Indikator PP Contoh')->firstOrFail();
        $this->assertSame('PP', $indikatorPp->jenis_indikator);

        $indikatorBiasa = IndikatorPsn::where('nama_indikator', 'Indikator Output Biasa')->firstOrFail();
        $this->assertNull($indikatorBiasa->jenis_indikator);

        // Daftar 'indikator' (tab Perencanaan) tidak boleh menampilkan
        // Indikator PP, dan sebaliknya.
        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'indikator'])
            ->assertSee('Indikator Output Biasa')
            ->assertDontSee('Indikator PP Contoh');

        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'indikator_pp'])
            ->assertSee('Indikator PP Contoh')
            ->assertDontSee('Indikator Output Biasa');
    }

    public function test_indikator_pp_tidak_bisa_diedit_lewat_scope_indikator_biasa(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'PKPN Contoh', 'tipe_hierarki' => 'PKPN']);
        $indikatorPp = IndikatorPsn::create(['psn_id' => $psn->id, 'jenis_indikator' => 'PP', 'nama_indikator' => 'Indikator PP Contoh']);

        // Mencoba edit() ID milik indikator_pp lewat instance ber-scope
        // 'indikator' (Output/Outcome biasa) harus gagal (model tidak
        // ditemukan pada scope tsb), bukan menampilkan/mengubah data
        // lintas-scope.
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(AnnualTargetManager::class, ['psn' => $psn, 'type' => 'indikator'])
            ->call('editParent', $indikatorPp->id);
    }

    public function test_penanggung_jawab_bisa_ditambah_diubah_dihapus_lewat_gambaran_umum(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);
        $instansi = RefInstansi::create(['nama_instansi' => 'Kementerian PUPR']);

        $this->get(route('admin.psn.gambaran-umum', $psn))
            ->assertOk()
            ->assertSee('Penanggung Jawab');

        Livewire::test(SubResourceManager::class, ['psn' => $psn, 'type' => 'penanggung_jawab'])
            ->set('form.instansi_id', $instansi->id)
            ->call('save')
            ->assertHasNoErrors();

        $pj = PsnPenanggungJawab::where('psn_id', $psn->id)->firstOrFail();
        $this->assertSame($instansi->id, $pj->instansi_id);

        Livewire::test(SubResourceManager::class, ['psn' => $psn, 'type' => 'penanggung_jawab'])
            ->call('delete', $pj->id);

        $this->assertSame(0, PsnPenanggungJawab::where('psn_id', $psn->id)->count());
    }

    public function test_visualisasi_kerangka_kelembagaan_pindah_ke_gambaran_umum_bukan_dokumen(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->get(route('admin.psn.gambaran-umum', $psn))
            ->assertOk()
            ->assertSee('Visualisasi Kerangka Kelembagaan');

        $this->get(route('admin.psn.dokumen', $psn))
            ->assertOk()
            ->assertDontSee('Visualisasi Kerangka Kelembagaan');
    }

    public function test_label_field_gambaran_umum_sesuai_diagram_struktur_project_profile(): void
    {
        $this->actingAsSuperAdmin();
        $psn = Psn::create(['nama_psn' => 'Bendungan Contoh']);

        $this->get(route('admin.psn.gambaran-umum', $psn))
            ->assertOk()
            ->assertSee('Klaster PSN')
            ->assertSee('Klaster PKPN')
            ->assertSee('Diagram Kerangka Kerja Logis (Kode RKP)');
    }
}
