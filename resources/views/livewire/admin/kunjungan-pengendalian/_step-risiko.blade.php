<div class="space-y-6">
    <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
        <h3 class="font-semibold text-gray-700 mb-1">Bagian E: Verifikasi Peristiwa Risiko & Risiko Residual</h3>
        <p class="text-xs text-gray-400 mb-4">Evaluasi risiko dihitung otomatis dari perbandingan Risiko Residual Harapan (saat perencanaan) vs Aktual (temuan lapangan).</p>

        <form wire:submit="addRisiko" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Peristiwa Risiko</label>
                <select wire:model="risikoForm.risiko_id" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih Risiko --</option>
                    @foreach ($risikoOptions as $id => $nama)
                        <option value="{{ $id }}">{{ \Illuminate\Support\Str::limit($nama, 60) }}</option>
                    @endforeach
                </select>
                @error('risikoForm.risiko_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Progres Perlakuan (%)</label>
                <input type="number" step="0.01" wire:model="risikoForm.progres_pelaksanaan_persen" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Risiko Residual Aktual</label>
                <select wire:model="risikoForm.risiko_residual_aktual" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Rendah">Rendah</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Tinggi">Tinggi</option>
                    <option value="Sangat Tinggi">Sangat Tinggi</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status Perlakuan</label>
                <select wire:model="risikoForm.status_perlakuan" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Selesai">Selesai</option>
                    <option value="On Progress">On Progress</option>
                    <option value="Belum ada Tindak Lanjut">Belum ada Tindak Lanjut</option>
                </select>
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                <input type="text" wire:model="risikoForm.catatan" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
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
                    <th class="px-4 py-3">Peristiwa Risiko</th>
                    <th class="px-4 py-3">Harapan</th>
                    <th class="px-4 py-3">Aktual</th>
                    <th class="px-4 py-3">Status Perlakuan</th>
                    <th class="px-4 py-3">Evaluasi</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($risikoList as $r)
                    <tr wire:key="risiko-row-{{ $r->id }}">
                        <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($r->risiko?->peristiwa_risiko, 60) }}</td>
                        <td class="px-4 py-3">{{ $r->risiko?->risiko_residual_harapan ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $r->risiko_residual_aktual ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $r->status_perlakuan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($r->evaluasi_risiko)
                                <span class="rounded-full text-xs px-2.5 py-1 font-medium {{ $r->evaluasi_risiko === 'Memburuk' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $r->evaluasi_risiko }}
                                </span>
                            @else - @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="deleteRisiko({{ $r->id }})" wire:confirm="Hapus data ini?" class="text-red-700 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-between">
        <button wire:click="goToStep(4)" class="rounded-md border px-4 py-2 text-sm bg-white">&larr; Kembali</button>
        <button wire:click="goToStep(6)" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">Lanjut ke Bagian F &rarr;</button>
    </div>
</div>
