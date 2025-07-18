<?php

use App\Models\Tenant;
use App\Models\User;
use App\Rules\UniqueEmailInTenant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public int $systemId = 0;
    public string $tenant = '';
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
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                new UniqueEmailInTenant($this->tenant),

            ],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'tenant' => ['numeric'],
            'systemId' => ['numeric'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated + ['is_tenant' => true, 'system_id' => $this->systemId]))));
        $user->tenants()->attach($this->tenant);

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false));

    }

    private function getSystemId()
    {
        return User::find($this->getUserIdByTenant_id())->system_id;
    }

    private function getUserIdByTenant_id()
    {
        return User::whereHas('tenants', function ($query) {
            $query->where('id', $this->tenant);
        })->pluck('id')->first();
    }

    public function mount()
    {
        $this->tenant = tenant('id');
        $this->systemId =$this->getSystemId();
    }

}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create a tenant account')"
                   :description="__('Enter your details below to create your account')"/>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')"/>

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Tenant ID (hidden) -->
        <flux:input
            wire:model="tenant"
            type="hidden"
            value="{{ tenant('id') }}"
        />

        <!-- System ID (hidden) -->
        <flux:input
            wire:model="systemId"
            type="hidden"
            value="{{ $systemId }}"
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
