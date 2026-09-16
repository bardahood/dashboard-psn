<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-1">Bagian C: Verifikasi Capaian Fisik per RO/Aktivitas</h3>
        <p class="text-xs text-gray-400 mb-4">% capaian &amp; kesesuaian dihitung otomatis (toleransi deviasi &le;5% = Sesuai, 5-20% = Sebagian, &gt;20% = Tidak Sesuai).</p>

        <form wire:submit="addFisik" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">RO/Aktivitas</label>
                <select wire:model="fisikForm.ro_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih RO --</option>
                    @foreach ($roOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
                @error('fisikForm.ro_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Target Periode Ini</label>
                <input type="number" step="0.01" wire:model="fisikForm.target_periode_ini" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Realisasi Klaim</label>
                <input type="number" step="0.01" wire:model="fisikForm.realisasi_fisik_klaim" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Realisasi Verifikasi</label>
                <input type="number" step="0.01" wire:model="fisikForm.realisasi_fisik_verifikasi" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                <input type="text" wire:model="fisikForm.catatan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700 w-full">Tambah</button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">RO/Aktivitas</th>
                    <th class="px-4 py-3">Target</th>
                    <th class="px-4 py-3">Klaim</th>
                    <th class="px-4 py-3">Verifikasi</th>
                    <th class="px-4 py-3">% Capaian</th>
                    <th class="px-4 py-3">Kesesuaian</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($fisikList as $f)
                    <tr wire:key="fisik-{{ $f->id }}">
                        <td class="px-4 py-3">{{ $f->ro?->nama_ro }}</td>
                        <td class="px-4 py-3">{{ $f->target_periode_ini ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $f->realisasi_fisik_klaim ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $f->realisasi_fisik_verifikasi ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $f->persen_capaian !== null ? $f->persen_capaian.'%' : '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($f->kesesuaian)
                                <span class="rounded-full text-xs px-2 py-1
                                    {{ $f->kesesuaian === 'Sesuai' ? 'bg-green-100 text-green-700' : ($f->kesesuaian === 'Sebagian' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $f->kesesuaian }}
                                </span>
                            @else - @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="deleteFisik({{ $f->id }})" wire:confirm="Hapus data ini?" class="text-red-700 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-between">
        <button wire:click="goToStep(2)" class="rounded-md border px-4 py-2 text-sm bg-white">&larr; Kembali</button>
        <button wire:click="goToStep(4)" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">Lanjut ke Bagian D &rarr;</button>
    </div>
</div>
