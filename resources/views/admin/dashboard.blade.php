<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Executive Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow rounded-lg p-5">
                    <div class="text-3xl font-bold text-blue-900">{{ $data['total_psn'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total PSN</div>
                </div>
                <div class="bg-white shadow rounded-lg p-5">
                    <div class="text-3xl font-bold text-blue-900">{{ $data['total_pkpn'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">PKPN (wajib lapor bulanan)</div>
                </div>
                <div class="bg-white shadow rounded-lg p-5">
                    <div class="text-3xl font-bold text-red-700">{{ $data['risiko_tinggi'] }} / {{ $data['total_risiko'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Risiko Tinggi/Sangat Tinggi</div>
                </div>
                <div class="bg-white shadow rounded-lg p-5">
                    <div class="text-3xl font-bold text-blue-900">{{ $data['kunjungan_bulan_ini'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Kunjungan Pengendalian Bulan Ini</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Sebaran PSN per Klaster</h3>
                    <canvas id="chartKlaster" height="260"></canvas>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Sebaran PSN per Status Lifecycle</h3>
                    <canvas id="chartStatus" height="260"></canvas>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-5 flex items-center justify-between">
                <div>
                    <div class="font-medium text-gray-700">Data dengan Sumber Input Manual</div>
                    <div class="text-sm text-gray-500">Menunggu sinkronisasi API PSI (belum tersedia).</div>
                </div>
                <div class="text-2xl font-bold text-amber-600">{{ $data['psn_manual_belum_sinkron'] }}</div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        const klasterLabels = @json($data['per_klaster']->pluck('nama_klaster'));
        const klasterData = @json($data['per_klaster']->pluck('psn_count'));
        new Chart(document.getElementById('chartKlaster'), {
            type: 'bar',
            data: { labels: klasterLabels, datasets: [{ label: 'Jumlah PSN', data: klasterData, backgroundColor: '#1e3a8a' }] },
            options: { indexAxis: 'y', plugins: { legend: { display: false } } }
        });

        const statusLabels = @json($data['per_status']->pluck('nama_status'));
        const statusData = @json($data['per_status']->pluck('psn_count'));
        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: ['#1e3a8a','#2563eb','#60a5fa','#93c5fd','#166534','#9ca3af'] }] },
        });
    </script>
</x-app-layout>
