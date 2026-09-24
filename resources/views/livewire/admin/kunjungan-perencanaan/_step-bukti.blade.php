<div class="space-y-6">
    <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Indeks Bukti Lapangan</h3>

        <form wire:submit="addBukti" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">ID Bukti</label>
                <input type="text" wire:model="buktiForm.id_bukti" placeholder="mis. KL-K4-001" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kriteria Terkait</label>
                <input type="text" wire:model="buktiForm.kriteria_terkait" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Nama Dokumen</label>
                <input type="text" wire:model="buktiForm.nama_dokumen" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Pemilik Data</label>
                <input type="text" wire:model="buktiForm.pemilik_data" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Lokasi Bukti</label>
                <input type="text" wire:model="buktiForm.lokasi_bukti" placeholder="halaman/foto/koordinat" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status Verifikasi</label>
                <select wire:model="buktiForm.status_verifikasi" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Belum diterima">Belum diterima</option>
                    <option value="Diterima">Diterima</option>
                    <option value="Diverifikasi">Diverifikasi</option>
                    <option value="Perlu perbaikan">Perlu perbaikan</option>
                    <option value="Kedaluwarsa">Kedaluwarsa</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Simpulan Singkat</label>
                <input type="text" wire:model="buktiForm.simpulan_singkat" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Tindak Lanjut</label>
                <input type="text" wire:model="buktiForm.tindak_lanjut" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700 w-full">Tambah</button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">ID Bukti</th>
                    <th class="px-4 py-3">Nama Dokumen</th>
                    <th class="px-4 py-3">Kriteria Terkait</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($buktiList as $b)
                    <tr wire:key="bukti-{{ $b->id }}">
                        <td class="px-4 py-3">{{ $b->id_bukti ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $b->nama_dokumen ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $b->kriteria_terkait ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $b->status_verifikasi ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="deleteBukti({{ $b->id }})" wire:confirm="Hapus data ini?" class="text-red-700 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-between">
        <button wire:click="goToStep(8)" class="rounded-md border px-4 py-2 text-sm bg-white">&larr; Kembali</button>
        <button wire:click="goToStep(10)" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">Lanjut ke Kesimpulan &rarr;</button>
    </div>
</div>
