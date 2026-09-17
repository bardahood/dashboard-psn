<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Ringkasan PSN</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 0; color: #1e3a8a; }
        h2 { font-size: 13px; margin-top: 24px; margin-bottom: 8px; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        .subtitle { color: #6b7280; margin-top: 2px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 5px 8px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; }
        .kpi-table td { text-align: center; }
        .kpi-value { font-size: 20px; font-weight: bold; color: #1e3a8a; }
        .kpi-label { color: #6b7280; font-size: 10px; }
        .footer { margin-top: 24px; color: #9ca3af; font-size: 9px; }
    </style>
</head>
<body>
    <h1>Laporan Ringkasan Proyek Strategis Nasional</h1>
    <p class="subtitle">Tim Koordinasi Perencanaan dan Pengendalian PSN, Kementerian PPN/Bappenas &mdash; per {{ $tanggal }}</p>

    <table class="kpi-table">
        <tr>
            <td style="width: 25%">
                <div class="kpi-value">{{ $total_psn }}</div>
                <div class="kpi-label">Total PSN</div>
            </td>
            <td style="width: 25%">
                <div class="kpi-value">{{ $total_pkpn }}</div>
                <div class="kpi-label">PKPN (wajib lapor bulanan)</div>
            </td>
            <td style="width: 25%">
                <div class="kpi-value">{{ $total_ro_kunci }}</div>
                <div class="kpi-label">RO Kunci/Critical Path</div>
            </td>
            <td style="width: 25%">
                <div class="kpi-value">{{ $psn_sumber_manual }}</div>
                <div class="kpi-label">Sumber Input Manual</div>
            </td>
        </tr>
    </table>

    <h2>Sebaran PSN per Klaster</h2>
    <table>
        <thead><tr><th>Klaster</th><th>Jumlah PSN</th></tr></thead>
        <tbody>
            @foreach ($per_klaster as $k)
                <tr><td>{{ $k->nama_klaster }}</td><td>{{ $k->psn_count }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Sebaran PSN per Status Lifecycle</h2>
    <table>
        <thead><tr><th>Status</th><th>Jumlah PSN</th></tr></thead>
        <tbody>
            @foreach ($per_status as $s)
                <tr><td>{{ $s->nama_status }}</td><td>{{ $s->psn_count }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Register Risiko per Level</h2>
    <table>
        <thead><tr><th>Level Risiko</th><th>Jumlah</th></tr></thead>
        <tbody>
            @forelse ($risiko_per_level as $level => $jumlah)
                <tr><td>{{ $level }}</td><td>{{ $jumlah }}</td></tr>
            @empty
                <tr><td colspan="2">Belum ada data risiko.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Ketersediaan Data per Sumber (Matriks Sandingan)</h2>
    <table>
        <thead><tr><th>RKP Pemutakhiran 2026</th><th>Data PEKS3</th><th>Data PSI</th><th>Permenko</th></tr></thead>
        <tbody>
            <tr>
                <td>{{ $ketersediaan_sumber->rkp ?? 0 }} PSN</td>
                <td>{{ $ketersediaan_sumber->peks3 ?? 0 }} PSN</td>
                <td>{{ $ketersediaan_sumber->psi ?? 0 }} PSN</td>
                <td>{{ $ketersediaan_sumber->permenko ?? 0 }} PSN</td>
            </tr>
        </tbody>
    </table>

    <p class="footer">Digenerate otomatis oleh Dashboard PSN pada {{ now()->format('d M Y H:i') }}. Data bersifat snapshot saat laporan diunduh.</p>
</body>
</html>
