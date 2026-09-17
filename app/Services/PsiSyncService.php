<?php

namespace App\Services;

use App\Models\SyncLogPsi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sinkronisasi data PSN dari API Dit. PSI (Direktorat Pembiayaan Strategis
 * dan Inovatif). Endpoint dijanjikan tersedia (lihat Resume Rapat 11 Sept
 * 2026) tetapi belum aktif saat pengembangan dashboard ini -- kelas ini
 * dibungkus terpisah dari Command agar mudah disambungkan begitu endpoint
 * dan struktur payload aktualnya diketahui (Bagian 4 prinsip 6 prompt
 * pengembangan).
 */
class PsiSyncService
{
    public function __construct(protected ?string $endpoint = null)
    {
        $this->endpoint ??= config('services.psi.endpoint');
    }

    /**
     * @return array{status: string, jumlah: int, catatan: ?string}
     */
    public function sync(): array
    {
        if (! $this->endpoint) {
            return $this->catatHasil(
                'Gagal',
                0,
                'Endpoint API Dit. PSI belum dikonfigurasi (isi PSI_API_ENDPOINT di .env). '
                    .'Command/service ini sudah siap dipanggil, tinggal disambungkan begitu API tersedia.'
            );
        }

        try {
            $response = Http::timeout(30)
                ->when(config('services.psi.token'), fn ($http) => $http->withToken(config('services.psi.token')))
                ->get($this->endpoint);

            $response->throw();

            $jumlah = $this->terapkanData($response->json('data', []));

            return $this->catatHasil('Sukses', $jumlah, null);
        } catch (\Throwable $e) {
            Log::error('Sinkronisasi API PSI gagal', ['error' => $e->getMessage()]);

            return $this->catatHasil('Gagal', 0, $e->getMessage());
        }
    }

    /**
     * TODO: struktur payload aktual dari Dit. PSI belum tersedia saat
     * pengembangan dashboard ini. Setelah diketahui, petakan tiap baris ke
     * Psn::updateOrCreate(['kode_rkp' => ...], [...] + ['sumber_input' =>
     * 'API PSI']), lalu kembalikan jumlah baris yang diproses.
     */
    protected function terapkanData(array $payload): int
    {
        return count($payload);
    }

    protected function catatHasil(string $status, int $jumlah, ?string $catatan): array
    {
        SyncLogPsi::create([
            'tanggal_sync' => now(),
            'jumlah_psn_diterima' => $jumlah,
            'status' => $status,
            'catatan' => $catatan,
        ]);

        return ['status' => $status, 'jumlah' => $jumlah, 'catatan' => $catatan];
    }
}
