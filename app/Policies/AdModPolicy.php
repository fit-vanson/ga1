<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AdModPolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.admod-index'));
    }
    public function show(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.admod-show'));
    }
    public function add(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.admod-add'))
            ? Response::allow()
            : Response::deny('Tài khoản không có quyền thêm mới.');;
    }
    public function edit(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.admod-edit'));
    }
    public function update(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.admod-update'));
    }
    public function delete(User $user)
    {
        return $user->checkPermissionAccess(config('permissions.access.admod-delete'));
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
