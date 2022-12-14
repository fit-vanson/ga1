<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.phan_quyen-index'));
    }


    public function show(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.phan_quyen-show'));
    }
    public function add(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.phan_quyen-add'));
    }
    public function edit(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.phan_quyen-edit'));
    }

    public function update(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.phan_quyen-update'));
    }

    public function delete(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.phan_quyen-delete'));
    }


    public function restore(User $user)
    {
        //
    }

    public function forceDelete(User $user)
    {
        //
    }
}
