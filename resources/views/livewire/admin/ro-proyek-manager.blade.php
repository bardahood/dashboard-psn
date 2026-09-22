<div class="space-y-6">
    <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
        <h3 class="font-semibold text-gray-700 mb-4">{{ $editingId ? 'Ubah' : 'Tambah' }} RO/Proyek/Aktivitas</h3>

        <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                <select wire:model.live="form.tipe" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="RO">RO/Proyek (induk)</option>
                    <option value="Aktivitas">Aktivitas (turunan)</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama RO/Proyek/Aktivitas (Kegiatan) <span class="text-red-500">*</span></label>
                <input type="text" wire:model="form.nama_ro" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                @error('form.nama_ro') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @if ($form['tipe'] === 'Aktivitas')
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">RO/Proyek Induk</label>
                    <select wire:model="form.ro_induk_id" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                        <option value="">-- Pilih RO Induk --</option>
                        @foreach ($roIndukOptions as $id => $nama)
                            <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end pb-1.5">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="form.is_ro_kunci" class="rounded border-gray-300">
                        Critical Path
                    </label>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                <input type="text" wire:model="form.lokasi" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                @error('form.lokasi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pelaksana</label>
                <select wire:model="form.instansi_pelaksana_id" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih dari Stakeholder Mapping --</option>
                    @foreach ($instansiOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Akhir <span class="text-red-500">*</span> <span class="text-gray-400 font-normal">(s/d akhir 2029)</span></label>
                <input type="text" wire:model="form.target_akhir" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                @error('form.target_akhir') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                <select wire:model="form.satuan" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Unit">Unit</option>
                    <option value="Persentase">Persentase</option>
                </select>
            </div>
            @if ($form['tipe'] === 'RO')
                <div class="flex items-end pb-1.5">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="form.is_ro_kunci" class="rounded border-gray-300">
                        RO Kunci
                    </label>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Baseline</label>
                <input type="text" wire:model="form.baseline" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Baseline</label>
                <input type="number" wire:model="form.baseline_tahun" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            </div>

            <div class="sm:col-span-3 flex justify-end gap-3">
                @if ($editingId)
                    <button type="button" wire:click="resetForm" class="rounded-md border px-4 py-2 text-sm">Batal</button>
                @endif
                <button type="submit" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">
                    {{ $editingId ? 'Simpan Perubahan' : 'Tambah' }}
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($roIndukList as $ro)
            <div wire:key="ro-{{ $ro->id }}" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="text-sm">
                        <div class="font-medium text-gray-800">
                            {{ $ro->nama_ro }}
                            @if ($ro->is_ro_kunci) <span class="ml-1 text-xs rounded-full bg-red-100 text-red-700 px-2 py-0.5">RO Kunci</span> @endif
                            @if ($ro->is_ro_kunci && $ro->targetPeriode->isEmpty())
                                <span class="ml-1 text-xs rounded-full bg-amber-100 text-amber-700 px-2 py-0.5">&#9888; Belum dijabarkan per periode</span>
                            @endif
                        </div>
                        <div class="text-gray-500 text-xs mt-0.5">
                            {{ $ro->satuan ?? '-' }} &middot; {{ $ro->lokasi ?? '-' }} &middot; {{ $ro->instansiPelaksana?->nama_instansi ?? '-' }}
                        </div>
                    </div>
                    <div class="flex gap-3 text-sm whitespace-nowrap">
                        <button wire:click="togglePeriode({{ $ro->id }})" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">
                            {{ $expandedPeriodeRoId === $ro->id ? 'Tutup Periode' : 'Kelola Periode' }}
                        </button>
                        <button wire:click="edit({{ $ro->id }})" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">Ubah</button>
                        <button wire:click="delete({{ $ro->id }})" wire:confirm="Hapus RO ini beserta seluruh aktivitas & periode turunannya?" class="text-red-700 hover:underline">Hapus</button>
                    </div>
                </div>

                @include('livewire.admin._ro-periode-panel', ['ro' => $ro])

                @if ($ro->anak->isNotEmpty())
                    <div class="mt-3 pl-4 border-l-2 border-gray-100 space-y-2">
                        @foreach ($ro->anak as $aktivitas)
                            <div wire:key="aktivitas-{{ $aktivitas->id }}" class="flex items-start justify-between gap-4 text-sm">
                                <div>
                                    <span class="text-gray-700">&#8618; {{ $aktivitas->nama_ro }}</span>
                                    @if ($aktivitas->is_ro_kunci) <span class="ml-1 text-xs rounded-full bg-red-100 text-red-700 px-2 py-0.5">Critical Path</span> @endif
                                    @if ($aktivitas->is_ro_kunci && $aktivitas->targetPeriode->isEmpty())
                                        <span class="ml-1 text-xs rounded-full bg-amber-100 text-amber-700 px-2 py-0.5">&#9888; Belum dijabarkan</span>
                                    @endif
                                    <span class="text-gray-400 text-xs ml-1">{{ $aktivitas->satuan }}</span>
                                </div>
                                <div class="flex gap-3 whitespace-nowrap">
                                    <button wire:click="togglePeriode({{ $aktivitas->id }})" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors text-xs">
                                        {{ $expandedPeriodeRoId === $aktivitas->id ? 'Tutup' : 'Periode' }}
                                    </button>
                                    <button wire:click="edit({{ $aktivitas->id }})" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors text-xs">Ubah</button>
                                    <button wire:click="delete({{ $aktivitas->id }})" wire:confirm="Hapus aktivitas ini?" class="text-red-700 hover:underline text-xs">Hapus</button>
                                </div>
                            </div>
                            @include('livewire.admin._ro-periode-panel', ['ro' => $aktivitas])
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 text-center text-gray-400 text-sm">Belum ada data RO/Proyek.</div>
        @endforelse
    </div>
</div>
