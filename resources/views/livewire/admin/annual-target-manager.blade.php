<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">{{ $editingParentId ? 'Ubah' : 'Tambah' }} {{ $config['label'] }}</h3>

        <form wire:submit="saveParent" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ($config['parentFields'] as $field)
                <div class="{{ $field['type'] === 'textarea' ? 'sm:col-span-2' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $field['label'] }}@if($field['required'] ?? false) <span class="text-red-500">*</span>@endif
                    </label>

                    @if ($field['type'] === 'textarea')
                        <textarea wire:model="parentForm.{{ $field['name'] }}" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                    @elseif ($field['type'] === 'select')
                        <select wire:model="parentForm.{{ $field['name'] }}" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach ($field['options'] as $value => $labelText)
                                <option value="{{ $value }}">{{ $labelText }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" wire:model="parentForm.{{ $field['name'] }}" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
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
                <button type="submit" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">
                    {{ $editingParentId ? 'Simpan Perubahan' : 'Tambah' }}
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        @forelse ($parents as $parent)
            <div wire:key="parent-{{ $parent->id }}" class="bg-white shadow rounded-lg p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="text-sm">
                        <div class="font-medium text-gray-800">{{ $parent->{$config['titleField']} }}</div>
                        <div class="text-gray-500 text-xs mt-0.5">
                            @if(isset($parent->satuan)) Satuan: {{ $parent->satuan ?? '-' }} &middot; @endif
                            Baseline: {{ $parent->baseline ?? '-' }}
                        </div>
                    </div>
                    <div class="flex gap-3 text-sm whitespace-nowrap">
                        <button wire:click="toggleYears({{ $parent->id }})" class="text-blue-800 hover:underline">
                            {{ $expandedParentId === $parent->id ? 'Tutup' : 'Target/Realisasi Tahunan' }}
                        </button>
                        <button wire:click="editParent({{ $parent->id }})" class="text-blue-800 hover:underline">Ubah</button>
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
                                    <th class="py-1 pr-3">Status Capaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($years as $year)
                                    <tr>
                                        <td class="py-1 pr-3 font-medium">{{ $year }}</td>
                                        <td class="py-1 pr-3"><input type="text" wire:model="yearForm.{{ $year }}.target_akhir" class="w-28 rounded border-gray-300 text-xs"></td>
                                        <td class="py-1 pr-3"><input type="number" step="0.01" wire:model="yearForm.{{ $year }}.target" class="w-24 rounded border-gray-300 text-xs"></td>
                                        <td class="py-1 pr-3"><input type="number" step="0.01" wire:model="yearForm.{{ $year }}.realisasi" class="w-24 rounded border-gray-300 text-xs"></td>
                                        <td class="py-1 pr-3"><input type="text" wire:model="yearForm.{{ $year }}.status_capaian" class="w-32 rounded border-gray-300 text-xs"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="rounded-md bg-blue-800 text-white px-4 py-1.5 text-xs hover:bg-blue-700">Simpan Target Tahunan</button>
                        </div>
                        <p class="mt-2 text-xs text-gray-400">% Realisasi dihitung otomatis dari Target &amp; Realisasi. Kosongkan seluruh kolom pada satu tahun untuk menghapus baris tahun tersebut.</p>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-400 text-sm">Belum ada data {{ $config['label'] }}.</div>
        @endforelse
    </div>
</div>
