{{--
    Form mandiri untuk upload Visualisasi Kerangka Kelembagaan, dipisah dari
    form utama Gambaran Umum (struktur 5-tab Project Profile: field ini kini
    tampil di tab "Upload Dokumen"). Tetap submit ke admin.psn.update yang
    sama -- nama_psn & sumber_input disertakan sebagai hidden field karena
    keduanya wajib diisi validasi controller (PsnController::validated()),
    walau tidak diubah dari form ini.
--}}
<form method="POST" action="{{ route('admin.psn.update', $psn) }}" enctype="multipart/form-data" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
    @csrf
    @method('PUT')
    <input type="hidden" name="nama_psn" value="{{ $psn->nama_psn }}">
    <input type="hidden" name="sumber_input" value="{{ $psn->sumber_input ?? 'Manual' }}">

    <h3 class="font-semibold text-gray-700 mb-4">Visualisasi Kerangka Kelembagaan</h3>
    <p class="text-xs text-gray-400 mb-3">
        Diagram skematik hubungan antar pihak (delegasi/pengarahan, akuntabilitas &amp; pelaporan, koordinasi &amp; kolaborasi) sesuai Pedoman Project Profile PSN. Format gambar, maks. 4MB.
    </p>

    @if ($psn->diagram_kelembagaan_path)
        <div class="mb-3 flex items-center gap-3">
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($psn->diagram_kelembagaan_path) }}" alt="Kerangka Kelembagaan" class="h-24 w-auto rounded border">
            <label class="inline-flex items-center gap-2 text-xs text-red-700">
                <input type="checkbox" name="hapus_diagram_kelembagaan" value="1" class="rounded border-gray-300">
                Hapus diagram ini
            </label>
        </div>
    @endif

    <input type="file" id="diagram_kelembagaan" name="diagram_kelembagaan" accept="image/*" class="block w-full text-sm">
    <x-input-error :messages="$errors->get('diagram_kelembagaan')" class="mt-2" />

    <div class="mt-4 flex justify-end">
        <button class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">Simpan Diagram</button>
    </div>
</form>
