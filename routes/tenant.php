<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Livewire\Volt\Volt;

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
        ->middleware('signed');;

    Route::domain('{subdomain}.localhost')
        ->middleware(['signed'])
        ->get('/auto-login', [AuthController::class, 'autoLogin'])
        ->name('auto-login');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');


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
