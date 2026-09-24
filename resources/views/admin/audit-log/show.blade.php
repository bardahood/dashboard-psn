<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Audit Log</h2>
            <a href="{{ url()->previous(route('admin.audit-log')) }}" class="text-sm text-blue-800 hover:text-blue-900 hover:underline underline-offset-2">&larr; Kembali</a>
        </div>
    </x-slot>

    @php
        // Data field-demi-field TANPA pemotongan teks (beda dari pratinjau di
        // halaman index yang dipotong 25 karakter) -- inilah "isi datanya yang
        // dirubah, data apa saja" yang diminta.
        $format = function ($value) {
            if ($value === null) {
                return ['teks' => '-', 'kosong' => true];
            }
            if (is_bool($value)) {
                return ['teks' => $value ? 'Ya' : 'Tidak', 'kosong' => false];
            }
            if (is_array($value)) {
                return ['teks' => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'kosong' => false, 'mono' => true];
            }

            return ['teks' => (string) $value, 'kosong' => $value === ''];
        };

        $semuaField = collect(array_keys(($auditLog->nilai_baru ?? []) + ($auditLog->nilai_lama ?? [])))->unique()->sort()->values();
    @endphp

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-8 gap-y-4 text-sm">
                    <div><dt class="text-gray-500">Waktu</dt><dd class="font-medium">{{ $auditLog->created_at->format('d M Y H:i:s') }}</dd></div>
                    <div><dt class="text-gray-500">Tabel</dt><dd class="font-mono text-xs mt-1">{{ $auditLog->nama_tabel }}</dd></div>
                    <div><dt class="text-gray-500">Record ID</dt><dd class="font-medium">{{ $auditLog->record_id }}</dd></div>
                    <div><dt class="text-gray-500">Aksi</dt><dd>
                        <span class="rounded-full text-xs px-2.5 py-1 font-medium
                            {{ $auditLog->aksi === 'insert' ? 'bg-green-100 text-green-700' : ($auditLog->aksi === 'update' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                            {{ $auditLog->aksi }}
                        </span>
                    </dd></div>
                    <div><dt class="text-gray-500">Oleh</dt><dd class="font-medium">{{ $auditLog->user?->name ?? $auditLog->pic?->nama_pic ?? '-' }} {{ $auditLog->user?->email ? '('.$auditLog->user->email.')' : '' }}</dd></div>
                    <div><dt class="text-gray-500">Peran</dt><dd>
                        @if ($auditLog->role)
                            <span class="rounded-full bg-gray-100 text-gray-700 px-2.5 py-1 text-xs font-medium">{{ $auditLog->role }}</span>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </dd></div>
                </dl>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Kolom</th>
                            @if ($auditLog->aksi === 'update')
                                <th class="px-4 py-3 w-2/5">Nilai Lama</th>
                                <th class="px-4 py-3 w-2/5">Nilai Baru</th>
                            @else
                                <th class="px-4 py-3 w-4/5">{{ $auditLog->aksi === 'insert' ? 'Nilai (data baru)' : 'Nilai (sebelum dihapus)' }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($semuaField as $field)
                            @php
                                $lama = $format($auditLog->nilai_lama[$field] ?? null);
                                $baru = $format($auditLog->nilai_baru[$field] ?? null);
                                $berubah = $auditLog->aksi === 'update' && ($auditLog->nilai_lama[$field] ?? null) !== ($auditLog->nilai_baru[$field] ?? null);
                            @endphp
                            <tr class="align-top {{ $berubah ? 'bg-amber-50/40' : '' }}">
                                <td class="px-4 py-3 font-mono text-xs whitespace-nowrap">{{ $field }}</td>
                                @if ($auditLog->aksi === 'update')
                                    <td class="px-4 py-3 {{ $lama['kosong'] ? 'text-gray-300' : 'text-gray-700' }} {{ $lama['mono'] ?? false ? 'font-mono text-xs whitespace-pre-wrap' : 'whitespace-pre-wrap break-words' }}">{{ $lama['teks'] }}</td>
                                    <td class="px-4 py-3 {{ $baru['kosong'] ? 'text-gray-300' : 'font-medium text-gray-900' }} {{ $baru['mono'] ?? false ? 'font-mono text-xs whitespace-pre-wrap' : 'whitespace-pre-wrap break-words' }}">{{ $baru['teks'] }}</td>
                                @else
                                    @php $satu = $auditLog->aksi === 'insert' ? $baru : $lama; @endphp
                                    <td class="px-4 py-3 {{ $satu['kosong'] ? 'text-gray-300' : 'text-gray-900' }} {{ $satu['mono'] ?? false ? 'font-mono text-xs whitespace-pre-wrap' : 'whitespace-pre-wrap break-words' }}">{{ $satu['teks'] }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Tidak ada data kolom tercatat untuk baris ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
