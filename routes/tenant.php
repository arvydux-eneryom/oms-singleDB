<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Livewire\Volt\Volt;
use App\Livewire\Users;
use App\Livewire\Roles;
use App\Livewire\Permissions;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::middleware('guest')->group(function () {
        Volt::route('login', 'auth.login')
            ->name('login');

        Volt::route('register', 'auth.tenant.register')
            ->name('register');

        Volt::route('forgot-password', 'auth.forgot-password')
            ->name('password.request');

        Volt::route('reset-password/{token}', 'auth.reset-password')
            ->name('password.reset');

        Route::redirect('/', 'login')->name('home');

    });

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        //Route::resource('roles', RoleController::class);
       // Route::resource('users', UserController::class);
        Route::get('users', Users\Index::class)->name('users.index');
        Route::get('users/create', Users\Create::class)->name('users.create');
        Route::get('users/{user}/edit', Users\Edit::class)->name('users.edit');

        Route::get('roles', Roles\Index::class)->name('roles.index');
        Route::get('roles/create', Roles\Create::class)->name('roles.create');
        Route::get('roles/{role}/edit', Roles\Edit::class)->name('roles.edit');

        Route::get('permissions', Permissions\Index::class)->name('permissions.index');
        Route::get('permissions/create', Permissions\Create::class)->name('permissions.create');
        Route::get('permissions/{permission}/edit', Permissions\Edit::class)->name('permissions.edit');

        Route::post('logout', App\Livewire\Actions\Logout::class)
            ->name('logout');
    });




        Route::redirect('settings', 'settings/profile');

        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
        Volt::route('settings/password', 'settings.password')->name('settings.password');
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');


    Route::get('/a', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
});
