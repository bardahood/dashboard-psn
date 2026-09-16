<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Verifikasi Kesesuaian Peta & Tata Ruang</h3>

        <form wire:submit="addLokasi" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Aspek</label>
                <input type="text" wire:model="lokasiForm.aspek" placeholder="mis. Kesesuaian RTRW" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                @error('lokasiForm.aspek') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Klaim Dokumen</label>
                <input type="text" wire:model="lokasiForm.klaim_dokumen" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Temuan Lapangan</label>
                <input type="text" wire:model="lokasiForm.temuan_lapangan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sesuai?</label>
                <select wire:model="lokasiForm.sesuai" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Sesuai">Sesuai</option>
                    <option value="Sebagian">Sebagian</option>
                    <option value="Tidak Sesuai">Tidak Sesuai</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                <input type="text" wire:model="lokasiForm.catatan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
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
                    <th class="px-4 py-3">Aspek</th>
                    <th class="px-4 py-3">Klaim Dokumen</th>
                    <th class="px-4 py-3">Temuan Lapangan</th>
                    <th class="px-4 py-3">Sesuai</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($lokasiList as $l)
                    <tr wire:key="lokasi-{{ $l->id }}">
                        <td class="px-4 py-3">{{ $l->aspek }}</td>
                        <td class="px-4 py-3">{{ $l->klaim_dokumen ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $l->temuan_lapangan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($l->sesuai)
                                <span class="rounded-full text-xs px-2 py-1 {{ $l->sesuai === 'Sesuai' ? 'bg-green-100 text-green-700' : ($l->sesuai === 'Sebagian' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ $l->sesuai }}</span>
                            @else - @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="deleteLokasi({{ $l->id }})" wire:confirm="Hapus data ini?" class="text-red-700 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-between">
        <button wire:click="goToStep(5)" class="rounded-md border px-4 py-2 text-sm bg-white">&larr; Kembali</button>
        <button wire:click="goToStep(7)" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">Lanjut ke Dokumen Teknis &rarr;</button>
    </div>
</div>
