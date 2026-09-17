@php $u = $pengguna ?? null; $akses = $u?->pic?->hakAkses?->first(); @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm text-gray-600 mb-1">Nama</label>
        <input type="text" name="nama" value="{{ old('nama', $u?->name) }}" required
               class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $u?->email) }}" required
               class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">
            Password @if($u) <span class="text-xs text-gray-400">(kosongkan jika tidak ingin mengubah)</span> @endif
        </label>
        <input type="password" name="password" autocomplete="new-password"
               class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">Instansi</label>
        <select name="instansi_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">-- Tidak ada --</option>
            @foreach ($instansiOptions as $i)
                <option value="{{ $i->id }}" @selected(old('instansi_id', $u?->pic?->instansi_id) == $i->id)>{{ $i->nama_instansi }}</option>
            @endforeach
        </select>
        @error('instansi_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">Role</label>
        <select name="role" required class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            @foreach ($roleOptions as $role)
                <option value="{{ $role }}" @selected(old('role', $u?->roles?->first()?->name) === $role)>{{ $role }}</option>
            @endforeach
        </select>
        @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">Level Akses</label>
        <select name="level_akses" required class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            @foreach ($levelAksesOptions as $level)
                <option value="{{ $level }}" @selected(old('level_akses', $akses?->level_akses) === $level)>{{ $level }}</option>
            @endforeach
        </select>
        @error('level_akses') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $akses?->is_active ?? true)) class="rounded border-gray-300">
            Akun aktif (nonaktifkan untuk mencabut akses tanpa menghapus histori)
        </label>
        @error('is_active') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
