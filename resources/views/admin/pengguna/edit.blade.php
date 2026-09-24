<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Pengguna</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.pengguna.update', $pengguna) }}" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                @csrf
                @method('PUT')
                @include('admin.pengguna._form')

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('admin.pengguna.index') }}" class="rounded-md border px-4 py-2 text-sm">Batal</a>
                    <button class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
