<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Bagian F: Verifikasi Kebutuhan Regulasi</h3>

        <form wire:submit="addRegulasi" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Kebutuhan Regulasi</label>
                <select wire:model="regulasiForm.regulasi_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih Regulasi --</option>
                    @foreach ($regulasiOptions as $id => $nama)
                        <option value="{{ $id }}">{{ \Illuminate\Support\Str::limit($nama, 60) }}</option>
                    @endforeach
                </select>
                @error('regulasiForm.regulasi_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status Klaim</label>
                <select wire:model="regulasiForm.status_klaim" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Proses">Proses</option>
                    <option value="Direncanakan">Direncanakan</option>
                    <option value="Belum Ada">Belum Ada</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status Temuan Lapangan</label>
                <input type="text" wire:model="regulasiForm.status_temuan_lapangan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Bukti Dukung Ditemukan</label>
                <select wire:model="regulasiForm.bukti_dukung_ditemukan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Ya">Ya</option>
                    <option value="Tidak">Tidak</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kesesuaian</label>
                <select wire:model="regulasiForm.kesesuaian" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Konsisten">Konsisten</option>
                    <option value="Ada Perbedaan - Perlu Klarifikasi">Ada Perbedaan - Perlu Klarifikasi</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                <input type="text" wire:model="regulasiForm.catatan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
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
                    <th class="px-4 py-3">Kebutuhan Regulasi</th>
                    <th class="px-4 py-3">Status Klaim</th>
                    <th class="px-4 py-3">Temuan Lapangan</th>
                    <th class="px-4 py-3">Kesesuaian</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($regulasiList as $r)
                    <tr wire:key="regulasi-{{ $r->id }}">
                        <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($r->regulasi?->nama_regulasi, 50) }}</td>
                        <td class="px-4 py-3">{{ $r->status_klaim ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $r->status_temuan_lapangan ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $r->kesesuaian ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="deleteRegulasi({{ $r->id }})" wire:confirm="Hapus data ini?" class="text-red-700 hover:underline">Hapus</button>
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
        <button wire:click="goToStep(7)" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">Lanjut ke Bagian G &rarr;</button>
    </div>
</div>
