<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Katalog RO/Output resmi dari sistem Krisna (lampiran laporan_PSN.xlsx,
     * Risalah Rapat 21 Sept 2026: "RO pilihannya dropdown, pilihan ditarik
     * dari krisna"). Tabel referensi baca-saja -- dipakai sebagai sumber
     * dropdown pengisian Nama RO pada RoProyekManager, TIDAK menggantikan
     * ro_proyek (data project profile milik aplikasi ini tetap independen,
     * hanya diberi opsi "isi cepat" dari katalog resmi ini bila cocok).
     *
     * psn_id ditautkan lewat pencocokan nama project_psn -- Krisna
     * menamainya dengan format "KODE-Nama" (mis. "G10-Jalan Tol Semarang -
     * Demak"), prefiksnya dibuang sebelum dicocokkan ke psn.nama_psn.
     *
     * prop_kode_rkp/nama_prop_rkp/alokasi_prop_juta_rp adalah pengayaan dari
     * lampiran kedua (matrix_pembangunan_rkp2026.xlsx, matriks nasional PN/
     * PP/KP/ProP) -- ditautkan lewat "Kode Source" pada baris Output di
     * matriks tsb yang persis merekonstruksi kolom kegiatan/kro/ro/
     * project_rkp di laporan_PSN.xlsx. Kolom PN/PP/KP granular TIDAK
     * diimpor terpisah karena matriksnya mencakup seluruh RKP nasional
     * (bukan spesifik PSN) dan tidak dibutuhkan langsung oleh kebutuhan
     * Risalah Rapat -- lihat README untuk penjelasan lengkap batasan ini.
     */
    public function up(): void
    {
        Schema::create('ref_ro_krisna', function (Blueprint $table) {
            $table->id();
            $table->string('sektor_psn', 100)->nullable();
            $table->string('project_psn', 255)->nullable();
            $table->string('project_rkp', 255)->nullable();
            $table->string('kementerian', 150)->nullable();
            $table->text('program')->nullable();
            $table->text('kegiatan')->nullable();
            $table->text('kro')->nullable();
            $table->text('ro')->nullable();
            // Beberapa RO nasional (bukan spesifik lokasi tunggal) mencantumkan
            // puluhan nama provinsi sekaligus dipisah koma pada kolom ini.
            $table->text('lokasi_ro')->nullable();
            $table->decimal('volume', 18, 2)->nullable();
            $table->string('satuan', 50)->nullable();
            // Satuan nilai mengikuti apa adanya dari sumber (Krisna) -- TIDAK
            // dipastikan Rupiah penuh atau ribuan/jutaan Rupiah, sumbernya tidak
            // mencantumkan keterangan satuan eksplisit. Ditampilkan mentah tanpa
            // konversi/asumsi supaya tidak menyesatkan pengguna dengan label
            // satuan yang salah.
            $table->decimal('alokasi', 20, 2)->nullable()->comment('Alokasi anggaran RKP, satuan mengikuti sumber (belum terverifikasi)');
            $table->text('pn')->nullable();
            $table->text('pp')->nullable();
            $table->text('kp')->nullable();
            $table->text('prop')->nullable()->comment('Proyek Prioritas (kolom sumber bernama "ppn" di laporan_PSN.xlsx)');
            $table->foreignId('psn_id')->nullable()->constrained('psn')->nullOnDelete();
            $table->string('prop_kode_rkp', 30)->nullable()->comment('Kode ProP hasil pengayaan matrix_pembangunan_rkp2026.xlsx');
            $table->string('nama_prop_rkp', 255)->nullable();
            $table->decimal('alokasi_prop', 20, 2)->nullable()->comment('Alokasi ProP dari matrix_pembangunan_rkp2026.xlsx, satuan mengikuti sumber');
            $table->timestamps();
            $table->index('psn_id', 'idx_ro_krisna_psn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_ro_krisna');
    }
};
