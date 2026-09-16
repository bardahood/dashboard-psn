@if ($expandedPeriodeRoId === $ro->id)
    <div class="mt-3 border-t pt-3">
        <form wire:submit="addPeriode" class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-gray-500 mb-1">Tahun</label>
                <input type="number" wire:model="periodeForm.tahun" class="w-full rounded border-gray-300 text-xs">
            </div>
            <div>
                <label class="block text-gray-500 mb-1">Tipe Periode</label>
                <select wire:model.live="periodeForm.tipe_periode" class="w-full rounded border-gray-300 text-xs">
                    <option value="TAHUNAN">Tahunan</option>
                    <option value="TRIWULANAN">Triwulanan</option>
                    <option value="BULANAN">Bulanan</option>
                </select>
            </div>
            @if ($periodeForm['tipe_periode'] === 'TRIWULANAN')
                <div>
                    <label class="block text-gray-500 mb-1">Triwulan</label>
                    <select wire:model="periodeForm.triwulan" class="w-full rounded border-gray-300 text-xs">
                        <option value="">-</option>
                        @for ($i = 1; $i <= 4; $i++) <option value="{{ $i }}">TW {{ $i }}</option> @endfor
                    </select>
                </div>
            @elseif ($periodeForm['tipe_periode'] === 'BULANAN')
                <div>
                    <label class="block text-gray-500 mb-1">Bulan</label>
                    <select wire:model="periodeForm.bulan" class="w-full rounded border-gray-300 text-xs">
                        <option value="">-</option>
                        @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}">Bulan {{ $i }}</option> @endfor
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-gray-500 mb-1">Target</label>
                <input type="number" step="0.01" wire:model="periodeForm.target" class="w-full rounded border-gray-300 text-xs">
            </div>
            <div>
                <label class="block text-gray-500 mb-1">Realisasi Fisik</label>
                <input type="number" step="0.01" wire:model="periodeForm.realisasi_fisik" class="w-full rounded border-gray-300 text-xs">
            </div>
            <div>
                <label class="block text-gray-500 mb-1">Pembiayaan Rencana (Juta Rp)</label>
                <input type="number" step="0.01" wire:model="periodeForm.pembiayaan_rencana_juta_rp" class="w-full rounded border-gray-300 text-xs">
            </div>
            <div>
                <label class="block text-gray-500 mb-1">Realisasi Anggaran (Juta Rp)</label>
                <input type="number" step="0.01" wire:model="periodeForm.realisasi_anggaran_juta_rp" class="w-full rounded border-gray-300 text-xs">
            </div>
            <div>
                <label class="block text-gray-500 mb-1">Indikasi Sumber Pendanaan</label>
                <input type="text" wire:model="periodeForm.indikasi_sumber_pendanaan" placeholder="APBN/APBD/BUMN/BU-Swasta" class="w-full rounded border-gray-300 text-xs">
            </div>
            <div>
                <label class="block text-gray-500 mb-1">Status</label>
                <input type="text" wire:model="periodeForm.status" class="w-full rounded border-gray-300 text-xs">
            </div>
            <div class="col-span-2 sm:col-span-4">
                <label class="block text-gray-500 mb-1">Permasalahan</label>
                <input type="text" wire:model="periodeForm.permasalahan" class="w-full rounded border-gray-300 text-xs">
            </div>
            <div class="col-span-2 sm:col-span-4 flex justify-end">
                <button type="submit" class="rounded-md bg-blue-800 text-white px-3 py-1.5 text-xs hover:bg-blue-700">Tambah Periode</button>
            </div>
        </form>

        <table class="min-w-full text-xs mt-3">
            <thead class="text-left text-gray-500">
                <tr>
                    <th class="py-1 pr-3">Periode</th>
                    <th class="py-1 pr-3">Target</th>
                    <th class="py-1 pr-3">Realisasi Fisik</th>
                    <th class="py-1 pr-3">Realisasi Anggaran</th>
                    <th class="py-1 pr-3">Status</th>
                    <th class="py-1"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($periodeList as $p)
                    <tr wire:key="periode-{{ $p->id }}">
                        <td class="py-1 pr-3">{{ $p->tahun }} {{ $p->tipe_periode }}@if($p->triwulan) TW{{ $p->triwulan }}@endif@if($p->bulan) Bln{{ $p->bulan }}@endif</td>
                        <td class="py-1 pr-3">{{ $p->target ?? '-' }}</td>
                        <td class="py-1 pr-3">{{ $p->realisasi_fisik ?? '-' }}</td>
                        <td class="py-1 pr-3">{{ $p->realisasi_anggaran_juta_rp ?? '-' }}</td>
                        <td class="py-1 pr-3">{{ $p->status ?? '-' }}</td>
                        <td class="py-1 text-right">
                            <button wire:click="deletePeriode({{ $p->id }})" wire:confirm="Hapus data periode ini?" class="text-red-700 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-2 text-center text-gray-400">Belum ada data periode.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
