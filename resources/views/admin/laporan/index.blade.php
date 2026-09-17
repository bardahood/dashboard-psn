<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reporting</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500">
                Bahan pendukung Laporan Presiden/Semester, digenerate langsung dari data terkini.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-medium text-gray-800 mb-1">Laporan Ringkasan (PDF)</h3>
                    <p class="text-xs text-gray-500 mb-4">Ringkasan portfolio, sebaran klaster/status, risiko, dan ketersediaan sumber data.</p>
                    <a href="{{ route('admin.laporan.ringkasan-pdf') }}" class="inline-block rounded-md bg-red-700 text-white px-4 py-2 text-sm hover:bg-red-600">
                        Unduh PDF
                    </a>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-medium text-gray-800 mb-1">Matriks Sandingan Sumber (Excel)</h3>
                    <p class="text-xs text-gray-500 mb-4">Sandingan ketersediaan data PSN di 4 sumber (RKP/PEKS3/PSI/Permenko).</p>
                    <a href="{{ route('admin.laporan.matriks-excel') }}" class="inline-block rounded-md bg-green-700 text-white px-4 py-2 text-sm hover:bg-green-600">
                        Unduh Excel
                    </a>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-medium text-gray-800 mb-1">Daftar PSN Lengkap (Excel)</h3>
                    <p class="text-xs text-gray-500 mb-4">Profil lengkap seluruh PSN: klaster, status, lokasi, nilai investasi, kelembagaan.</p>
                    <a href="{{ route('admin.laporan.daftar-psn-excel') }}" class="inline-block rounded-md bg-green-700 text-white px-4 py-2 text-sm hover:bg-green-600">
                        Unduh Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
