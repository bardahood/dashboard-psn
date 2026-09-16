<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">{{ $editingId ? 'Ubah' : 'Tambah' }} Risiko</h3>

        <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Peristiwa Risiko <span class="text-red-500">*</span></label>
                <textarea wire:model="form.peristiwa_risiko" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                @error('form.peristiwa_risiko') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Risiko</label>
                <input type="text" wire:model="form.kategori_risiko" placeholder="Regulasi/Teknis/Finansial/Lingkungan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Level Risiko Awal</label>
                <select wire:model="form.level_risiko_awal" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    @foreach ($levels as $value => $labelText)
                        <option value="{{ $value }}">{{ $labelText }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Risiko Residual Harapan</label>
                <select wire:model="form.risiko_residual_harapan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    @foreach ($levels as $value => $labelText)
                        <option value="{{ $value }}">{{ $labelText }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Perlakuan/Rencana Penyelesaian</label>
                <textarea wire:model="form.perlakuan_rencana" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
            </div>

            <div class="sm:col-span-2 flex justify-end gap-3">
                @if ($editingId)
                    <button type="button" wire:click="resetForm" class="rounded-md border px-4 py-2 text-sm">Batal</button>
                @endif
                <button type="submit" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">
                    {{ $editingId ? 'Simpan Perubahan' : 'Tambah' }}
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        @forelse ($risikoList as $risiko)
            <div wire:key="risiko-{{ $risiko->id }}" class="bg-white shadow rounded-lg p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="text-sm">
                        <div class="font-medium text-gray-800">{{ $risiko->peristiwa_risiko }}</div>
                        <div class="text-gray-500 text-xs mt-0.5 flex gap-2">
                            @if ($risiko->kategori_risiko) <span>{{ $risiko->kategori_risiko }}</span> @endif
                            @if ($risiko->level_risiko_awal)
                                <span class="rounded-full bg-amber-100 text-amber-700 px-2 py-0.5">{{ $risiko->level_risiko_awal }}</span>
                            @endif
                            @if ($risiko->risiko_residual_harapan)
                                <span class="text-gray-400">Harapan residual: {{ $risiko->risiko_residual_harapan }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-3 text-sm whitespace-nowrap">
                        <button wire:click="toggleStatus({{ $risiko->id }})" class="text-blue-800 hover:underline">
                            {{ $expandedStatusRisikoId === $risiko->id ? 'Tutup' : 'Laporan Triwulanan' }}
                        </button>
                        <button wire:click="edit({{ $risiko->id }})" class="text-blue-800 hover:underline">Ubah</button>
                        <button wire:click="delete({{ $risiko->id }})" wire:confirm="Hapus risiko ini beserta seluruh laporan triwulanannya?" class="text-red-700 hover:underline">Hapus</button>
                    </div>
                </div>

                @if ($expandedStatusRisikoId === $risiko->id)
                    <div class="mt-3 border-t pt-3">
                        <form wire:submit="addStatus" class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div>
                                <label class="block text-gray-500 mb-1">Tahun</label>
                                <input type="number" wire:model="statusForm.tahun" class="w-full rounded border-gray-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-gray-500 mb-1">Triwulan</label>
                                <select wire:model="statusForm.triwulan" class="w-full rounded border-gray-300 text-xs">
                                    @for ($i = 1; $i <= 4; $i++) <option value="{{ $i }}">TW {{ $i }}</option> @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-500 mb-1">Progres Perlakuan (%)</label>
                                <input type="number" step="0.01" wire:model="statusForm.progres_pelaksanaan_persen" class="w-full rounded border-gray-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-gray-500 mb-1">Risiko Residual Aktual</label>
                                <select wire:model="statusForm.risiko_residual_aktual" class="w-full rounded border-gray-300 text-xs">
                                    <option value="">-</option>
                                    @foreach ($levels as $value => $labelText)
                                        <option value="{{ $value }}">{{ $labelText }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-500 mb-1">Status Perlakuan</label>
                                <select wire:model="statusForm.status_perlakuan" class="w-full rounded border-gray-300 text-xs">
                                    <option value="">-</option>
                                    @foreach ($statusPerlakuanOptions as $value => $labelText)
                                        <option value="{{ $value }}">{{ $labelText }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2 sm:col-span-3">
                                <label class="block text-gray-500 mb-1">Catatan</label>
                                <input type="text" wire:model="statusForm.catatan" class="w-full rounded border-gray-300 text-xs">
                            </div>
                            <div class="col-span-2 sm:col-span-4 flex justify-end">
                                <button type="submit" class="rounded-md bg-blue-800 text-white px-3 py-1.5 text-xs hover:bg-blue-700">Simpan Laporan</button>
                            </div>
                        </form>

                        <table class="min-w-full text-xs mt-3">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th class="py-1 pr-3">Periode</th>
                                    <th class="py-1 pr-3">Progres</th>
                                    <th class="py-1 pr-3">Residual Aktual</th>
                                    <th class="py-1 pr-3">Status</th>
                                    <th class="py-1"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse ($statusList as $s)
                                    <tr wire:key="status-{{ $s->id }}">
                                        <td class="py-1 pr-3">{{ $s->tahun }} TW{{ $s->triwulan }}</td>
                                        <td class="py-1 pr-3">{{ $s->progres_pelaksanaan_persen !== null ? $s->progres_pelaksanaan_persen.'%' : '-' }}</td>
                                        <td class="py-1 pr-3">{{ $s->risiko_residual_aktual ?? '-' }}</td>
                                        <td class="py-1 pr-3">{{ $s->status_perlakuan ?? '-' }}</td>
                                        <td class="py-1 text-right">
                                            <button wire:click="deleteStatus({{ $s->id }})" wire:confirm="Hapus laporan periode ini?" class="text-red-700 hover:underline">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-2 text-center text-gray-400">Belum ada laporan triwulanan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-400 text-sm">Belum ada data risiko.</div>
        @endforelse
    </div>
</div>
