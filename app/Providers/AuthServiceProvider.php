<?php

namespace App\Providers;

use App\Policies\AdModPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->defineUser();
        $this->defineVaitro();
        $this->definePhan_quyen();
        $this->defineAdMod();

    }
    public function defineVaitro(){
        Gate::define('vai_tro-index', [RolePolicy::class, 'index']);
        Gate::define('vai_tro-show', [RolePolicy::class, 'show']);
        Gate::define('vai_tro-add', [RolePolicy::class, 'add']);
        Gate::define('vai_tro-edit', [RolePolicy::class, 'edit']);
        Gate::define('vai_tro-update', [RolePolicy::class, 'update']);
        Gate::define('vai_tro-delete', [RolePolicy::class, 'delete']);
    }
    public function defineUser(){
        Gate::define('user-index', [UserPolicy::class, 'index']);
        Gate::define('user-show', [UserPolicy::class, 'show']);
        Gate::define('user-add', [UserPolicy::class, 'add']);
        Gate::define('user-edit', [UserPolicy::class, 'edit']);
        Gate::define('user-update', [UserPolicy::class, 'update']);
        Gate::define('user-delete', [UserPolicy::class, 'delete']);
    }
    public function definePhan_quyen(){
        Gate::define('phan_quyen-index', [PermissionPolicy::class, 'index']);
        Gate::define('phan_quyen-show', [PermissionPolicy::class, 'show']);
        Gate::define('phan_quyen-add', [PermissionPolicy::class, 'add']);
        Gate::define('phan_quyen-edit', [PermissionPolicy::class, 'edit']);
        Gate::define('phan_quyen-update', [PermissionPolicy::class, 'update']);
        Gate::define('phan_quyen-delete', [PermissionPolicy::class, 'delete']);

    }
    public function defineAdMod(){
        Gate::define('admod-index', [AdModPolicy::class, 'index']);
        Gate::define('admod-show', [AdModPolicy::class, 'show']);
        Gate::define('admod-add', [AdModPolicy::class, 'add']);
        Gate::define('admod-edit', [AdModPolicy::class, 'edit']);
        Gate::define('admod-update', [AdModPolicy::class, 'update']);
        Gate::define('admod-delete', [AdModPolicy::class, 'delete']);

    }
}
