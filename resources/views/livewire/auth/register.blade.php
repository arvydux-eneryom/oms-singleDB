<?php

use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
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
                Rule::unique(User::class)->where(fn($query) => $query->where('is_system', true)),
            ],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated + ['is_system' => true, 'system_id' => User::getNextSystemIdOrDefault()]))));
        $user->assignRole('super-admin');

        Company::create([
            'name' => $validated['name'],
            'user_id' => $user->id,
        ]);

        $this->createRandomDomain($validated['name'], $user);

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }

    private function createRandomDomain($companyName, User $user): void
    {
        $tenant = Tenant::create([
            'name' => $companyName,
        ]);


        $subdomainName = $this->randomString2to8();
        $tenant->domains()->create([
            'name' => $companyName,
            'subdomain' => $subdomainName,
            'domain' => $subdomainName . '.' . config('tenancy.central_domains')[0],
            'system_id' => $user->system_id,
        ]);

        $user->is_tenant = true;
        $user->save();

        $user->tenants()->attach($tenant->id);
    }

    function randomString2to8(): string
    {
        $length = rand(2, 8);
        return Str::random($length);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create a system account.')"
                   :description="__('Enter your details below to create your account')"/>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')"/>

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Company name')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Company name')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address (this will be your super admin account)')"
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
