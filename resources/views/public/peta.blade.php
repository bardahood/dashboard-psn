@extends('layouts.public')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-blue-900">Peta Sebaran PSN</h1>
        <p class="text-sm text-gray-500 mt-1">
            Marker ditempatkan per-provinsi (agregat), klik marker untuk melihat daftar PSN pada provinsi tersebut.
        </p>
    </div>

    <div id="peta-psn" class="rounded-lg shadow" style="height: 560px;"></div>

    <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-4 text-xs text-gray-400">
        Sumber peta: &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" class="underline">OpenStreetMap</a> contributors.
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1/dist/leaflet.css" />
<script src="https://cdn.jsdelivr.net/npm/leaflet@1/dist/leaflet.js"></script>
<script>
    const markers = @json($markers);

    const map = L.map('peta-psn').setView([-2.5, 118], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    markers.forEach((m) => {
        const radius = Math.min(10 + m.jumlah * 3, 30);
        const marker = L.circleMarker([m.lat, m.lng], {
            radius,
            color: '#1e3a8a',
            fillColor: '#2563eb',
            fillOpacity: 0.6,
        }).addTo(map);

        const daftarHtml = m.daftar.slice(0, 8).map((p) =>
            `<li><a href="/psn/${p.id}" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">${p.nama}</a></li>`
        ).join('');

        marker.bindPopup(`
            <div class="text-sm">
                <div class="font-semibold mb-1">${m.provinsi} (${m.jumlah} PSN)</div>
                <ul class="list-disc list-inside space-y-0.5">${daftarHtml}</ul>
                ${m.daftar.length > 8 ? `<div class="text-xs text-gray-400 mt-1">+ ${m.daftar.length - 8} PSN lainnya</div>` : ''}
            </div>
        `);
    });
</script>
@endsection
