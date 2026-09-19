<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Data PSN</h2>
            <a href="{{ route('admin.psn.show', $psn) }}" class="text-sm text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">Lihat Detail &rarr;</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.psn.update', $psn) }}" enctype="multipart/form-data" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                @csrf
                @method('PUT')
                @include('admin.psn._form')

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('admin.psn.index') }}" class="rounded-md border px-4 py-2 text-sm">Batal</a>
                    <button class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
