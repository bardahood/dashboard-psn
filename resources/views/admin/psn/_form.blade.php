@php $psn = $psn ?? null; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="sm:col-span-2">
        <x-input-label for="nama_psn" value="Nama PSN" />
        <textarea id="nama_psn" name="nama_psn" rows="2" required
                  class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">{{ old('nama_psn', $psn?->nama_psn) }}</textarea>
        <x-input-error :messages="$errors->get('nama_psn')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="nama_sub_proyek" value="Sub Proyek" />
        <p class="text-xs text-gray-400 mb-1">Isi bila PSN ini merupakan salah satu komponen/sub-proyek dari sebuah Program dengan nama PSN yang sama (Risalah Rapat 21 Sept 2026).</p>
        <x-text-input id="nama_sub_proyek" name="nama_sub_proyek" class="mt-1 block w-full" value="{{ old('nama_sub_proyek', $psn?->nama_sub_proyek) }}" />
    </div>

    <div>
        <x-input-label for="klaster_id" value="Klaster PSN" />
        <select id="klaster_id" name="klaster_id" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Pilih Klaster --</option>
            @foreach ($klasterOptions as $k)
                <option value="{{ $k->id }}" @selected(old('klaster_id', $psn?->klaster_id) == $k->id)>{{ $k->nama_klaster }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="status_psn_id" value="Status PSN" />
        <select id="status_psn_id" name="status_psn_id" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Pilih Status --</option>
            @foreach ($statusOptions as $s)
                <option value="{{ $s->id }}" @selected(old('status_psn_id', $psn?->status_psn_id) == $s->id)>{{ $s->nama_status }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="provinsi_id" value="Provinsi" />
        <select id="provinsi_id" name="provinsi_id" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Pilih Provinsi --</option>
            @foreach ($provinsiOptions as $p)
                <option value="{{ $p->id }}" @selected(old('provinsi_id', $psn?->provinsi_id) == $p->id)>{{ $p->nama_provinsi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="kabupaten_kota" value="Kabupaten/Kota" />
        <x-text-input id="kabupaten_kota" name="kabupaten_kota" class="mt-1 block w-full" value="{{ old('kabupaten_kota', $psn?->kabupaten_kota) }}" />
    </div>

    <div>
        <x-input-label for="kategori_usulan" value="Kategori Usulan" />
        <select id="kategori_usulan" name="kategori_usulan" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Belum Ditetapkan --</option>
            <option value="Carryover" @selected(old('kategori_usulan', $psn?->kategori_usulan) == 'Carryover')>Carryover (PSN Berjalan)</option>
            <option value="Usulan Baru" @selected(old('kategori_usulan', $psn?->kategori_usulan) == 'Usulan Baru')>Usulan Baru</option>
        </select>
    </div>

    <div>
        <x-input-label for="tipe_hierarki" value="Klaster PKPN" />
        <select id="tipe_hierarki" name="tipe_hierarki" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Tidak Ditetapkan --</option>
            <option value="PKPN" @selected(old('tipe_hierarki', $psn?->tipe_hierarki) == 'PKPN')>PKPN (wajib lapor bulanan)</option>
            <option value="PSN" @selected(old('tipe_hierarki', $psn?->tipe_hierarki) == 'PSN')>PSN (bulanan/triwulanan)</option>
        </select>
    </div>

    <div>
        <x-input-label for="tahun_penyelesaian" value="Tahun Penyelesaian" />
        <x-text-input type="number" id="tahun_penyelesaian" name="tahun_penyelesaian" class="mt-1 block w-full" value="{{ old('tahun_penyelesaian', $psn?->tahun_penyelesaian) }}" />
    </div>

    <div>
        <x-input-label for="bulan_penyelesaian" value="Bulan Penyelesaian" />
        <select id="bulan_penyelesaian" name="bulan_penyelesaian" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Pilih Bulan --</option>
            @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $namaBulan)
                <option value="{{ $i + 1 }}" @selected(old('bulan_penyelesaian', $psn?->bulan_penyelesaian) == $i + 1)>{{ $namaBulan }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('bulan_penyelesaian')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="output_akhir" value="Output Akhir Tahun yang Dicapai pada Akhir Proyek" />
        <textarea id="output_akhir" name="output_akhir" rows="2" minlength="20" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">{{ old('output_akhir', $psn?->output_akhir) }}</textarea>
        <x-input-error :messages="$errors->get('output_akhir')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="data_teknis" value="Data Teknis" />
        <p class="text-xs text-gray-400 mb-1">Mis. luas kawasan, panjang jalan, kapasitas produksi, dll (Risalah Rapat 21 Sept 2026, pengganti "Spesifikasi Teknis").</p>
        <textarea id="data_teknis" name="data_teknis" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">{{ old('data_teknis', $psn?->data_teknis) }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="tujuan_utama" value="Tujuan Utama" />
        <textarea id="tujuan_utama" name="tujuan_utama" rows="3" minlength="20" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">{{ old('tujuan_utama', $psn?->tujuan_utama) }}</textarea>
        <x-input-error :messages="$errors->get('tujuan_utama')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="urgensi" value="Urgensi & Dasar Hukum" />
        <textarea id="urgensi" name="urgensi" rows="3" minlength="20" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">{{ old('urgensi', $psn?->urgensi) }}</textarea>
        <x-input-error :messages="$errors->get('urgensi')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nilai_investasi_apbn_rp" value="Nilai Investasi APBN (Rp)" />
        <x-text-input type="number" step="0.01" id="nilai_investasi_apbn_rp" name="nilai_investasi_apbn_rp" class="mt-1 block w-full" value="{{ old('nilai_investasi_apbn_rp', $psn?->nilai_investasi_apbn_rp) }}" />
    </div>

    <div>
        <x-input-label for="nilai_investasi_non_apbn_rp" value="Nilai Investasi Non-APBN (Rp)" />
        <x-text-input type="number" step="0.01" id="nilai_investasi_non_apbn_rp" name="nilai_investasi_non_apbn_rp" class="mt-1 block w-full" value="{{ old('nilai_investasi_non_apbn_rp', $psn?->nilai_investasi_non_apbn_rp) }}" />
    </div>

    <div>
        <x-input-label for="indikasi_sumber_pendanaan" value="Indikasi Sumber Pendanaan" />
        <select id="indikasi_sumber_pendanaan" name="indikasi_sumber_pendanaan" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Pilih --</option>
            @foreach (['APBN', 'APBD', 'BUMN', 'BU-Swasta', 'Lainnya'] as $sumber)
                <option value="{{ $sumber }}" @selected(old('indikasi_sumber_pendanaan', $psn?->indikasi_sumber_pendanaan) == $sumber)>{{ $sumber }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="pengusul_instansi_id" value="Pengusul" />
        <select id="pengusul_instansi_id" name="pengusul_instansi_id" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Pilih Instansi --</option>
            @foreach ($instansiOptions as $i)
                <option value="{{ $i->id }}" @selected(old('pengusul_instansi_id', $psn?->pengusul_instansi_id) == $i->id)>{{ $i->nama_instansi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="pengelola_instansi_id" value="Pengelola" />
        <select id="pengelola_instansi_id" name="pengelola_instansi_id" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Pilih Instansi --</option>
            @foreach ($instansiOptions as $i)
                <option value="{{ $i->id }}" @selected(old('pengelola_instansi_id', $psn?->pengelola_instansi_id) == $i->id)>{{ $i->nama_instansi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="kontraktor_instansi_id" value="Kontraktor" />
        <select id="kontraktor_instansi_id" name="kontraktor_instansi_id" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Pilih Instansi --</option>
            @foreach ($instansiOptions as $i)
                <option value="{{ $i->id }}" @selected(old('kontraktor_instansi_id', $psn?->kontraktor_instansi_id) == $i->id)>{{ $i->nama_instansi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="supervisi_instansi_id" value="Supervisi" />
        <select id="supervisi_instansi_id" name="supervisi_instansi_id" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="">-- Pilih Instansi --</option>
            @foreach ($instansiOptions as $i)
                <option value="{{ $i->id }}" @selected(old('supervisi_instansi_id', $psn?->supervisi_instansi_id) == $i->id)>{{ $i->nama_instansi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="kode_rkp" value="Diagram Kerangka Kerja Logis (Kode RKP)" />
        <x-text-input id="kode_rkp" name="kode_rkp" class="mt-1 block w-full" value="{{ old('kode_rkp', $psn?->kode_rkp) }}" />
    </div>

    <div>
        <x-input-label for="peks" value="PEKS Penanggung Jawab" />
        <p class="text-xs text-gray-400 mb-1">Unit PEKS internal Kementerian PPN/Bappenas yang menangani PSN ini (Master Data PSN Kode).</p>
        <x-text-input id="peks" name="peks" class="mt-1 block w-full" value="{{ old('peks', $psn?->peks) }}" />
    </div>

    <div>
        <x-input-label for="unit_kerja" value="Unit Kerja" />
        <x-text-input id="unit_kerja" name="unit_kerja" class="mt-1 block w-full" value="{{ old('unit_kerja', $psn?->unit_kerja) }}" />
    </div>

    <div>
        <x-input-label for="sumber_input" value="Sumber Input" />
        <select id="sumber_input" name="sumber_input" required class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">
            <option value="Manual" @selected(old('sumber_input', $psn?->sumber_input ?? 'Manual') == 'Manual')>Manual</option>
            <option value="API PSI" @selected(old('sumber_input', $psn?->sumber_input) == 'API PSI')>API PSI</option>
        </select>
    </div>

    <div>
        <x-input-label for="periode_update" value="Periode Update" />
        <x-text-input type="date" id="periode_update" name="periode_update" class="mt-1 block w-full" value="{{ old('periode_update', $psn?->periode_update?->format('Y-m-d')) }}" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="asta_cita" value="Keterkaitan dengan Asta Cita" />
        <textarea id="asta_cita" name="asta_cita" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 transition-colors shadow-sm">{{ old('asta_cita', $psn?->asta_cita) }}</textarea>
    </div>
</div>
