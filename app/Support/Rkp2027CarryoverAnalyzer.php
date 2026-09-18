<?php

namespace App\Support;

use App\Models\Psn;
use Illuminate\Support\Collection;

/**
 * Menyandingkan daftar PSN pada lampiran "Daftar PSN dalam RKP 2027" dengan
 * data PSN yang sudah ada di dashboard (hasil impor Matrik Sandingan PSN,
 * bersumber dari RKP Pemutakhiran 2026) untuk mengidentifikasi proyek
 * carryover (berlanjut ke 2027) vs proyek yang perlu ditinjau manual
 * (kemungkinan usulan baru, atau berganti nama/redaksional dari 2026).
 *
 * Pencocokan nama proyek bersifat fuzzy (bukan exact match) karena redaksi
 * nama antara dokumen RKP dan Matrik Sandingan sering berbeda kata namun
 * merujuk proyek yang sama. Ambang batas skor ditentukan secara empiris
 * dari sampel data (skor >= 0.55 terbukti akurat berdasarkan tinjauan
 * manual saat pengembangan fitur ini).
 */
class Rkp2027CarryoverAnalyzer
{
    public const SKOR_AMBANG_COCOK = 0.55;

    /** @var array<string> */
    private const STOPWORDS = [
        'pembangunan', 'proyek', 'program', 'pengembangan', 'peningkatan',
        'di', 'dan', 'dengan', 'untuk', 'dari', 'ke', 'yang', 'pada',
    ];

    /** @var array<string> */
    private const PREFIX_BUKAN_JUDUL = [
        'PSN', 'Proyek-Proyek', 'Di samping', 'Terdapat', '*)', 'Daftar', 'Dalam rangka',
    ];

    public function __construct(private readonly DocxTableParser $parser)
    {
    }

    /**
     * @return array<int, array{klaster: ?string, group: ?string, nama_proyek: string, lokasi: string, is_group: bool}>
     */
    public function parseDaftarRkp(string $path): array
    {
        $blocks = $this->parser->parseBlocks($path);

        $heading = null;
        $records = [];

        foreach ($blocks as $block) {
            if ($block['type'] === 'paragraph') {
                $text = trim($block['text']);
                if ($text !== '' && mb_strlen($text) <= 90 && ! $this->startsWithAny($text, self::PREFIX_BUKAN_JUDUL)) {
                    $heading = $text;
                }

                continue;
            }

            $currentGroup = null;
            foreach ($block['rows'] as $i => $row) {
                if ($i === 0) {
                    continue; // baris header tabel
                }

                $collapsed = $this->collapseConsecutive($row);
                $adaIsi = count(array_filter($collapsed, fn ($v) => $v !== '')) > 0;
                if (! $adaIsi) {
                    continue;
                }

                if (count($collapsed) === 1) {
                    $currentGroup = $collapsed[0];
                    $records[] = [
                        'klaster' => $heading,
                        'group' => null,
                        'nama_proyek' => $currentGroup,
                        'lokasi' => '',
                        'is_group' => true,
                    ];

                    continue;
                }

                $idx0 = (preg_match('/^\d+\.?$/', $collapsed[0]) === 1 || $collapsed[0] === '') ? 1 : 0;
                $rest = array_slice($collapsed, $idx0);

                if (empty($rest) || $rest[0] === '') {
                    continue;
                }

                $records[] = [
                    'klaster' => $heading,
                    'group' => $currentGroup,
                    'nama_proyek' => $rest[0],
                    'lokasi' => $rest[1] ?? '',
                    'is_group' => false,
                ];
            }
        }

        return $records;
    }

    /**
     * Jalankan analisis lengkap: parse dokumen, cocokkan dengan data psn
     * yang ada, kelompokkan hasil ke 3 kategori.
     *
     * @return array{
     *     carryover: Collection<int, array>,
     *     perlu_ditinjau: Collection<int, array>,
     *     tidak_ditemukan_lagi: Collection<int, Psn>,
     * }
     */
    public function analisis(string $path): array
    {
        $records = collect($this->parseDaftarRkp($path))->reject(fn ($r) => $r['is_group']);

        $daftarPsn = Psn::with('klaster')->get(['id', 'nama_psn', 'klaster_id']);

        $hasilPencocokan = $records->map(function ($rec) use ($daftarPsn) {
            [$psn, $skor] = $this->cariKecocokanTerbaik($rec['nama_proyek'], $daftarPsn);

            return $rec + ['psn_id' => $psn?->id, 'psn_nama' => $psn?->nama_psn, 'skor' => $skor];
        });

        $carryover = $hasilPencocokan->filter(fn ($m) => $m['skor'] >= self::SKOR_AMBANG_COCOK);
        $perluDitinjau = $hasilPencocokan->filter(fn ($m) => $m['skor'] < self::SKOR_AMBANG_COCOK);

        $idTercocokkan = $carryover->pluck('psn_id')->filter()->unique();
        $tidakDitemukanLagi = $daftarPsn->reject(fn ($p) => $idTercocokkan->contains($p->id))->values();

        return [
            'carryover' => $carryover->values(),
            'perlu_ditinjau' => $perluDitinjau->values(),
            'tidak_ditemukan_lagi' => $tidakDitemukanLagi,
        ];
    }

    /**
     * Terapkan hasil analisis ke kolom psn.kategori_usulan (hanya untuk PSN
     * yang belum punya kategori_usulan, agar tidak menimpa isian manual).
     */
    public function terapkanKategoriCarryover(Collection $carryover): int
    {
        $idUnik = $carryover->pluck('psn_id')->filter()->unique();

        return Psn::whereIn('id', $idUnik)
            ->whereNull('kategori_usulan')
            ->update(['kategori_usulan' => 'Carryover']);
    }

    /**
     * @param  Collection<int, Psn>  $daftarPsn
     * @return array{0: ?Psn, 1: float}
     */
    private function cariKecocokanTerbaik(string $namaProyek, Collection $daftarPsn): array
    {
        $terbaik = null;
        $skorTerbaik = 0.0;

        foreach ($daftarPsn as $psn) {
            $skor = $this->skorKemiripan($namaProyek, $psn->nama_psn);
            if ($skor > $skorTerbaik) {
                $skorTerbaik = $skor;
                $terbaik = $psn;
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

        similar_text($normA, $normB, $persen);
        $rasioTeks = $persen / 100;

        $tokenA = array_filter(explode(' ', $normA));
        $tokenB = array_filter(explode(' ', $normB));
        $irisan = array_intersect($tokenA, $tokenB);
        $gabungan = array_unique(array_merge($tokenA, $tokenB));
        $jaccard = count($gabungan) > 0 ? count($irisan) / count($gabungan) : 0.0;

        return max($rasioTeks, $jaccard);
    }

    private function normalisasi(string $s): string
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/[^a-z0-9\s]/u', ' ', $s) ?? $s;
        $tokens = array_filter(preg_split('/\s+/', $s) ?: [], fn ($t) => $t !== '' && ! in_array($t, self::STOPWORDS, true));

        return implode(' ', $tokens);
    }

    private function collapseConsecutive(array $row): array
    {
        $result = [];
        $prev = null;
        $first = true;
        foreach ($row as $v) {
            if ($first || $v !== $prev) {
                $result[] = $v;
            }
            $prev = $v;
            $first = false;
        }

        return $result;
    }

    private function startsWithAny(string $text, array $prefixes): bool
    {
        foreach ($prefixes as $p) {
            if (str_starts_with($text, $p)) {
                return true;
            }
        }

        return false;
    }
}
