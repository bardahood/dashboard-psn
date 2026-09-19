<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Pengguna & Hak Akses</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-50 text-green-700 text-sm rounded-md p-3">{{ session('status') }}</div>
            @endif

            <div class="flex justify-end">
                <a href="{{ route('admin.pengguna.create') }}" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all text-sm font-medium px-4 py-2 hover:bg-blue-700">
                    + Tambah Pengguna
                </a>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Instansi</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Level Akses</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($pengguna as $user)
                            @php $akses = $user->pic?->hakAkses?->first(); @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3">{{ $user->pic?->instansi?->nama_instansi ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $akses?->level_akses ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if (! $akses || $akses->is_active)
                                        <span class="rounded-full text-xs px-2.5 py-1 font-medium bg-green-100 text-green-700">Aktif</span>
                                    @else
                                        <span class="rounded-full text-xs px-2.5 py-1 font-medium bg-red-100 text-red-700">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.pengguna.edit', $user) }}" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">Belum ada pengguna.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $pengguna->links() }}
        </div>
    </div>
</x-app-layout>
