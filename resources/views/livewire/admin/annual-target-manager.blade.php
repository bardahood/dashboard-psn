<div class="space-y-6">
    <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
        <h3 class="font-semibold text-gray-700 mb-4">{{ $editingParentId ? 'Ubah' : 'Tambah' }} {{ $config['label'] }}</h3>

        <form wire:submit="saveParent" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ($config['parentFields'] as $field)
                <div class="{{ $field['type'] === 'textarea' ? 'sm:col-span-2' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $field['label'] }}@if($field['required'] ?? false) <span class="text-red-500">*</span>@endif
                    </label>

                    @if ($field['type'] === 'textarea')
                        <textarea wire:model="parentForm.{{ $field['name'] }}" rows="2" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm"></textarea>
                    @elseif ($field['type'] === 'trisula_indikator')
                        @if (isset($parentForm['kategori_trisula']) && array_key_exists($parentForm['kategori_trisula'], \App\Livewire\Admin\AnnualTargetManager::PRESET_INDIKATOR_TRISULA))
                            <input type="text" readonly wire:model="parentForm.{{ $field['name'] }}" class="block w-full rounded-lg border-gray-200 bg-gray-50 text-gray-500 shadow-sm text-sm">
                            <p class="mt-1 text-xs text-gray-400">Indikator sudah baku untuk kategori Trisula ini.</p>
                        @else
                            <textarea wire:model="parentForm.{{ $field['name'] }}" rows="2" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm"></textarea>
                        @endif
                    @elseif ($field['type'] === 'select')
                        <select wire:model.live="parentForm.{{ $field['name'] }}" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach ($field['options'] as $value => $labelText)
                                <option value="{{ $value }}">{{ $labelText }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="{{ $field['type'] === 'number' ? 'number' : 'text' }}" wire:model="parentForm.{{ $field['name'] }}" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    @endif

                    @error('parentForm.'.$field['name'])
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="sm:col-span-2 flex justify-end gap-3">
                @if ($editingParentId)
                    <button type="button" wire:click="resetParentForm" class="rounded-md border px-4 py-2 text-sm">Batal</button>
                @endif
                <button type="submit" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">
                    {{ $editingParentId ? 'Simpan Perubahan' : 'Tambah' }}
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        @forelse ($parents as $parent)
            <div wire:key="parent-{{ $parent->id }}" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="text-sm">
                        <div class="font-medium text-gray-800">{{ $parent->{$config['titleField']} }}</div>
                        <div class="text-gray-500 text-xs mt-0.5">
                            @if(isset($parent->satuan)) Satuan: {{ $parent->satuan ?? '-' }} &middot; @endif
                            Baseline: {{ $parent->baseline ?? '-' }}
                        </div>
                    </div>
                    <div class="flex gap-3 text-sm whitespace-nowrap">
                        <button wire:click="toggleYears({{ $parent->id }})" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">
                            {{ $expandedParentId === $parent->id ? 'Tutup' : ($type === 'trisula' ? 'Target/Realisasi Tahunan & Triwulanan' : 'Target/Realisasi Tahunan') }}
                        </button>
                        <button wire:click="editParent({{ $parent->id }})" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">Ubah</button>
                        <button wire:click="deleteParent({{ $parent->id }})" wire:confirm="Hapus data ini beserta seluruh target tahunannya?" class="text-red-700 hover:underline">Hapus</button>
                    </div>
                </div>

                @if ($expandedParentId === $parent->id)
                    <form wire:submit="saveYears" class="mt-4 border-t pt-4 overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th class="py-1 pr-3">Tahun</th>
                                    <th class="py-1 pr-3">Target Akhir</th>
                                    <th class="py-1 pr-3">Target</th>
                                    <th class="py-1 pr-3">Realisasi</th>
                                    @unless ($config['hideStatusCapaian'] ?? false)
                                        <th class="py-1 pr-3">Status Capaian</th>
                                    @endunless
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($years as $year)
                                    <tr>
                                        <td class="py-1 pr-3 font-medium">{{ $year }}</td>
                                        <td class="py-1 pr-3"><input type="text" wire:model="yearForm.{{ $year }}.target_akhir" class="w-28 rounded-lg border-gray-300 transition-colors text-xs"></td>
                                        <td class="py-1 pr-3"><input type="number" step="0.01" wire:model="yearForm.{{ $year }}.target" class="w-24 rounded-lg border-gray-300 transition-colors text-xs"></td>
                                        <td class="py-1 pr-3">
                                            <input type="number" step="0.01" wire:model="yearForm.{{ $year }}.realisasi" @disabled($year > now()->year)
                                                   class="w-24 rounded-lg border-gray-300 transition-colors text-xs disabled:bg-gray-100 disabled:text-gray-400"
                                                   title="{{ $year > now()->year ? 'Realisasi tahun mendatang belum bisa diisi' : '' }}">
                                        </td>
                                        @unless ($config['hideStatusCapaian'] ?? false)
                                            <td class="py-1 pr-3"><input type="text" wire:model="yearForm.{{ $year }}.status_capaian" class="w-32 rounded-lg border-gray-300 transition-colors text-xs"></td>
                                        @endunless
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-1.5 text-xs hover:bg-blue-700">Simpan Target Tahunan</button>
                        </div>
                        <p class="mt-2 text-xs text-gray-400">% Realisasi dihitung otomatis dari Target &amp; Realisasi. Kosongkan seluruh kolom pada satu tahun untuk menghapus baris tahun tersebut.</p>
                    </form>

                    @if ($type === 'trisula')
                        <div class="mt-4 border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Target/Realisasi Triwulanan</h4>
                            <form wire:submit="saveTw" class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                                <div>
                                    <label class="block text-gray-500 mb-1">Tahun</label>
                                    <input type="number" wire:model="twForm.tahun" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                                    @error('twForm.tahun') <p class="text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-gray-500 mb-1">Triwulan</label>
                                    <select wire:model="twForm.triwulan" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                                        <option value="">-</option>
                                        @for ($i = 1; $i <= 4; $i++) <option value="{{ $i }}">TW {{ $i }}</option> @endfor
                                    </select>
                                    @error('twForm.triwulan') <p class="text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-gray-500 mb-1">Target</label>
                                    <input type="number" step="0.01" wire:model="twForm.target" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                                </div>
                                <div>
                                    <label class="block text-gray-500 mb-1">Realisasi</label>
                                    <input type="number" step="0.01" wire:model="twForm.realisasi" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                                </div>
                                <div>
                                    <label class="block text-gray-500 mb-1">Status Capaian</label>
                                    <input type="text" wire:model="twForm.status_capaian" class="w-full rounded-lg border-gray-300 transition-colors text-xs">
                                </div>
                                <div class="col-span-2 sm:col-span-5 flex justify-end">
                                    <button type="submit" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-3 py-1.5 text-xs hover:bg-blue-700">Tambah Target Triwulanan</button>
                                </div>
                            </form>

                            <table class="min-w-full text-xs mt-3">
                                <thead class="text-left text-gray-500">
                                    <tr>
                                        <th class="py-1 pr-3">Periode</th>
                                        <th class="py-1 pr-3">Target</th>
                                        <th class="py-1 pr-3">Realisasi</th>
                                        <th class="py-1 pr-3">% Realisasi</th>
                                        <th class="py-1 pr-3">Status Capaian</th>
                                        <th class="py-1"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse ($twList as $tw)
                                        <tr wire:key="tw-{{ $tw->id }}">
                                            <td class="py-1 pr-3">{{ $tw->tahun }} TW{{ $tw->triwulan }}</td>
                                            <td class="py-1 pr-3">{{ $tw->target ?? '-' }}</td>
                                            <td class="py-1 pr-3">{{ $tw->realisasi ?? '-' }}</td>
                                            <td class="py-1 pr-3">{{ $tw->persen_realisasi !== null ? $tw->persen_realisasi.'%' : '-' }}</td>
                                            <td class="py-1 pr-3">{{ $tw->status_capaian ?? '-' }}</td>
                                            <td class="py-1 text-right">
                                                <button wire:click="deleteTw({{ $tw->id }})" wire:confirm="Hapus data triwulan ini?" class="text-red-700 hover:underline">Hapus</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="py-2 text-center text-gray-400">Belum ada target triwulanan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            </div>
        @empty
            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 text-center text-gray-400 text-sm">Belum ada data {{ $config['label'] }}.</div>
        @endforelse
    </div>
</div>
