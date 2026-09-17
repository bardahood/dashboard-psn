<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Psn;
use App\Models\SyncLogPsi;
use App\Models\User;
use App\Services\PsiSyncService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogAndSyncPsiTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_observer_mencatat_insert_update_delete_pada_psn(): void
    {
        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);
        $psn->update(['nama_psn' => 'Contoh PSN Diubah']);
        $psn->delete();

        $this->assertDatabaseCount('audit_log', 3);

        $logs = AuditLog::orderBy('id')->get();

        $this->assertEquals('insert', $logs[0]->aksi);
        $this->assertNull($logs[0]->nilai_lama);
        $this->assertEquals('Contoh PSN', $logs[0]->nilai_baru['nama_psn']);

        $this->assertEquals('update', $logs[1]->aksi);
        $this->assertEquals('Contoh PSN', $logs[1]->nilai_lama['nama_psn']);
        $this->assertEquals('Contoh PSN Diubah', $logs[1]->nilai_baru['nama_psn']);

        $this->assertEquals('delete', $logs[2]->aksi);
        $this->assertNull($logs[2]->nilai_baru);

        // created_at harus ter-cast ke Carbon walau $timestamps=false pada model AuditLog.
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $logs[0]->created_at);
    }

    public function test_psi_sync_service_mencatat_riwayat_gagal_saat_endpoint_belum_dikonfigurasi(): void
    {
        config(['services.psi.endpoint' => null]);

        $hasil = (new PsiSyncService)->sync();

        $this->assertEquals('Gagal', $hasil['status']);
        $this->assertDatabaseHas('sync_log_psi', ['status' => 'Gagal']);

        $log = SyncLogPsi::firstOrFail();
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $log->tanggal_sync);
    }

    public function test_hanya_super_admin_bisa_akses_halaman_sinkronisasi_psi_dan_audit_log(): void
    {
        $this->seed(RoleSeeder::class);

        $viewer = User::factory()->create();
        $viewer->assignRole('Viewer Internal');
        $this->actingAs($viewer)->get('/admin/sinkronisasi-psi')->assertForbidden();

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');
        $this->actingAs($superAdmin)->get('/admin/sinkronisasi-psi')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/audit-log')->assertOk();
    }
}
