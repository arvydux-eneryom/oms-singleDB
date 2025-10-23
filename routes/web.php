<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\TwilioWebhookController;
use App\Livewire\Integrations\SmsManager;
use App\Services\TwilioSmsService;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Subdomains;
use App\Http\Controllers\LogoController;
use App\Livewire\Users;
use App\Livewire\Roles;
use App\Livewire\Permissions;
use App\Livewire\Integrations;
use App\Livewire\Tenancy\Customers;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Twilio webhook routes (must be outside auth middleware and use POST)
Route::post(config('services.twilio.incoming_sms_url_path'), [TwilioWebhookController::class, 'handleIncomingSms'])
    ->middleware(\App\Http\Middleware\VerifyTwilioWebhook::class)
    ->name('integrations.sms.handleIncomingSms');

Route::post(config('services.twilio.outgoing_sms_status_callback_url_path'), [TwilioWebhookController::class, 'handleOutgoingSmsStatus'])
    ->middleware(\App\Http\Middleware\VerifyTwilioWebhook::class)
    ->name('integrations.sms.handleOutgoingSmsStatus');

Route::middleware(['auth'])->group(function () {
    Route::get('integrations', Integrations\Index::class)->name('integrations.index');
    Route::get('integrations/telegram', Integrations\telegram\Index::class)->name('integrations.telegram.index');
    Route::get('integrations/telegram/connection-message', [Integrations\telegram\Index::class, 'showConnectionMessage'])->name('integrations.telegram.connection-message');
    Route::get('integrations/telegram/create-channel', [Integrations\telegram\Index::class, 'createChannel'])->name('integrations.telegram.createChannel');

    Route::get('integrations/sms', SmsManager::class)->name('integrations.sms-manager.index');

    Route::post('integrations/sms/send-question', [\App\Services\SmsManagerService::class, 'sendQuestion'])
        ->middleware('throttle:sms-sending')
        ->name('integrations.sms.send-question');

    Route::get('subdomains', Subdomains\Index::class)->name('subdomains.index');
    Route::get('subdomains/redirect', Subdomains\Redirect::class)->name('subdomains.redirect'); //temporary
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

    Route::get('customers', Customers\Index::class)->name('customers.index');
    Route::get('customers/create', Customers\Create::class)->name('customers.create');
    Route::get('customers/{customer}/edit', Customers\Edit::class)->name('customers.edit');
    Route::get('customers/{customer}', Customers\View::class)->name('customers.view');

    Route::post('companylogo/{company}/upload', [LogoController::class, 'uploadLogo'])->name('company.uploadLogo');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
