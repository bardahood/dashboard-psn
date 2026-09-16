<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Bagian H: Dokumentasi Pendukung</h3>

        <form wire:submit="addDokumentasi" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">File</label>
                <input type="file" wire:model="dokumentasiFile" class="block w-full text-sm">
                <div wire:loading wire:target="dokumentasiFile" class="text-xs text-gray-400 mt-1">Mengunggah...</div>
                @error('dokumentasiFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                <select wire:model="dokumentasiForm.kategori" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Foto Lapangan">Foto Lapangan</option>
                    <option value="Berita Acara">Berita Acara</option>
                    <option value="Dokumen Realisasi Anggaran">Dokumen Realisasi Anggaran</option>
                    <option value="Dokumen Teknis">Dokumen Teknis</option>
                    <option value="Dokumen Regulasi">Dokumen Regulasi</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Deskripsi</label>
                <input type="text" wire:model="dokumentasiForm.deskripsi" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700 w-full">Unggah</button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">No.</th>
                    <th class="px-4 py-3">Deskripsi</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">File</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($dokumentasiList as $d)
                    <tr wire:key="dok-{{ $d->id }}">
                        <td class="px-4 py-3">{{ $d->nomor }}</td>
                        <td class="px-4 py-3">{{ $d->deskripsi ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $d->kategori ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($d->nama_file_tautan) }}" target="_blank" class="text-blue-800 hover:underline">Lihat File</a>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="deleteDokumentasi({{ $d->id }})" wire:confirm="Hapus file ini?" class="text-red-700 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada dokumentasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-between">
        <button wire:click="goToStep(7)" class="rounded-md border px-4 py-2 text-sm bg-white">&larr; Kembali</button>
        <button wire:click="goToStep(9)" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">Lanjut ke Bagian I &rarr;</button>
    </div>
</div>
