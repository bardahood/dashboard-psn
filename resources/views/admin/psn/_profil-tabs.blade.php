@php
    $tabs = [
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
        ['label' => 'Evaluasi Status', 'route' => 'admin.psn.profil', 'params' => [$psn, 'evaluasi_status']],
        ['label' => 'Info Memo', 'route' => 'admin.psn.profil', 'params' => [$psn, 'info_memo']],
        ['label' => 'Catatan Monev', 'route' => 'admin.psn.profil', 'params' => [$psn, 'catatan_monev']],
    ];
    $currentType = $type ?? null;
@endphp

<div class="bg-white shadow rounded-lg px-2 py-2 overflow-x-auto">
    <div class="flex gap-1 min-w-max">
        @foreach ($tabs as $tab)
            @php
                $isActive = request()->routeIs($tab['route']) && (count($tab['params']) < 2 || $currentType === $tab['params'][1]);
            @endphp
            <a href="{{ route($tab['route'], $tab['params']) }}"
               class="px-3 py-1.5 rounded-md text-sm whitespace-nowrap {{ $isActive ? 'bg-blue-800 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>
