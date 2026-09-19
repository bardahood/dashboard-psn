<div class="space-y-6">
    <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
        <h3 class="font-semibold text-gray-700 mb-4">{{ $editingId ? 'Ubah' : 'Tambah' }} {{ $config['label'] }}</h3>

        <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ($config['fields'] as $field)
                <div class="{{ in_array($field['type'], ['textarea']) ? 'sm:col-span-2' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $field['label'] }}@if($field['required'] ?? false) <span class="text-red-500">*</span>@endif
                    </label>

                    @if ($field['type'] === 'textarea')
                        <textarea wire:model="form.{{ $field['name'] }}" rows="2"
                                  class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm"></textarea>
                    @elseif ($field['type'] === 'select')
                        <select wire:model="form.{{ $field['name'] }}" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach ($optionsByField[$field['name']] ?? [] as $value => $labelText)
                                <option value="{{ $value }}">{{ $labelText }}</option>
                            @endforeach
                        </select>
                    @elseif ($field['type'] === 'number')
                        <input type="number" wire:model="form.{{ $field['name'] }}" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    @elseif ($field['type'] === 'date')
                        <input type="date" wire:model="form.{{ $field['name'] }}" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    @else
                        <input type="text" wire:model="form.{{ $field['name'] }}" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    @endif

                    @error('form.'.$field['name'])
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

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

    <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    @foreach ($config['fields'] as $field)
                        <th class="px-4 py-3">{{ $field['label'] }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($rows as $row)
                    <tr wire:key="row-{{ $row->id }}" class="hover:bg-gray-50">
                        @foreach ($config['fields'] as $field)
                            <td class="px-4 py-3 align-top">
                                @php $value = $row->{$field['name']}; @endphp
                                @if ($field['type'] === 'select' && isset($optionsByField[$field['name']][$value]))
                                    {{ $optionsByField[$field['name']][$value] }}
                                @elseif (is_bool($value))
                                    {{ $value ? 'Ya' : 'Tidak' }}
                                @else
                                    {{ \Illuminate\Support\Str::limit((string) $value, 80) ?: '-' }}
                                @endif
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            <button wire:click="edit({{ $row->id }})" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">Ubah</button>
                            <button wire:click="delete({{ $row->id }})" wire:confirm="Hapus data ini?" class="text-red-700 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($config['fields']) + 1 }}" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $rows->links() }}
</div>
