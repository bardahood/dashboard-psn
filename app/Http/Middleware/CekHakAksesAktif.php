<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekHakAksesAktif
{
    /**
     * `hak_akses.is_active` dicek di sini (Bagian 6 prompt pengembangan) agar
     * Super Admin bisa menonaktifkan akses seorang PIC tanpa menghapus histori
     * (audit_log, kunjungan, dsb yang mereferensikan pic_id tetap utuh).
     *
     * PIC tanpa baris hak_akses sama sekali dianggap tidak dikelola lewat
     * fitur ini (mis. dibuat manual sebelum halaman ini ada) sehingga tetap
     * diizinkan -- hanya PIC yang seluruh baris hak_akses-nya non-aktif yang
     * diblokir.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $hakAkses = $user?->pic?->hakAkses;

        if ($hakAkses && $hakAkses->isNotEmpty() && $hakAkses->every(fn ($h) => ! $h->is_active)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Akun Anda telah dinonaktifkan. Hubungi administrator.');
        }

        return $next($request);
    }
}
