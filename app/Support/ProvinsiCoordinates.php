<?php

namespace App\Support;

/**
 * Koordinat titik tengah (centroid) tiap provinsi -- dipakai untuk peta
 * sebaran PSN (Tier 2, Bagian 5.1 prompt pengembangan). Skema `psn` tidak
 * menyimpan koordinat presisi (hanya provinsi_id/kabupaten_kota), jadi
 * marker peta ditempatkan per-provinsi, bukan per-lokasi proyek persis.
 */
class ProvinsiCoordinates
{
    protected const KOORDINAT = [
        'Aceh' => [4.695, 96.749],
        'Sumatera Utara' => [2.115, 99.545],
        'Sumatera Barat' => [-0.740, 100.800],
        'Riau' => [0.293, 101.706],
        'Kepulauan Riau' => [3.945, 108.142],
        'Jambi' => [-1.610, 103.613],
        'Sumatera Selatan' => [-3.319, 103.914],
        'Bangka Belitung' => [-2.741, 106.440],
        'Bengkulu' => [-3.792, 102.260],
        'Lampung' => [-4.558, 105.407],
        'DKI Jakarta' => [-6.208, 106.845],
        'Jawa Barat' => [-6.914, 107.609],
        'Jawa Tengah' => [-7.150, 110.140],
        'DI Yogyakarta' => [-7.797, 110.370],
        'Jawa Timur' => [-7.536, 112.238],
        'Banten' => [-6.405, 106.064],
        'Bali' => [-8.409, 115.189],
        'Nusa Tenggara Barat' => [-8.653, 117.361],
        'Nusa Tenggara Timur' => [-8.657, 121.079],
        'Kalimantan Barat' => [-0.278, 111.475],
        'Kalimantan Tengah' => [-1.681, 113.382],
        'Kalimantan Selatan' => [-3.092, 115.283],
        'Kalimantan Timur' => [0.538, 116.419],
        'Kalimantan Utara' => [3.073, 116.041],
        'Sulawesi Utara' => [0.624, 123.975],
        'Sulawesi Tengah' => [-1.430, 121.445],
        'Sulawesi Selatan' => [-3.668, 119.974],
        'Sulawesi Tenggara' => [-4.145, 122.174],
        'Gorontalo' => [0.699, 122.446],
        'Sulawesi Barat' => [-2.844, 119.232],
        'Maluku' => [-3.238, 130.145],
        'Maluku Utara' => [1.571, 127.809],
        'Papua' => [-4.269, 138.080],
        'Papua Barat' => [-1.336, 133.174],
        'Papua Barat Daya' => [-0.860, 131.256],
        'Papua Tengah' => [-3.673, 136.653],
        'Papua Pegunungan' => [-4.081, 138.940],
        'Papua Selatan' => [-7.062, 139.475],
    ];

    public static function untuk(string $namaProvinsi): ?array
    {
        return self::KOORDINAT[$namaProvinsi] ?? null;
    }
}
