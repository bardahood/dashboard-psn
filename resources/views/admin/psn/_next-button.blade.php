@php
    // Urutan linear alur pengisian project profile (tanpa "Kunjungan
    // Pengendalian", itu bukan bagian dari alur pengisian, hanya tertaut
    // dari bar navigasi). Dipertahankan sinkron manual dengan
    // _profil-tabs.blade.php (Risalah Rapat 21 Sept 2026: tombol "Simpan &
    // Lanjutkan" antar bagian).
    $tabsUrutan = [
        ['label' => 'Detail', 'route' => 'admin.psn.show', 'params' => [$psn]],
        ['label' => 'RO/Proyek', 'route' => 'admin.psn.ro', 'params' => [$psn]],
        ['label' => 'Risiko', 'route' => 'admin.psn.risiko', 'params' => [$psn]],
        ['label' => 'Indikator', 'route' => 'admin.psn.capaian', 'params' => [$psn, 'indikator']],
        ['label' => 'Penerima Manfaat', 'route' => 'admin.psn.capaian', 'params' => [$psn, 'penerima_manfaat']],
        ['label' => 'Trisula', 'route' => 'admin.psn.capaian', 'params' => [$psn, 'trisula']],
        ['label' => 'Dasar Hukum', 'route' => 'admin.psn.profil', 'params' => [$psn, 'dasar_hukum']],
        ['label' => 'Stakeholder', 'route' => 'admin.psn.profil', 'params' => [$psn, 'stakeholder']],
        ['label' => 'Kebutuhan Regulasi', 'route' => 'admin.psn.profil', 'params' => [$psn, 'regulasi']],
        ['label' => 'Isu Lainnya', 'route' => 'admin.psn.profil', 'params' => [$psn, 'isu_lainnya']],
        ['label' => 'Kebutuhan Status PSN Tahun Selanjutnya', 'route' => 'admin.psn.profil', 'params' => [$psn, 'evaluasi_status']],
        ['label' => 'Info Memo', 'route' => 'admin.psn.profil', 'params' => [$psn, 'info_memo']],
        ['label' => 'Catatan Monev', 'route' => 'admin.psn.profil', 'params' => [$psn, 'catatan_monev']],
    ];

    $currentTypeNext = $type ?? null;
    $indexAktif = null;
    foreach ($tabsUrutan as $i => $tab) {
        if (request()->routeIs($tab['route']) && (count($tab['params']) < 2 || $currentTypeNext === $tab['params'][1])) {
            $indexAktif = $i;
            break;
        }
    }
    $tabBerikutnya = $indexAktif !== null ? ($tabsUrutan[$indexAktif + 1] ?? null) : null;
@endphp

@if ($tabBerikutnya)
    <div class="flex justify-end">
        <a href="{{ route($tabBerikutnya['route'], $tabBerikutnya['params']) }}"
           class="inline-flex items-center gap-2 rounded-lg bg-blue-800 text-white shadow-sm hover:shadow hover:bg-blue-700 transition-all px-4 py-2 text-sm font-medium">
            Lanjutkan ke {{ $tabBerikutnya['label'] }}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
        </a>
    </div>
@endif
