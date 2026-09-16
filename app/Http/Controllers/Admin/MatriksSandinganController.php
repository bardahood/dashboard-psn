<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriksSandinganController extends Controller
{
    /**
     * Matriks Sandingan Sumber -- tabel v/x 4 sumber per PSN, highlight gap.
     * Langsung memakai view v_psn_sandingan_sumber, tidak menulis ulang JOIN di PHP.
     */
    public function index(Request $request)
    {
        $query = DB::table('v_psn_sandingan_sumber');

        if ($request->filled('q')) {
            $query->where('nama_psn', 'like', '%'.$request->string('q').'%');
        }

        $matriks = $query->orderBy('nama_psn')->paginate(25)->withQueryString();

        return view('admin.matriks.index', compact('matriks'));
    }
}
