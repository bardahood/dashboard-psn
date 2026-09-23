@php
    // Struktur 5-tab konsolidasi mengikuti paparan resmi "Update Project
    // Profile" (Struktur Project Profile: Gambaran Umum, Perencanaan,
    // Trisula, Penjabaran Tahunan) ditambah Upload Dokumen -- menggantikan
    // tab-tab terpisah per sub-resource (RO, Risiko, Indikator, dst) yang
    // sebelumnya masing-masing punya route sendiri. Dipertahankan sinkron
    // manual dengan _next-button.blade.php.
    $tabs = [
        ['label' => 'Gambaran Umum', 'route' => 'admin.psn.gambaran-umum', 'icon' => 'user'],
        ['label' => 'Perencanaan', 'route' => 'admin.psn.perencanaan', 'icon' => 'calendar'],
        ['label' => 'Trisula', 'route' => 'admin.psn.trisula', 'icon' => 'squares'],
        ['label' => 'Penjabaran', 'route' => 'admin.psn.penjabaran', 'icon' => 'clipboard'],
        ['label' => 'Upload Dokumen', 'route' => 'admin.psn.dokumen', 'icon' => 'upload'],
    ];

    if (auth()->user()->can('pengendalian.manage')) {
        $tabs[] = ['label' => 'Kunjungan Pengendalian', 'route' => 'admin.kunjungan-pengendalian.index', 'params' => ['psn_id' => $psn->id], 'icon' => 'briefcase'];
    }

    $icons = [
        'user' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />',
        'squares' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />',
        'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />',
        'upload' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />',
        'briefcase' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.653v-2.87c0-1.09-.784-2.023-1.865-2.175-2.078-.292-4.21-.444-6.385-.444s-4.307.152-6.385.444C4.359 7.604 3.575 8.537 3.575 9.627v2.87c0 .655.284 1.243.75 1.653m16.5 0a2.253 2.253 0 01-1.096.573A47.35 47.35 0 0112 15.75c-2.965 0-5.858-.267-8.654-.777a2.253 2.253 0 01-1.096-.573m0 0V9.75" />',
    ];

    $currentRoute = request()->route()?->getName();
@endphp

<div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl px-2 py-2 overflow-x-auto">
    <div class="flex gap-1 min-w-max">
        @foreach ($tabs as $tab)
            @php
                $isActive = $currentRoute === $tab['route'];
            @endphp
            <a href="{{ route($tab['route'], $tab['params'] ?? $psn) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ $isActive ? 'bg-blue-800 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-blue-800' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">{!! $icons[$tab['icon']] !!}</svg>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>
