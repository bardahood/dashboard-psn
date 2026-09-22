<div class="space-y-6">
    <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
        <h3 class="font-semibold text-gray-700 mb-4">{{ $editingId ? 'Ubah' : 'Tambah' }} Risiko</h3>

        <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Peristiwa Risiko <span class="text-red-500">*</span></label>
                <textarea wire:model="form.peristiwa_risiko" rows="2" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm"></textarea>
                @error('form.peristiwa_risiko') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PJ Risiko <span class="text-xs font-normal text-gray-400">(risk owner)</span></label>
                <select wire:model="form.penanggung_jawab_id" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih PIC --</option>
                    @foreach ($picOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Perlakuan/Rencana Penyelesaian</label>
                <textarea wire:model="form.perlakuan_rencana" rows="2" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PJ Perlakuan <span class="text-xs font-normal text-gray-400">(pelaksana perlakuan)</span></label>
                <select wire:model="form.pelaksana_perlakuan_id" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih PIC --</option>
                    @foreach ($picOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori/Aspek Risiko</label>
                <select wire:model="form.kategori_risiko" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    @foreach ($kategoriOptions as $value => $labelText)
                        <option value="{{ $value }}">{{ $labelText }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Level Risiko Awal</label>
                <select wire:model="form.level_risiko_awal" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    @foreach ($levels as $value => $labelText)
                        <option value="{{ $value }}">{{ $labelText }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Proyek/RO Terkait <span class="text-xs font-normal text-gray-400">(untuk Critical Path)</span></label>
                <select wire:model="form.ro_id" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Tidak terkait RO spesifik --</option>
                    @foreach ($roOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Mulai Perlakuan</label>
                <input type="date" wire:model="form.target_mulai" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Selesai Perlakuan</label>
                <input type="date" wire:model="form.target_selesai" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Pelaksanaan Perlakuan</label>
                <input type="number" wire:model="form.tahun_pelaksanaan_perlakuan" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div class="flex items-end pb-1.5">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="form.is_titik_kritis" class="rounded border-gray-300">
                    Titik Kritis (Critical Path)
                </label>
            </div>

            <div class="sm:col-span-2 flex justify-end gap-3">
                @if ($editingId)
                    <button type="button" wire:click="resetForm" class="rounded-md border px-4 py-2 text-sm">Batal</button>
                @endif
                <button type="submit" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">
                    {{ $editingId ? 'Simpan Perubahan' : 'Tambah' }}
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        @forelse ($risikoList as $risiko)
            <div wire:key="risiko-{{ $risiko->id }}" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="text-sm">
                        <div class="font-medium text-gray-800">
                            {{ $risiko->peristiwa_risiko }}
                            @if ($risiko->is_titik_kritis)
                                <span class="ml-1 text-xs rounded-full bg-red-100 text-red-700 px-2 py-0.5">Titik Kritis</span>
                            @endif
                        </div>
                        <div class="text-gray-500 text-xs mt-0.5 flex flex-wrap gap-2">
                            @if ($risiko->kategori_risiko) <span>{{ $risiko->kategori_risiko }}</span> @endif
                            @if ($risiko->level_risiko_awal)
                                <span class="rounded-full bg-amber-100 text-amber-700 px-2 py-0.5">{{ $risiko->level_risiko_awal }}</span>
                            @endif
                            @if ($risiko->ro) <span class="text-gray-400">RO: {{ $risiko->ro->nama_ro }}</span> @endif
                            @if ($risiko->penanggungJawab) <span class="text-gray-400">PJ Risiko: {{ $risiko->penanggungJawab->nama_pic }}</span> @endif
                            @if ($risiko->pelaksanaPerlakuan) <span class="text-gray-400">PJ Perlakuan: {{ $risiko->pelaksanaPerlakuan->nama_pic }}</span> @endif
                            @if ($risiko->target_mulai || $risiko->target_selesai)
                                <span class="text-gray-400">
                                    Target: {{ $risiko->target_mulai?->format('d/m/Y') ?? '-' }} &rarr; {{ $risiko->target_selesai?->format('d/m/Y') ?? '-' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-3 text-sm whitespace-nowrap">
                        <button wire:click="toggleStatus({{ $risiko->id }})" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">
                            {{ $expandedStatusRisikoId === $risiko->id ? 'Tutup' : 'Laporan Triwulanan' }}
                        </button>
                        <button wire:click="edit({{ $risiko->id }})" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">Ubah</button>
                        <button wire:click="delete({{ $risiko->id }})" wire:confirm="Hapus risiko ini beserta seluruh laporan triwulanannya?" class="text-red-700 hover:underline">Hapus</button>
                    </div>
                </div>

                @if ($expandedStatusRisikoId === $risiko->id)
                    <div class="mt-3 border-t pt-3">
                        <form wire:submit="addStatus" class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div>
                                <label class="block text-gray-500 mb-1">Tahun</label>
                                <input type="number" wire:model="statusForm.tahun" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                            </div>
                            <div>
                                <label class="block text-gray-500 mb-1">Triwulan</label>
                                <select wire:model="statusForm.triwulan" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                                    @for ($i = 1; $i <= 4; $i++) <option value="{{ $i }}">TW {{ $i }}</option> @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-500 mb-1">Progres Perlakuan (%)</label>
                                <input type="number" step="0.01" wire:model="statusForm.progres_pelaksanaan_persen" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                            </div>
                            <div>
                                <label class="block text-gray-500 mb-1">Risiko Residual Aktual</label>
                                <select wire:model="statusForm.risiko_residual_aktual" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                                    <option value="">-</option>
                                    @foreach ($levels as $value => $labelText)
                                        <option value="{{ $value }}">{{ $labelText }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-500 mb-1">Status Perlakuan</label>
                                <select wire:model="statusForm.status_perlakuan" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                                    <option value="">-</option>
                                    @foreach ($statusPerlakuanOptions as $value => $labelText)
                                        <option value="{{ $value }}">{{ $labelText }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2 sm:col-span-3">
                                <label class="block text-gray-500 mb-1">Catatan</label>
                                <input type="text" wire:model="statusForm.catatan" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                            </div>
                            <div class="col-span-2 sm:col-span-4 flex justify-end">
                                <button type="submit" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-3 py-1.5 text-xs hover:bg-blue-700">Simpan Laporan</button>
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
            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 text-center text-gray-400 text-sm">Belum ada data risiko.</div>
        @endforelse
    </div>
</div>
