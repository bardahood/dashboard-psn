<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Mengimpor "Matrik Sandingan Data PSN" (format resmi Tim Koordinasi
 * Perencanaan dan Pengendalian PSN) ke tabel inti psn beserta tabel
 * normalisasinya (psn_penanggung_jawab, psn_sumber_data, psn_ketersediaan).
 *
 * Sheet "Matrik" berformat wide (4 kolom sumber jadi kolom terpisah,
 * lokasi berupa narasi bebas kadang multi-provinsi) sehingga perlu
 * dinormalisasi ke bentuk skema. Data sumber PSN tidak lengkap/konsisten
 * (beberapa baris hanya "Permenko" tanpa K/L, klaster, atau lokasi) --
 * ini dibiarkan apa adanya (null) sesuai prinsip validasi longgar pada
 * prompt pengembangan, bukan bug pada importer.
 *
 * Sejak pembaruan 17 Sept 2026, kolom K/L Matrik tidak lagi berisi 1
 * "Ketersediaan Data" (Ada/Tidak Ada + Keterangan) melainkan 2 dimensi
 * terpisah: "Data Gambaran Umum Proyek" dan "Data Project Profile Lengkap
 * untuk Kebutuhan Evaluasi" (masing-masing Ada/Tidak Ada) -- disimpan
 * sebagai 2 baris psn_ketersediaan per PSN dibedakan `jenis_ketersediaan`.
 */
class MatriksSandinganImporter
{
    private const SUMBER_KOLOM = [
        'G' => 'RKP Pemutakhiran 2026 (Perpres 68)',
        'H' => 'Data PEKS3',
        'I' => 'Data PSI',
        'J' => 'Permenko',
    ];

    /** Provinsi/wilayah yang secara khusus dikenal tapi tidak persis cocok nama ref_provinsi. */
    private const ALIAS_KHUSUS = [
        'ibu kota nusantara' => 'Kalimantan Timur',
    ];

    private array $klasterMap = [];

    private array $provinsiMap = [];

    private array $sumberDataMap = [];

    private array $statusKetersediaanMap = [];

    private array $instansiCache = [];

    public function import(string $path, string $periode): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('Matrik') ?? $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestRow();

        $this->muatPeta();

        $jumlahPsn = 0;
        $jumlahPenanggungJawab = 0;
        $jumlahSumberData = 0;
        $jumlahKetersediaan = 0;
        $jumlahDilewati = 0;

        DB::transaction(function () use (
            $sheet, $highestRow, $periode,
            &$jumlahPsn, &$jumlahPenanggungJawab, &$jumlahSumberData, &$jumlahKetersediaan, &$jumlahDilewati
        ) {
            // Hapus data hasil import sebelumnya agar perintah ini idempotent.
            // ON DELETE CASCADE pada psn_penanggung_jawab/psn_sumber_data/psn_ketersediaan
            // ikut membersihkan baris terkait.
            DB::table('psn')->delete();

            for ($row = 3; $row <= $highestRow; $row++) {
                $no = trim((string) $sheet->getCell("A{$row}")->getFormattedValue());
                $namaPsn = trim((string) $sheet->getCell("B{$row}")->getFormattedValue());

                if ($no === '' && $namaPsn === '') {
                    continue;
                }

                if ($namaPsn === '') {
                    $jumlahDilewati++;

                    continue;
                }

                $klPenanggungjawab = trim((string) $sheet->getCell("C{$row}")->getFormattedValue());
                $klaster = trim((string) $sheet->getCell("D{$row}")->getFormattedValue());
                $lokasiProv = trim((string) $sheet->getCell("E{$row}")->getFormattedValue());
                $lokasiKab = trim((string) $sheet->getCell("F{$row}")->getFormattedValue());
                $ketersediaanGambaranUmum = trim((string) $sheet->getCell('K'.$row)->getFormattedValue());
                $ketersediaanProfileLengkap = trim((string) $sheet->getCell('L'.$row)->getFormattedValue());

                $provinsiId = $this->resolveProvinsi($lokasiProv);

                if ($lokasiKab === '' && $provinsiId === null && $lokasiProv !== '' && mb_strlen($lokasiProv) <= 150) {
                    // Simpan narasi lokasi apa adanya sebagai fallback agar informasinya
                    // tidak hilang, meski tidak berhasil dipetakan ke provinsi baku.
                    $lokasiKab = $lokasiProv;
                }

                $psnId = DB::table('psn')->insertGetId([
                    'nama_psn' => $namaPsn,
                    'klaster_id' => $this->klasterMap[mb_strtolower($klaster)] ?? null,
                    'provinsi_id' => $provinsiId,
                    'kabupaten_kota' => $lokasiKab !== '' ? mb_substr($lokasiKab, 0, 150) : null,
                    'sumber_input' => 'Manual',
                    'periode_update' => $periode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $jumlahPsn++;

                foreach ($this->pecahInstansi($klPenanggungjawab) as $namaInstansi) {
                    $instansiId = $this->findOrCreateInstansi($namaInstansi);
                    DB::table('psn_penanggung_jawab')->insertOrIgnore([
                        'psn_id' => $psnId,
                        'instansi_id' => $instansiId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $jumlahPenanggungJawab++;
                }

                foreach (self::SUMBER_KOLOM as $kolom => $namaSumber) {
                    $nilai = trim((string) $sheet->getCell($kolom.$row)->getFormattedValue());
                    DB::table('psn_sumber_data')->insert([
                        'psn_id' => $psnId,
                        'sumber_data_id' => $this->sumberDataMap[$namaSumber],
                        'tersedia' => $nilai === '√',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $jumlahSumberData++;
                }

                foreach ([
                    'Gambaran Umum' => $ketersediaanGambaranUmum,
                    'Project Profile Lengkap' => $ketersediaanProfileLengkap,
                ] as $jenis => $nilai) {
                    $statusId = $this->statusKetersediaanMap[mb_strtolower($nilai)] ?? null;

                    if ($statusId === null) {
                        continue;
                    }

                    DB::table('psn_ketersediaan')->insert([
                        'psn_id' => $psnId,
                        'status_ketersediaan_id' => $statusId,
                        'jenis_ketersediaan' => $jenis,
                        'keterangan' => null,
                        'periode_pemutakhiran' => $periode,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $jumlahKetersediaan++;
                }
            }
        });

        return [
            'psn' => $jumlahPsn,
            'penanggung_jawab' => $jumlahPenanggungJawab,
            'sumber_data' => $jumlahSumberData,
            'ketersediaan' => $jumlahKetersediaan,
            'dilewati' => $jumlahDilewati,
        ];
    }

    /**
     * Isi psn.kode_rkp (+ peks/unit_kerja sejak pembaruan 24 Sept 2026) dari
     * "Master Data PSN Kode" (Kode_PSI + PSN [+ Nama PSN + Peks + Unit_Kerja]),
     * dicocokkan lewat nama_psn persis sama (kedua file terbukti selaras
     * baris-demi-baris pada sumber 17 Sept 2026 -- dicocokkan lewat nama,
     * bukan urutan baris, agar tahan bila urutan berubah pada pembaruan
     * berikutnya). Kolom B ("PSN") dipakai untuk pencocokan, BUKAN kolom C
     * ("Nama PSN") yang ditemukan sudah terpotong (truncated) pada baris
     * dengan nama sangat panjang di pembaruan 24 Sept 2026 -- pakai kolom C
     * untuk pencocokan akan gagal mencocokkan baris-baris tsb ke psn.nama_psn
     * yang tersimpan lengkap.
     */
    public function importKodeRkp(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestRow();

        $jumlahCocok = 0;
        $jumlahTidakCocok = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $kode = trim((string) $sheet->getCell('A'.$row)->getFormattedValue());
            $namaPsn = trim((string) $sheet->getCell('B'.$row)->getFormattedValue());

            if ($kode === '' || $namaPsn === '') {
                continue;
            }

            $peks = trim((string) $sheet->getCell('D'.$row)->getFormattedValue());
            $unitKerja = trim((string) $sheet->getCell('E'.$row)->getFormattedValue());

            $terupdate = DB::table('psn')->where('nama_psn', $namaPsn)->update([
                'kode_rkp' => $kode,
                'peks' => $peks !== '' ? $peks : null,
                'unit_kerja' => $unitKerja !== '' ? $unitKerja : null,
            ]);

            if ($terupdate > 0) {
                $jumlahCocok++;
            } else {
                $jumlahTidakCocok++;
            }
        }

        return ['cocok' => $jumlahCocok, 'tidak_cocok' => $jumlahTidakCocok];
    }

    private function muatPeta(): void
    {
        $this->klasterMap = DB::table('ref_klaster')->pluck('id', 'nama_klaster')
            ->mapWithKeys(fn ($id, $nama) => [mb_strtolower($nama) => $id])->all();

        $this->provinsiMap = DB::table('ref_provinsi')->pluck('id', 'nama_provinsi')
            ->mapWithKeys(fn ($id, $nama) => [mb_strtolower($nama) => $id])->all();

        $this->sumberDataMap = DB::table('ref_sumber_data')->pluck('id', 'nama_sumber')->all();

        $this->statusKetersediaanMap = DB::table('ref_status_ketersediaan')->pluck('id', 'nama_status')
            ->mapWithKeys(fn ($id, $nama) => [mb_strtolower($nama) => $id])->all();
    }

    /**
     * Kolom Lokasi_Prov berisi narasi bebas: satu provinsi, beberapa provinsi
     * dipisah "dan"/","/"-", atau kadang bukan nama provinsi sama sekali.
     * Diambil kecocokan pertama sebagai lokasi utama (loose matching, sesuai
     * prinsip validasi longgar pada prompt pengembangan).
     */
    private function resolveProvinsi(string $raw): ?int
    {
        if ($raw === '') {
            return null;
        }

        $aliasKhusus = self::ALIAS_KHUSUS[mb_strtolower($raw)] ?? null;
        if ($aliasKhusus !== null) {
            return $this->provinsiMap[mb_strtolower($aliasKhusus)] ?? null;
        }

        $tokens = preg_split('/,|\bdan\b|-|–/u', $raw) ?: [$raw];

        foreach ($tokens as $token) {
            $normal = $this->normalisasiTokenProvinsi($token);
            if ($normal !== '' && isset($this->provinsiMap[mb_strtolower($normal)])) {
                return $this->provinsiMap[mb_strtolower($normal)];
            }
        }

        return null;
    }

    private function normalisasiTokenProvinsi(string $token): string
    {
        $token = trim($token);
        $token = preg_replace('/^Provinsi\s+/iu', '', $token) ?? $token;
        $token = str_ireplace(
            ['D.I. Yogyakarta', 'Daerah Istimewa Yogyakarta'],
            'DI Yogyakarta',
            $token
        );
        $token = str_ireplace('Daerah Khusus Ibukota Jakarta', 'DKI Jakarta', $token);

        return trim($token);
    }

    /**
     * Kolom K_L_Penanggungjawab kadang berisi lebih dari satu K/L dipisah
     * "dan" (mis. "Menteri Sosial dan Menteri Pekerjaan Umum"). Tanda "/"
     * dibiarkan utuh karena itu jabatan rangkap satu pejabat yang sama
     * (mis. "Menteri Lingkungan Hidup/Kepala Badan Pengendalian Lingkungan Hidup").
     *
     * @return array<int, string>
     */
    private function pecahInstansi(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $bagian = preg_split('/\bdan\b/iu', $raw) ?: [$raw];

        return collect($bagian)
            ->map(fn ($v) => trim($v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function findOrCreateInstansi(string $nama): int
    {
        $key = mb_strtolower($nama);

        if (isset($this->instansiCache[$key])) {
            return $this->instansiCache[$key];
        }

        $id = DB::table('ref_instansi')->where('nama_instansi', $nama)->value('id');

        if (! $id) {
            $id = DB::table('ref_instansi')->insertGetId([
                'nama_instansi' => $nama,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $this->instansiCache[$key] = $id;
    }
}
