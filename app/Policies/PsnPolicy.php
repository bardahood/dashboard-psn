<?php

namespace App\Policies;

use App\Models\Psn;
use App\Models\User;

class PsnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('psn.view');
    }

    public function view(User $user, Psn $psn): bool
    {
        return $user->can('psn.view');
    }

    public function create(User $user): bool
    {
        return $user->can('profil.manage') || $user->can('psn.manage');
    }

    public function update(User $user, Psn $psn): bool
    {
        if ($user->can('profil.manage') || $user->can('psn.manage')) {
            return true;
        }

        // K/L Pelaksana: hanya boleh mengubah PSN miliknya sendiri.
        if ($user->hasRole('K/L Pelaksana')) {
            return $this->belongsToUserInstansi($user, $psn);
        }

        return false;
    }

    public function delete(User $user, Psn $psn): bool
    {
        return $user->can('profil.manage') || $user->can('psn.manage');
    }

    /**
     * Basis policy check K/L Pelaksana: peran instansi tunggal (pengusul/pengelola/
     * kontraktor/supervisi) ATAU relasi N:M psn_penanggung_jawab.
     */
    protected function belongsToUserInstansi(User $user, Psn $psn): bool
    {
        $instansiId = $user->instansiId();

        if (! $instansiId) {
            return false;
        }

        if (in_array($instansiId, [
            $psn->pengusul_instansi_id,
            $psn->pengelola_instansi_id,
            $psn->kontraktor_instansi_id,
            $psn->supervisi_instansi_id,
        ], true)) {
            return true;
        }

        return $psn->penanggungJawab()->where('instansi_id', $instansiId)->exists();
    }
}
