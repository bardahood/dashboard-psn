<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profil PSN: {{ \Illuminate\Support\Str::limit($psn->nama_psn, 60) }}
            </h2>
            <a href="{{ route('admin.psn.show', $psn) }}" class="text-sm text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">&larr; Kembali ke Detail PSN</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin.psn._profil-tabs')

            @livewire('admin.risiko-manager', ['psn' => $psn])
        </div>
    </div>
</x-app-layout>
