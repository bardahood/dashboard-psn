@php
    // Urutan linear 5-tab Project Profile. Dipertahankan sinkron manual
    // dengan _profil-tabs.blade.php.
    $tabsUrutan = [
        ['label' => 'Gambaran Umum', 'route' => 'admin.psn.gambaran-umum'],
        ['label' => 'Perencanaan', 'route' => 'admin.psn.perencanaan'],
        ['label' => 'Trisula', 'route' => 'admin.psn.trisula'],
        ['label' => 'Penjabaran', 'route' => 'admin.psn.penjabaran'],
        ['label' => 'Upload Dokumen', 'route' => 'admin.psn.dokumen'],
    ];

    $currentRouteNext = request()->route()?->getName();
    $indexAktif = null;
    foreach ($tabsUrutan as $i => $tab) {
        if ($currentRouteNext === $tab['route']) {
            $indexAktif = $i;
            break;
        }
    }
    $tabBerikutnya = $indexAktif !== null ? ($tabsUrutan[$indexAktif + 1] ?? null) : null;
@endphp
@if ($tabBerikutnya)
    <div class="flex justify-end">
        <a href="{{ route($tabBerikutnya['route'], $psn) }}"
           class="inline-flex items-center gap-2 rounded-lg bg-blue-800 text-white shadow-sm hover:shadow hover:bg-blue-700 transition-all px-4 py-2 text-sm font-medium">
            Lanjutkan ke {{ $tabBerikutnya['label'] }}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
        </a>
    </div>
@endif
