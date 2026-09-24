<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Psn;
use App\Support\ProvinsiCoordinates;
use Illuminate\Support\Facades\Cache;

class PetaController extends Controller
{
    /**
     * Peta Sebaran PSN (Tier 2, Bagian 5.1 prompt pengembangan). Marker
     * ditempatkan per-provinsi (bukan per-lokasi presisi, karena skema tidak
     * menyimpan koordinat), diklik untuk menuju daftar PSN publik pada
     * provinsi tersebut.
     */
    public function index()
    {
        $markers = Cache::remember('publik.peta.markers', now()->addMinutes(15), function () {
            return Psn::query()
                ->with('provinsi', 'klaster')
                ->whereHas('provinsi', fn ($q) => $q->where('nama_provinsi', '!=', 'Nasional'))
                ->get()
                ->groupBy(fn ($psn) => $psn->provinsi->nama_provinsi)
                ->map(function ($group, $namaProvinsi) {
                    $koordinat = ProvinsiCoordinates::untuk($namaProvinsi);

                    if (! $koordinat) {
                        return null;
                    }

                    return [
                        'provinsi' => $namaProvinsi,
                        'lat' => $koordinat[0],
                        'lng' => $koordinat[1],
                        'jumlah' => $group->count(),
                        'daftar' => $group->map(fn ($psn) => [
                            'id' => $psn->id,
                            'nama' => $psn->nama_psn,
                            'klaster' => $psn->klaster?->nama_klaster,
                        ])->values(),
                    ];
                })
                ->filter()
                ->values();
        });

        return view('public.peta', compact('markers'));
    }
}
