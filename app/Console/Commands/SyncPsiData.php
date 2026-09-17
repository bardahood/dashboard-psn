<?php

namespace App\Console\Commands;

use App\Services\PsiSyncService;
use Illuminate\Console\Command;

/**
 * php artisan psn:sync-psi
 *
 * Menarik pemutakhiran data PSN dari API Dit. PSI. Lihat App\Services\
 * PsiSyncService untuk detail & catatan implementasi -- endpoint belum
 * tersedia saat command ini dibuat, jadi hasilnya akan "Gagal" dengan
 * catatan konfigurasi sampai PSI_API_ENDPOINT diisi di .env.
 */
class SyncPsiData extends Command
{
    protected $signature = 'psn:sync-psi';

    protected $description = 'Sinkronisasi data PSN dari API Direktorat Pembiayaan Strategis dan Inovatif (Dit. PSI)';

    public function handle(PsiSyncService $service): int
    {
        $this->info('Memulai sinkronisasi data PSN dari API Dit. PSI...');

        $hasil = $service->sync();

        if ($hasil['status'] === 'Sukses') {
            $this->info("Sinkronisasi berhasil: {$hasil['jumlah']} PSN diterima.");

            return self::SUCCESS;
        }

        $this->warn('Sinkronisasi belum berhasil: '.$hasil['catatan']);

        return self::FAILURE;
    }
}
