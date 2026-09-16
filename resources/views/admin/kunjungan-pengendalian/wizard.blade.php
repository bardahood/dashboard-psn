<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Instrumen Kunjungan Lapangan: Pengendalian
            </h2>
            <a href="{{ route('admin.kunjungan-pengendalian.index') }}" class="text-sm text-blue-800 hover:underline">&larr; Kembali ke Daftar Kunjungan</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @livewire('admin.kunjungan-pengendalian-wizard', ['kunjungan' => $kunjungan], key($kunjungan?->id ?? 'new'))
        </div>
    </div>
</x-app-layout>
