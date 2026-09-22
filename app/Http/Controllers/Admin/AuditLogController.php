<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AuditLogController extends Controller
{
    /**
     * Read-only: siapa (dengan peran apa) mengubah apa kapan (Bagian 5.2 prompt
     * pengembangan) -- lintas seluruh tabel yang diaudit, lihat daftar lengkap
     * di AppServiceProvider::modelDiaudit().
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()->with(['user', 'pic'])->orderByDesc('created_at');

        if ($request->filled('nama_tabel')) {
            $query->where('nama_tabel', $request->string('nama_tabel'));
        }

        if ($request->filled('aksi')) {
            $query->where('aksi', $request->string('aksi'));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('q')) {
            $cari = $request->string('q');
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$cari}%"));
        }

        $daftarAudit = $query->paginate(30)->withQueryString();
        $daftarTabel = AuditLog::query()->distinct()->orderBy('nama_tabel')->pluck('nama_tabel');
        $daftarRole = Role::orderBy('name')->pluck('name');

        return view('admin.audit-log.index', compact('daftarAudit', 'daftarTabel', 'daftarRole'));
    }

    /**
     * Detail satu baris audit: seluruh isi data yang berubah field demi
     * field, tanpa pemotongan teks seperti pada pratinjau di halaman index.
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load(['user', 'pic']);

        return view('admin.audit-log.show', compact('auditLog'));
    }
}
