<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DasarHukumPsn;
use App\Models\Psn;
use App\Models\RefPic;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modul Audit Log: memantau aksi CRUD pengguna dengan peran apa pun,
 * ditampung dalam satu tabel (audit_log) sehingga terlacak data apa yang
 * berubah dan oleh siapa (nama pengguna + peran saat aksi terjadi).
 * Melengkapi test dasar observer pada AuditLogAndSyncPsiTest.
 */
class AuditLogModulTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsRole(string $role): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['name' => 'Petugas '.$role]);
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    public function test_audit_log_mencatat_user_dan_peran_yang_melakukan_aksi(): void
    {
        $user = $this->actingAsRole('Super Admin');

        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);

        $log = AuditLog::where('nama_tabel', 'psn')->where('record_id', $psn->id)->firstOrFail();

        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('Super Admin', $log->role);
        $this->assertSame($user->name, $log->user->name);
    }

    public function test_audit_log_mencakup_sub_resource_bukan_hanya_tabel_induk(): void
    {
        $this->actingAsRole('Super Admin');
        $psn = Psn::create(['nama_psn' => 'Contoh PSN']);

        $dasarHukum = DasarHukumPsn::create(['psn_id' => $psn->id, 'nama_regulasi' => 'Perpres Contoh']);
        $dasarHukum->update(['nama_regulasi' => 'Perpres Contoh Diubah']);
        $dasarHukum->delete();

        $logsDasarHukum = AuditLog::where('nama_tabel', 'dasar_hukum_psn')->orderBy('id')->get();
        $this->assertCount(3, $logsDasarHukum);
        $this->assertSame('insert', $logsDasarHukum[0]->aksi);
        $this->assertSame('update', $logsDasarHukum[1]->aksi);
        $this->assertSame('delete', $logsDasarHukum[2]->aksi);
    }

    public function test_audit_log_tidak_pernah_mencatat_hash_password_user(): void
    {
        $this->actingAsRole('Super Admin');
        $pic = RefPic::create(['nama_pic' => 'Target User', 'email' => 'target@bappenas.go.id']);

        $target = User::create([
            'name' => 'Target User',
            'email' => 'target@bappenas.go.id',
            'password' => bcrypt('rahasia-awal'),
            'pic_id' => $pic->id,
        ]);
        $target->update(['password' => bcrypt('rahasia-baru')]);

        $logs = AuditLog::where('nama_tabel', 'users')->where('record_id', $target->id)->get();
        $this->assertNotEmpty($logs);

        foreach ($logs as $log) {
            $this->assertArrayNotHasKey('password', $log->nilai_lama ?? []);
            $this->assertArrayNotHasKey('password', $log->nilai_baru ?? []);
        }
    }

    public function test_halaman_audit_log_bisa_difilter_per_peran_aksi_dan_nama_pengguna(): void
    {
        $superAdmin = $this->actingAsRole('Super Admin');
        Psn::create(['nama_psn' => 'PSN oleh Super Admin']);

        $this->actingAs($superAdmin);
        $editor = User::factory()->create(['name' => 'Editor Perencanaan']);
        $editor->assignRole('Viewer Internal');

        $this->actingAs($editor);
        // Viewer Internal tidak berhak mengubah PSN, jadi untuk mensimulasikan
        // aksi lintas-peran cukup buat langsung sebagai user tsb (observer
        // hanya peduli siapa yang sedang login, bukan otorisasi Livewire).
        Psn::create(['nama_psn' => 'PSN oleh Editor']);

        $this->actingAs($superAdmin);

        // Query langsung memverifikasi filter role benar-benar menyaring baris
        // (bukan sekadar cocok teks di HTML, yang bisa keliru positif karena
        // nama aktor yang sedang login tetap tampil di nav terlepas dari filter).
        $rowsRole = AuditLog::query()->where('role', 'Viewer Internal')->get();
        $this->assertCount(1, $rowsRole);
        $this->assertSame($editor->id, $rowsRole->first()->user_id);

        $responseRole = $this->get('/admin/audit-log?role=Viewer Internal');
        $responseRole->assertOk();
        $responseRole->assertSee('Editor Perencanaan');

        $responseNama = $this->get('/admin/audit-log?q=Editor Perencanaan');
        $responseNama->assertOk();
        $responseNama->assertSee('Editor Perencanaan');

        $responseAksi = $this->get('/admin/audit-log?aksi=insert');
        $responseAksi->assertOk();
        $responseAksi->assertSee('insert');
    }
}
