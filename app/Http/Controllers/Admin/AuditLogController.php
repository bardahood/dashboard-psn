<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Read-only: siapa mengubah apa kapan (Bagian 5.2 prompt pengembangan).
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()->with('pic')->orderByDesc('created_at');

        if ($request->filled('nama_tabel')) {
            $query->where('nama_tabel', $request->string('nama_tabel'));
        }

        $daftarAudit = $query->paginate(30)->withQueryString();
        $daftarTabel = AuditLog::query()->distinct()->orderBy('nama_tabel')->pluck('nama_tabel');

        return view('admin.audit-log.index', compact('daftarAudit', 'daftarTabel'));
    }
}
