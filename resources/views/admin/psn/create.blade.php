<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Data PSN</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.psn.store') }}" enctype="multipart/form-data" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                @csrf
                @include('admin.psn._form')

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('admin.psn.index') }}" class="rounded-md border px-4 py-2 text-sm">Batal</a>
                    <button class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
