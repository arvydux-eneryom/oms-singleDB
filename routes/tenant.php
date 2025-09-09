<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Livewire\Volt\Volt;

use App\Livewire\Roles;
use App\Livewire\Tenancy;
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
        Volt::route('login', 'auth.tenant.login')
            ->name('login');

        Volt::route('register', 'auth.tenant.register')
            ->name('register');

        Volt::route('forgot-password', 'auth.forgot-password')
            ->name('password.request');

        Volt::route('reset-password/{token}', 'auth.reset-password')
            ->name('password.reset');

        Route::redirect('/', 'login')->name('home');

    });

    Route::get('auto-login', [AuthController::class, 'autoLogin'])
        ->name('auto-login')
        ->middleware('signed');

    Route::domain('{subdomain}.localhost')
        ->middleware(['signed'])
        ->get('/auto-login', [AuthController::class, 'autoLogin'])
        ->name('auto-login');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        Route::get('customers', Tenancy\Customers\Index::class)->name('customers.index');
        Route::get('customers/create', Tenancy\Customers\Create::class)->name('customers.create');
        Route::get('customers/{customer}/edit', Tenancy\Customers\Edit::class)->name('customers.edit');
        Route::get('customers/{customer}', Tenancy\Customers\View::class)->name('customers.show');
        Route::delete('customers/{customer}', Tenancy\Customers\Delete::class)->name('customers.delete');

        Route::post('logout', App\Livewire\Actions\Logout::class)
            ->name('logout');
    });


    Route::get('roles', Roles\Index::class)->name('roles.index');
    Route::get('roles/create', Roles\Create::class)->name('roles.create');
    Route::get('roles/{role}/edit', Roles\Edit::class)->name('roles.edit');


        Route::redirect('settings', 'settings/profile');

        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
        Volt::route('settings/password', 'settings.password')->name('settings.password');
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');


    Route::get('/a', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
});
