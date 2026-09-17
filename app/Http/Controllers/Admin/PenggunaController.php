<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HakAkses;
use App\Models\RefInstansi;
use App\Models\RefPic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * Manajemen Pengguna & Hak Akses (Bagian 5.2 & 6 prompt pengembangan):
 * CRUD ref_pic + hak_akses, assign role spatie/laravel-permission.
 *
 * Sengaja TANPA method destroy() -- "hak_akses.is_active dicek di middleware,
 * nonaktifkan akses tanpa hapus histori" (Bagian 6). Menghapus User/RefPic
 * akan merusak banyak FK histori (audit_log.pic_id, kunjungan.verifikator_id,
 * dst), jadi jalur satu-satunya untuk mencabut akses adalah menonaktifkan
 * hak_akses lewat form edit.
 */
class PenggunaController extends Controller
{
    private const LEVEL_AKSES = ['Admin', 'Editor', 'Viewer'];

    public function index()
    {
        $pengguna = User::query()
            ->with(['pic.instansi', 'pic.hakAkses', 'roles'])
            ->orderBy('name')
            ->paginate(25);

        return view('admin.pengguna.index', compact('pengguna'));
    }

    public function create()
    {
        return view('admin.pengguna.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        $pengguna = DB::transaction(function () use ($data) {
            $pic = RefPic::create([
                'nama_pic' => $data['nama'],
                'email' => $data['email'],
                'instansi_id' => $data['instansi_id'],
            ]);

            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'pic_id' => $pic->id,
            ]);
            $user->assignRole($data['role']);

            HakAkses::create([
                'pic_id' => $pic->id,
                'instansi_id' => $data['instansi_id'],
                'level_akses' => $data['level_akses'],
                'is_active' => $data['is_active'],
            ]);

            return $user;
        });

        return redirect()->route('admin.pengguna.edit', $pengguna)->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $pengguna)
    {
        $pengguna->load('pic.hakAkses', 'roles');

        return view('admin.pengguna.edit', $this->formOptions() + ['pengguna' => $pengguna]);
    }

    public function update(Request $request, User $pengguna)
    {
        $data = $this->validated($request, $pengguna);

        if ($pengguna->id === auth()->id() && ! $data['is_active']) {
            return back()->withErrors(['is_active' => 'Anda tidak bisa menonaktifkan akun Anda sendiri.'])->withInput();
        }

        DB::transaction(function () use ($data, $pengguna) {
            $pengguna->update([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => $data['password'] ? Hash::make($data['password']) : $pengguna->password,
            ]);
            $pengguna->syncRoles([$data['role']]);

            $pic = $pengguna->pic;
            $pic?->update([
                'nama_pic' => $data['nama'],
                'email' => $data['email'],
                'instansi_id' => $data['instansi_id'],
            ]);

            if ($pic) {
                HakAkses::updateOrCreate(
                    ['pic_id' => $pic->id],
                    [
                        'instansi_id' => $data['instansi_id'],
                        'level_akses' => $data['level_akses'],
                        'is_active' => $data['is_active'],
                    ]
                );
            }
        });

        return redirect()->route('admin.pengguna.edit', $pengguna)->with('status', 'Pengguna berhasil diperbarui.');
    }

    protected function validated(Request $request, ?User $pengguna): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($pengguna?->id)],
            'password' => [$pengguna ? 'nullable' : 'required', 'string', 'min:8'],
            'instansi_id' => ['nullable', 'exists:ref_instansi,id'],
            'level_akses' => ['required', Rule::in(self::LEVEL_AKSES)],
            'role' => ['required', Rule::exists('roles', 'name')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    protected function formOptions(): array
    {
        return [
            'instansiOptions' => RefInstansi::orderBy('nama_instansi')->get(),
            'roleOptions' => Role::orderBy('name')->pluck('name'),
            'levelAksesOptions' => self::LEVEL_AKSES,
        ];
    }
}
