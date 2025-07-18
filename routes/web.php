<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Subdomains;
use App\Http\Controllers\LogoController;
use App\Livewire\Users;
use App\Livewire\Roles;
use App\Livewire\Permissions;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('subdomains', Subdomains\Index::class)->name('subdomains.index');
    Route::get('subdomains/create', Subdomains\Create::class)->name('subdomains.create');
    Route::get('subdomains/{subdomain}/edit', Subdomains\Edit::class)->name('subdomains.edit');

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


    Route::post('companylogo/{company}/upload', [LogoController::class, 'uploadLogo'])->name('company.uploadLogo');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
