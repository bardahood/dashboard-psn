@extends('layouts.public')

@section('content')
<div class="space-y-8">
    <h1 class="text-2xl font-bold text-blue-900">Statistik PSN</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Distribusi per Klaster</h2>
            <canvas id="chartKlaster" height="260"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Distribusi per Status Lifecycle</h2>
            <canvas id="chartStatus" height="260"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="font-semibold text-gray-700 mb-4">10 Provinsi dengan PSN Terbanyak</h2>
        <canvas id="chartProvinsi" height="120"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const klasterLabels = @json($statistik['per_klaster']->pluck('nama_klaster'));
    const klasterData = @json($statistik['per_klaster']->pluck('psn_count'));
    new Chart(document.getElementById('chartKlaster'), {
        type: 'bar',
        data: { labels: klasterLabels, datasets: [{ label: 'Jumlah PSN', data: klasterData, backgroundColor: '#1e3a8a' }] },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });

    const statusLabels = @json($statistik['per_status']->pluck('nama_status'));
    const statusData = @json($statistik['per_status']->pluck('psn_count'));
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: ['#1e3a8a','#2563eb','#60a5fa','#93c5fd','#166534','#9ca3af'] }] },
    });

    const provinsiLabels = @json($statistik['per_provinsi']->pluck('nama_provinsi'));
    const provinsiData = @json($statistik['per_provinsi']->pluck('psn_count'));
    new Chart(document.getElementById('chartProvinsi'), {
        type: 'bar',
        data: { labels: provinsiLabels, datasets: [{ label: 'Jumlah PSN', data: provinsiData, backgroundColor: '#166534' }] },
        options: { plugins: { legend: { display: false } } }
    });
</script>
@endsection
