<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Executive Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" /></svg>
                        </span>
                    </div>
                    <div class="text-3xl font-bold text-blue-900 mt-3">{{ $data['total_psn'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total PSN</div>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-5 hover:shadow-md transition-shadow">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-gold-50 text-gold-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </span>
                    <div class="text-3xl font-bold text-blue-900 mt-3">{{ $data['total_pkpn'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">PKPN (wajib lapor bulanan)</div>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-5 hover:shadow-md transition-shadow">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    </span>
                    <div class="text-3xl font-bold text-red-700 mt-3">{{ $data['risiko_tinggi'] }} / {{ $data['total_risiko'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Risiko Tinggi/Sangat Tinggi</div>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-5 hover:shadow-md transition-shadow">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                    <div class="text-3xl font-bold text-blue-900 mt-3">{{ $data['kunjungan_bulan_ini'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Kunjungan Pengendalian Bulan Ini</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Sebaran PSN per Klaster</h3>
                    <canvas id="chartKlaster" height="260"></canvas>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Sebaran PSN per Status Lifecycle</h3>
                    <canvas id="chartStatus" height="260"></canvas>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-5 flex items-center justify-between">
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
