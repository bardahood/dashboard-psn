<?php

namespace App\Support;

use App\Models\Psn;
use App\Models\RefInstansi;
use App\Models\RefRoKrisna;
use App\Models\RoProyek;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Impor katalog RO/Output resmi dari sistem Krisna (lampiran laporan_PSN.xlsx)
 * ke ref_ro_krisna, ditautkan ke PSN lewat pencocokan nama (Risalah Rapat 21
 * Sept 2026: "RO pilihannya dropdown, pilihan ditarik dari krisna"). Opsional
 * diperkaya dengan jalur PN/PP/KP/ProP dari matrix_pembangunan_rkp2026.xlsx
 * (matriks nasional, lihat catatan batasan di pengayaanMatrixRkp()).
 */
class LaporanPsnImporter
{
    public const SKOR_AMBANG_COCOK = 0.55;

    /**
     * Kata generik yang sering muncul di banyak nama proyek pemerintah tanpa
     * membedakan proyek satu dari lainnya -- dibuang sebelum skoring supaya
     * dua proyek yang sama-sama diawali "Pembangunan Jaringan ..." atau
     * "Pembangunan Infrastruktur ..." tidak salah dianggap mirip hanya
     * karena kata pembukanya sama (ditemukan sebagai false-positive nyata
     * saat pengembangan: "Pembangunan Jaringan Gas Kota" sempat tertaut ke
     * PSN "Pembangunan Jaringan Irigasi ... Lematang").
     */
    private const STOPWORDS = [
        'pembangunan', 'proyek', 'program', 'pengembangan', 'peningkatan',
        'penyediaan', 'penguatan', 'infrastruktur', 'sarana', 'prasarana',
        'fasilitas', 'jaringan', 'nasional', 'daerah', 'di', 'dan', 'dengan',
        'untuk', 'dari', 'ke', 'yang', 'pada', 'provinsi',
    ];

    /** Cache instance (BUKAN `static`) untuk cariInstansiCocok(). */
    private ?Collection $instansiCache = null;

    /**
     * @return array{baris_diimpor: int, baris_tertaut_psn: int, baris_tidak_tertaut: int}
     */
    public function importKatalogRo(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $baris = $sheet->toArray(null, true, true, false);
        $header = array_map('trim', array_shift($baris));

        $daftarPsn = Psn::query()->select('id', 'nama_psn')->get();

        $tertaut = 0;
        $tidakTertaut = 0;
        $diimpor = 0;
        $sudahDiimpor = [];

        foreach ($baris as $row) {
            $data = array_combine($header, $row);
            $kunciUnik = implode('|', $row);
            if (isset($sudahDiimpor[$kunciUnik])) {
                continue; // baris duplikat identik pada sumber, lewati
            }
            $sudahDiimpor[$kunciUnik] = true;

            if (empty($data['project_psn'])) {
                continue;
            }

            [$psn, $skor] = $this->cariPsnTerbaik((string) $data['project_psn'], $daftarPsn);
            $psn && $skor >= self::SKOR_AMBANG_COCOK ? $tertaut++ : $tidakTertaut++;

            RefRoKrisna::create([
                'sektor_psn' => $data['sektor_psn'] ?? null,
                'project_psn' => $data['project_psn'] ?? null,
                'project_rkp' => $data['project_rkp'] ?? null,
                'kementerian' => $data['kementerian'] ?? null,
                'program' => $data['program'] ?? null,
                'kegiatan' => $data['kegiatan'] ?? null,
                'kro' => $data['kro'] ?? null,
                'ro' => $data['ro'] ?? null,
                'lokasi_ro' => $data['lokasi_ro'] ?? null,
                'volume' => $this->keAngka($data['volume'] ?? null),
                'satuan' => $data['satuan'] ?? null,
                'alokasi' => $this->keAngka($data['alokasi'] ?? null),
                'pn' => $data['pn'] ?? null,
                'pp' => $data['pp'] ?? null,
                'kp' => $data['kp'] ?? null,
                'prop' => $data['ppn'] ?? null,
                'psn_id' => ($psn && $skor >= self::SKOR_AMBANG_COCOK) ? $psn->id : null,
            ]);
            $diimpor++;
        }

        return [
            'baris_diimpor' => $diimpor,
            'baris_tertaut_psn' => $tertaut,
            'baris_tidak_tertaut' => $tidakTertaut,
        ];
    }

    /**
     * Impor "Hasil Sandingan Laporan PSN dan Matrix Pembangunan RKP 2026" --
     * berbeda dari importKatalogRo()/pengayaanMatrixRkp() di atas, file ini
     * SUDAH berisi hasil pencocokan manual antara laporan_PSN.xlsx dan
     * matrix_pembangunan_rkp2026.xlsx (kolom nama_psn bersih tanpa kode
     * prefiks, dan kode_output_or_ro sudah berupa kode hierarkis RKP
     * langsung per baris, bukan hasil rekonstruksi "Kode Source"). Karena
     * nama PSN-nya sudah bersih, penautan psn_id di sini pakai pencocokan
     * ketat (persis atau prefiks setelah normalisasi spasi/kapital) --
     * BUKAN fuzzy scorer di cariPsnTerbaik() -- supaya tidak mengulang
     * risiko false-positive yang pernah ditemukan pada importKatalogRo().
     *
     * Baris yang sama persis dengan katalog yang sudah ada (psn_id + ro +
     * lokasi setelah normalisasi) TIDAK membuat baris baru -- tapi kalau
     * baris lama itu belum punya kode RKP (prop_kode_rkp masih kosong,
     * krn pengayaanMatrixRkp() berbasis rekonstruksi "Kode Source" hanya
     * berhasil untuk sebagian baris), baris lama itu DIPERKAYA dengan kode
     * dari sumber sandingan ini (sudah langsung tersedia per baris di sini,
     * tidak perlu rekonstruksi) -- existing-first, tidak pernah menimpa
     * kode yang sudah terisi. Lokasi WAJIB ikut jadi kunci dedup -- RO
     * bernama sama (mis. "Pelatihan Pertanian bagi Non Aparatur") lazim
     * muncul berkali-kali dengan lokasi kabupaten/provinsi berbeda di
     * sumber ini, dan itu adalah entri katalog yang berbeda (bukan baris
     * duplikat) -- dedup hanya berdasar nama RO saja sempat salah membuang
     * seluruh varian lokasi ini, ditemukan & diperbaiki saat verifikasi.
     * File ini tidak punya kolom volume/satuan/kementerian (beda struktur
     * dari laporan_PSN.xlsx) sehingga field itu dibiarkan kosong pada baris
     * BARU yang diimpor dari sini -- field lain (Nama RO dropdown, kode
     * RKP, lokasi) tetap terisi penuh.
     *
     * @return array{baris_diimpor: int, baris_dilewati_duplikat: int, baris_diperkaya_kode: int, psn_tertaut: int, psn_tidak_ditemukan: array<string>}
     */
    public function importHasilSandingan(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $baris = $sheet->toArray(null, true, true, false);
        $header = array_map('trim', array_shift($baris));

        $daftarPsn = Psn::query()->select('id', 'nama_psn')->get();

        // Baris lama dari importKatalogRo() menyimpan `ro` dengan kode depan
        // (mis. "001-Bantuan Tenaga Kerja Mandiri..."), sedangkan sumber
        // sandingan ini sudah bersih tanpa kode -- kode itu harus dibuang
        // dulu sebelum dibandingkan, kalau tidak dedup gagal mendeteksi
        // baris yang sebenarnya sama (ditemukan saat verifikasi manual).
        // Nilai peta = id baris (dipakai utk pengayaan kode RKP di bawah).
        $roTersimpan = [];
        foreach (RefRoKrisna::query()->whereNotNull('psn_id')->get(['id', 'psn_id', 'ro', 'lokasi_ro', 'prop_kode_rkp']) as $r) {
            $roTersimpan[$this->kunciDedupRo($r->psn_id, (string) $r->ro, (string) $r->lokasi_ro)] = $r;
        }

        $diimpor = 0;
        $dilewatiDuplikat = 0;
        $diperkayaKode = 0;
        $psnTertautUnik = [];
        $psnTidakDitemukan = [];

        foreach ($baris as $row) {
            $data = array_combine($header, $row);
            $namaPsn = trim((string) ($data['nama_psn'] ?? ''));
            if ($namaPsn === '') {
                continue;
            }

            $psn = $this->cariPsnKetat($namaPsn, $daftarPsn);
            if (! $psn) {
                $psnTidakDitemukan[$namaPsn] = true;

                continue;
            }
            $psnTertautUnik[$psn->id] = true;

            $ro = trim((string) ($data['ro'] ?? ''));
            if ($ro === '') {
                continue;
            }

            $kodeRkp = $data['kode_output_or_ro'] ?? null;
            $kunciDedup = $this->kunciDedupRo($psn->id, $ro, (string) ($data['lokasi_ro'] ?? ''));

            if (isset($roTersimpan[$kunciDedup])) {
                $dilewatiDuplikat++;
                $existing = $roTersimpan[$kunciDedup];
                if (empty($existing->prop_kode_rkp) && ! empty($kodeRkp)) {
                    $existing->update(['prop_kode_rkp' => $kodeRkp]);
                    $diperkayaKode++;
                }

                continue;
            }
            $roTersimpan[$kunciDedup] = (object) ['prop_kode_rkp' => $kodeRkp];

            RefRoKrisna::create([
                'sektor_psn' => $data['sektor_psn'] ?? null,
                'project_psn' => $namaPsn,
                'project_rkp' => $ro,
                'ro' => $ro,
                'lokasi_ro' => $data['lokasi_ro'] ?? null,
                'prop' => $data['ppn'] ?? null,
                'prop_kode_rkp' => $kodeRkp,
                'psn_id' => $psn->id,
            ]);
            $diimpor++;
        }

        return [
            'baris_diimpor' => $diimpor,
            'baris_dilewati_duplikat' => $dilewatiDuplikat,
            'baris_diperkaya_kode' => $diperkayaKode,
            'psn_tertaut' => count($psnTertautUnik),
            'psn_tidak_ditemukan' => array_keys($psnTidakDitemukan),
        ];
    }

    /**
     * Pencocokan ketat nama_psn (BUKAN fuzzy scorer) -- dipakai khusus oleh
     * importHasilSandingan() karena sumbernya sudah berisi nama PSN bersih.
     * Coba persis dulu, lalu persis setelah normalisasi spasi/kapital/tanda
     * hubung, lalu sebagai upaya terakhir: prefiks (nama sumber adalah awal
     * dari nama_psn asli, mis. PSN nasional yang di sumber ditulis tanpa
     * daftar provinsi di belakangnya) -- dengan batas panjang minimum supaya
     * tidak salah cocok ke prefiks pendek yang kebetulan sama.
     */
    private function cariPsnKetat(string $namaSumber, Collection $daftarPsn): ?Psn
    {
        $exact = $daftarPsn->first(fn (Psn $p) => $p->nama_psn === $namaSumber);
        if ($exact) {
            return $exact;
        }

        $normSumber = $this->normalisasiRingan($namaSumber);
        $normMatch = $daftarPsn->first(fn (Psn $p) => $this->normalisasiRingan($p->nama_psn) === $normSumber);
        if ($normMatch) {
            return $normMatch;
        }

        if (mb_strlen($normSumber) < 15) {
            return null;
        }

        return $daftarPsn->first(fn (Psn $p) => str_starts_with($this->normalisasiRingan($p->nama_psn), $normSumber));
    }

    /** Kunci dedup RO: psn + nama RO (kode depan dibuang) + lokasi, semua dinormalisasi. */
    private function kunciDedupRo(int $psnId, string $ro, string $lokasi): string
    {
        return $psnId.'|'.$this->normalisasi($this->buangKodeAwal($ro)).'|'.$this->normalisasiRingan($lokasi);
    }

    /** Normalisasi ringan (bukan stopword-stripping skorKemiripan()): rapikan spasi/tanda hubung & kapital saja, urutan kata tetap dipertahankan. */
    private function normalisasiRingan(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = preg_replace('/\s*-\s*/', '-', $s);

        return preg_replace('/\s+/', ' ', $s);
    }

    /**
     * Pengayaan jalur PN/PP/KP/ProP dari matriks nasional RKP 2026.
     *
     * BATASAN: matrix_pembangunan_rkp2026.xlsx mencakup ~10.800 baris seluruh
     * RKP nasional (bukan spesifik PSN) berisi 3 tingkat: baris PN/PP/KP/ProP
     * (kode hierarkis, mis. "01.01.01.01") dan baris "Output" (kode sumber
     * administratif dalam teks "Kode Source: <kementerian>.<program>.
     * <kegiatan>.<kro>.<ro>.<output>"). Kode sumber pada baris Output inilah
     * yang persis merekonstruksi kolom kegiatan/kro/ro/project_rkp di
     * laporan_PSN.xlsx, sehingga dipakai sebagai kunci penaut baris demi
     * baris -- BUKAN mengimpor seluruh matriks (yang sebagian besar tidak
     * relevan untuk 380 PSN yang dilacak dashboard ini dan tidak dibutuhkan
     * langsung oleh kebutuhan Risalah Rapat).
     *
     * @return array{baris_diperkaya: int}
     */
    public function pengayaanMatrixRkp(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $baris = $sheet->toArray(null, true, true, false);
        array_shift($baris); // header

        // Tahap 1: indeks kode ProP -> [nama, alokasi] dari baris marker ProP.
        $propByKode = [];
        foreach ($baris as $row) {
            if (($row[0] ?? null) === 'PRO-P' && ! empty($row[1])) {
                $propByKode[$row[1]] = ['nama' => $row[2] ?? null, 'alokasi' => $this->keAngka($row[9] ?? null)];
            }
        }

        // Tahap 2: indeks katalog Krisna kita sendiri by (kegiatan_kode, kro_kode, ro_kode, output_kode)
        // supaya baris Output di matriks bisa dicari lawannya dalam O(1).
        $krisnaByKode = [];
        foreach (RefRoKrisna::query()->select('id', 'kegiatan', 'kro', 'ro', 'project_rkp')->cursor() as $k) {
            $kunci = implode('.', [
                $this->ambilKodeAwal((string) $k->kegiatan),
                $this->ambilKodeAwal((string) $k->kro),
                $this->ambilKodeAwal((string) $k->ro),
                $this->ambilKodeAwal((string) $k->project_rkp),
            ]);
            $krisnaByKode[$kunci] ??= [];
            $krisnaByKode[$kunci][] = $k->id;
        }

        $diperkaya = 0;
        foreach ($baris as $row) {
            if (($row[0] ?? null) !== 'Output' || empty($row[1]) || empty($row[3])) {
                continue;
            }

            // row[3] mis. "Kode Source: 145.GA.7696.RBC.001.3903"
            if (! preg_match('/Kode Source:\s*([\w.]+)/i', (string) $row[3], $m)) {
                continue;
            }
            $segmen = explode('.', $m[1]);
            if (count($segmen) < 6) {
                continue;
            }
            [, $programKode, $kegiatanKode, $kroKode, $roKode, $outputKode] = $segmen;
            $kunci = "{$kegiatanKode}.{$kroKode}.{$roKode}.{$outputKode}";

            if (empty($krisnaByKode[$kunci])) {
                continue;
            }

            $kodeSendiri = (string) $row[1]; // mis. "02.12.09.06.3903"
            $segmenSendiri = explode('.', $kodeSendiri);
            $propKode = implode('.', array_slice($segmenSendiri, 0, 4));
            $prop = $propByKode[$propKode] ?? null;

            RefRoKrisna::whereIn('id', $krisnaByKode[$kunci])->update([
                'prop_kode_rkp' => $propKode,
                'nama_prop_rkp' => $prop['nama'] ?? null,
                'alokasi_prop' => $prop['alokasi'] ?? null,
            ]);
            $diperkaya += count($krisnaByKode[$kunci]);
        }

        return ['baris_diperkaya' => $diperkaya];
    }

    /**
     * Isi RO/Proyek (ro_proyek) dari katalog Krisna yang sudah tertaut ke PSN
     * -- HANYA untuk PSN yang profil RO/Proyek-nya masih kosong sama sekali
     * (existing-first: tidak pernah menimpa data yang sudah diisi manual oleh
     * admin). Contoh konkret dari Risalah Rapat 21 Sept 2026: "Contoh PSN
     * jalan tol wajib terisi progress per-ruas jalan di bagian RO/kegiatan
     * karena sudah ada datanya" -- satu baris Krisna per ruas/segmen jadi
     * satu baris RO (tipe RO, bukan Aktivitas, karena masing-masing memang
     * berdiri sendiri sebagai unit pekerjaan, bukan turunan dari RO lain).
     *
     * @return array{psn_diisi: int, ro_dibuat: int}
     */
    public function seedRoProyekDariKatalog(): array
    {
        $psnDiisi = 0;
        $roDibuat = 0;

        $krisnaPerPsn = RefRoKrisna::whereNotNull('psn_id')->get()->groupBy('psn_id');

        foreach ($krisnaPerPsn as $psnId => $rows) {
            if (RoProyek::where('psn_id', $psnId)->exists()) {
                continue; // sudah ada data RO manual, jangan ditimpa
            }

            // Dedup (nama_ro, lokasi_ro): satu ruas/segmen Krisna bisa terhitung
            // berkali-kali pada baris berbeda karena tertaut ke lebih dari satu
            // klasifikasi PN/PP/KP -- cukup satu baris RO per pasangan unik.
            $unik = $rows->unique(fn (RefRoKrisna $r) => $this->buangKodeAwal((string) $r->project_rkp).'|'.$r->lokasi_ro);

            $dibuatUntukPsnIni = 0;
            foreach ($unik as $k) {
                $nama = $this->buangKodeAwal((string) $k->project_rkp) ?: (string) $k->ro;
                if ($nama === '') {
                    continue;
                }

                RoProyek::create([
                    'psn_id' => $psnId,
                    'nama_ro' => $nama,
                    'tipe' => 'RO',
                    'satuan' => $k->satuan,
                    'target_akhir' => $k->volume !== null ? $this->formatAngka((string) $k->volume) : null,
                    'lokasi' => $this->ringkasLokasi((string) $k->lokasi_ro),
                    'instansi_pelaksana_id' => $this->cariInstansiCocok((string) $k->kementerian)?->id,
                ]);
                $roDibuat++;
                $dibuatUntukPsnIni++;
            }

            if ($dibuatUntukPsnIni > 0) {
                $psnDiisi++;
            }
        }

        return ['psn_diisi' => $psnDiisi, 'ro_dibuat' => $roDibuat];
    }

    /**
     * ro_proyek.lokasi adalah varchar(255) untuk satu lokasi ringkas -- sejumlah
     * RO Krisna berskala nasional mencantumkan puluhan nama provinsi sekaligus
     * dipisah koma, yang jelas tidak muat dan juga bukan representasi "lokasi"
     * yang berguna. Diringkas jadi jumlah lokasi bila daftarnya sangat panjang.
     */
    private function ringkasLokasi(string $lokasiRo): ?string
    {
        if ($lokasiRo === '') {
            return null;
        }
        if (mb_strlen($lokasiRo) <= 250) {
            return $lokasiRo;
        }

        $jumlah = count(array_filter(explode(',', $lokasiRo)));

        return "Multi-lokasi ({$jumlah} lokasi)";
    }

    private function formatAngka(string $decimalString): string
    {
        return rtrim(rtrim($decimalString, '0'), '.') ?: '0';
    }

    private function cariInstansiCocok(string $kementerian): ?RefInstansi
    {
        if ($kementerian === '') {
            return null;
        }

        // Cache pada properti objek (BUKAN `static` lokal) -- `static` bertahan
        // lintas instance/test dalam proses PHP yang sama dan pernah terbukti
        // menyimpan ID basi dari transaksi test sebelumnya yang sudah di-rollback.
        $this->instansiCache ??= RefInstansi::all();

        // "KEMENTERIAN PEKERJAAN UMUM" (Krisna) vs "Menteri Pekerjaan Umum"
        // (ref_instansi) -- normalisasi kedua sisi sebelum dibandingkan.
        $bersih = fn (string $s) => trim(preg_replace('/^(kementerian|menteri)\s+/i', '', $s));
        $target = mb_strtolower($bersih($kementerian));

        return $this->instansiCache->first(fn ($i) => mb_strtolower($bersih($i->nama_instansi)) === $target);
    }

    private function ambilKodeAwal(string $teks): string
    {
        return trim(explode('-', $teks, 2)[0] ?? '');
    }

    /** Kebalikan dari ambilKodeAwal(): buang kode di depan, sisakan namanya. */
    private function buangKodeAwal(string $teks): string
    {
        return trim(preg_replace('/^[\w.]+-\s*/', '', $teks, 1));
    }

    private function keAngka(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }

        return is_numeric($v) ? (float) $v : null;
    }

    /**
     * @return array{0: ?Psn, 1: float}
     */
    private function cariPsnTerbaik(string $projectPsn, Collection $daftarPsn): array
    {
        // Krisna menamai project dengan format "KODE-Nama" (mis. "G10-Jalan Tol
        // Semarang - Demak"); kadang juga "KODE-Program: Sub-judul" di mana
        // sub-judul lebih dekat ke nama_psn kita dibanding judul program penuh.
        $nama = preg_replace('/^[A-Z]\d+-/', '', $projectPsn);
        $kandidat = [$nama];
        if (str_contains($nama, ':')) {
            $kandidat[] = trim(explode(':', $nama, 2)[1]);
        }

        $terbaik = null;
        $skorTerbaik = 0.0;

        foreach ($kandidat as $k) {
            foreach ($daftarPsn as $psn) {
                $skor = $this->skorKemiripan($k, $psn->nama_psn);
                if ($skor > $skorTerbaik) {
                    $skorTerbaik = $skor;
                    $terbaik = $psn;
                }
            }
        }

        return [$terbaik, round($skorTerbaik, 3)];
    }

    private function skorKemiripan(string $a, string $b): float
    {
        $normA = $this->normalisasi($a);
        $normB = $this->normalisasi($b);

        if ($normA === '' || $normB === '') {
            return 0.0;
        }

        if ($normA === $normB) {
            return 1.0;
        }

        $tokenA = array_filter(explode(' ', $normA));
        $tokenB = array_filter(explode(' ', $normB));

        // Setelah kata generik dibuang, string 1 kata (mis. "ketenagalistrikan"
        // saja) terlalu lemah untuk dipercaya -- similar_text bisa memberi
        // skor tinggi semu terhadap kata pendek tak terkait (ditemukan
        // sebagai false-positive nyata: "ketenagalistrikan" vs "peternakan").
        if (count($tokenA) < 2 || count($tokenB) < 2) {
            return 0.0;
        }

        similar_text($normA, $normB, $persen);
        $rasioTeks = $persen / 100;
        $irisan = array_intersect($tokenA, $tokenB);
        $gabungan = array_unique(array_merge($tokenA, $tokenB));
        $jaccard = count($gabungan) > 0 ? count($irisan) / count($gabungan) : 0.0;

        return max($rasioTeks, $jaccard);
    }

    private function normalisasi(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);

        $token = array_filter(explode(' ', trim($s)), fn ($t) => $t !== '' && ! in_array($t, self::STOPWORDS, true));

        return implode(' ', $token);
    }
}
