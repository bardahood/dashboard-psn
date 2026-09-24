<div class="space-y-6">
    <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
        <h3 class="font-semibold text-gray-700 mb-1">Bagian D: Verifikasi Realisasi Anggaran per RO/Aktivitas</h3>
        <p class="text-xs text-gray-400 mb-4">% realisasi &amp; kesesuaian dihitung otomatis dengan formula yang sama seperti Bagian C.</p>

        <form wire:submit="addAnggaran" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">RO/Aktivitas</label>
                <select wire:model="anggaranForm.ro_id" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih RO --</option>
                    @foreach ($roOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
                @error('anggaranForm.ro_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Pembiayaan Rencana (Juta Rp)</label>
                <input type="number" step="0.01" wire:model="anggaranForm.pembiayaan_rencana_juta_rp" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Realisasi Klaim (Juta Rp)</label>
                <input type="number" step="0.01" wire:model="anggaranForm.realisasi_anggaran_klaim_juta_rp" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Realisasi Verifikasi (Juta Rp)</label>
                <input type="number" step="0.01" wire:model="anggaranForm.realisasi_anggaran_verifikasi_juta_rp" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Bukti Dokumen Tersedia</label>
                <select wire:model="anggaranForm.bukti_dokumen_tersedia" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Ya">Ya</option>
                    <option value="Sebagian">Sebagian</option>
                    <option value="Tidak">Tidak</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                <input type="text" wire:model="anggaranForm.catatan" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
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
                    <th class="px-4 py-3">RO/Aktivitas</th>
                    <th class="px-4 py-3">Rencana</th>
                    <th class="px-4 py-3">Klaim</th>
                    <th class="px-4 py-3">Verifikasi</th>
                    <th class="px-4 py-3">% Realisasi</th>
                    <th class="px-4 py-3">Kesesuaian</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($anggaranList as $a)
                    <tr wire:key="anggaran-{{ $a->id }}">
                        <td class="px-4 py-3">{{ $a->ro?->nama_ro }}</td>
                        <td class="px-4 py-3">{{ $a->pembiayaan_rencana_juta_rp ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $a->realisasi_anggaran_klaim_juta_rp ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $a->realisasi_anggaran_verifikasi_juta_rp ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $a->persen_realisasi !== null ? $a->persen_realisasi.'%' : '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($a->kesesuaian)
                                <span class="rounded-full text-xs px-2.5 py-1 font-medium
                                    {{ $a->kesesuaian === 'Sesuai' ? 'bg-green-100 text-green-700' : ($a->kesesuaian === 'Sebagian' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $a->kesesuaian }}
                                </span>
                            @else - @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="deleteAnggaran({{ $a->id }})" wire:confirm="Hapus data ini?" class="text-red-700 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-between">
        <button wire:click="goToStep(3)" class="rounded-md border px-4 py-2 text-sm bg-white">&larr; Kembali</button>
        <button wire:click="goToStep(5)" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">Lanjut ke Bagian E &rarr;</button>
    </div>
</div>
