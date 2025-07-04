<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $subdomain = '';
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validatedDomain = $this->validate([
            'subdomain' => ['required', 'alpha', 'unique:domains,domain'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $tenant = Tenant::create([
            'name' => $this->name . ' system',
        ]);

        event(new Registered(($user = User::create($validated + ['tenant_id' => $tenant->id]))));

        $tenant->domains()->create([
            'domain' => $this->subdomain . '.' . config('tenancy.central_domains')[0],
        ]);
        $user->tenants()->attach($tenant->id);

        Auth::login($user);

        $this->redirectIntended('http://' . $this->subdomain . '.' . config('tenancy.central_domains')[0] . ':8000' . route('dashboard', absolute: false));

    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create a tenant')"
                   :description="__('Enter your details below to create your account')"/>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')"/>

    <form wire:submit="register" class="flex flex-col gap-6">

        <!-- Subdomain -->
        <flux:input
            wire:model="subdomain"
            :label="__('Subdomain')"
            type="text"
            required
            autofocus
            autocomplete="subdomain"
            :placeholder="__('Subdomain')"
        />

        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Name')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Full name')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
            viewable
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
