<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.user-index'));
    }
    public function show(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.user-show'));
    }
    public function add(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.user-add'));
    }
    public function edit(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.user-edit'));
    }
    public function update(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.user-update'));
    }
    public function delete(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.user-delete'));
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
