@php $psn = $psn ?? null; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="sm:col-span-2">
        <x-input-label for="nama_psn" value="Nama PSN" />
        <textarea id="nama_psn" name="nama_psn" rows="2" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('nama_psn', $psn?->nama_psn) }}</textarea>
        <x-input-error :messages="$errors->get('nama_psn')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="klaster_id" value="Klaster" />
        <select id="klaster_id" name="klaster_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">-- Pilih Klaster --</option>
            @foreach ($klasterOptions as $k)
                <option value="{{ $k->id }}" @selected(old('klaster_id', $psn?->klaster_id) == $k->id)>{{ $k->nama_klaster }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="status_psn_id" value="Status PSN" />
        <select id="status_psn_id" name="status_psn_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">-- Pilih Status --</option>
            @foreach ($statusOptions as $s)
                <option value="{{ $s->id }}" @selected(old('status_psn_id', $psn?->status_psn_id) == $s->id)>{{ $s->nama_status }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="provinsi_id" value="Provinsi" />
        <select id="provinsi_id" name="provinsi_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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
        <x-input-label for="tipe_hierarki" value="Tipe Hierarki" />
        <select id="tipe_hierarki" name="tipe_hierarki" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">-- Tidak Ditetapkan --</option>
            <option value="PKPN" @selected(old('tipe_hierarki', $psn?->tipe_hierarki) == 'PKPN')>PKPN (wajib lapor bulanan)</option>
            <option value="PSN" @selected(old('tipe_hierarki', $psn?->tipe_hierarki) == 'PSN')>PSN (bulanan/triwulanan)</option>
        </select>
    </div>

    <div>
        <x-input-label for="tahun_penyelesaian" value="Tahun Penyelesaian" />
        <x-text-input type="number" id="tahun_penyelesaian" name="tahun_penyelesaian" class="mt-1 block w-full" value="{{ old('tahun_penyelesaian', $psn?->tahun_penyelesaian) }}" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="output_akhir" value="Output Akhir" />
        <textarea id="output_akhir" name="output_akhir" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('output_akhir', $psn?->output_akhir) }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="tujuan_utama" value="Tujuan Utama" />
        <textarea id="tujuan_utama" name="tujuan_utama" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('tujuan_utama', $psn?->tujuan_utama) }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="urgensi" value="Urgensi & Dasar Hukum" />
        <textarea id="urgensi" name="urgensi" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('urgensi', $psn?->urgensi) }}</textarea>
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
        <x-input-label for="pengusul_instansi_id" value="Pengusul" />
        <select id="pengusul_instansi_id" name="pengusul_instansi_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">-- Pilih Instansi --</option>
            @foreach ($instansiOptions as $i)
                <option value="{{ $i->id }}" @selected(old('pengusul_instansi_id', $psn?->pengusul_instansi_id) == $i->id)>{{ $i->nama_instansi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="pengelola_instansi_id" value="Pengelola" />
        <select id="pengelola_instansi_id" name="pengelola_instansi_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">-- Pilih Instansi --</option>
            @foreach ($instansiOptions as $i)
                <option value="{{ $i->id }}" @selected(old('pengelola_instansi_id', $psn?->pengelola_instansi_id) == $i->id)>{{ $i->nama_instansi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="kontraktor_instansi_id" value="Kontraktor" />
        <select id="kontraktor_instansi_id" name="kontraktor_instansi_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">-- Pilih Instansi --</option>
            @foreach ($instansiOptions as $i)
                <option value="{{ $i->id }}" @selected(old('kontraktor_instansi_id', $psn?->kontraktor_instansi_id) == $i->id)>{{ $i->nama_instansi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="supervisi_instansi_id" value="Supervisi" />
        <select id="supervisi_instansi_id" name="supervisi_instansi_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">-- Pilih Instansi --</option>
            @foreach ($instansiOptions as $i)
                <option value="{{ $i->id }}" @selected(old('supervisi_instansi_id', $psn?->supervisi_instansi_id) == $i->id)>{{ $i->nama_instansi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="kode_rkp" value="Kode RKP" />
        <x-text-input id="kode_rkp" name="kode_rkp" class="mt-1 block w-full" value="{{ old('kode_rkp', $psn?->kode_rkp) }}" />
    </div>

    <div>
        <x-input-label for="sumber_input" value="Sumber Input" />
        <select id="sumber_input" name="sumber_input" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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
        <textarea id="asta_cita" name="asta_cita" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('asta_cita', $psn?->asta_cita) }}</textarea>
    </div>
</div>
