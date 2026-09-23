<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profil PSN: {{ \Illuminate\Support\Str::limit($psn->nama_psn, 60) }}
            </h2>
            <a href="{{ route('admin.psn.index') }}" class="text-sm text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">&larr; Kembali ke Data PSN</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin.psn._profil-tabs')

            @if (session('status'))
                <div class="rounded-md bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.psn.update', $psn) }}" enctype="multipart/form-data" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                @csrf
                @method('PUT')
                <h3 class="font-semibold text-gray-700 mb-4">Gambaran Umum</h3>
                @include('admin.psn._form')

                <div class="mt-6 flex justify-end gap-3">
                    <button class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>

            @livewire('admin.sub-resource-manager', ['psn' => $psn, 'type' => 'dasar_hukum'], key('dasar_hukum'))

            @livewire('admin.sub-resource-manager', ['psn' => $psn, 'type' => 'stakeholder'], key('stakeholder'))

            @include('admin.psn._next-button')
        </div>
    </div>
</x-app-layout>
