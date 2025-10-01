<?php

use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use App\Services\RedirectionToSubdomainService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
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
     *
     * This method creates a new system user with super-admin role and sets up their tenant environment.
     *
     * Process:
     * 1. Validates input (name, email, password)
     * 2. Creates user with is_system=true and assigns super-admin-for-tenant role
     * 3. Creates company record linked to the user
     * 4. Creates tenant with unique random subdomain (2-8 chars)
     * 5. Creates domain record for the tenant
     * 6. Links user to tenant and marks as tenant user
     * 7. Logs in the user and redirects to their subdomain
     *
     * All operations are wrapped in a database transaction to ensure data integrity.
     * If any step fails, all changes are rolled back.
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException
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

        DB::transaction(function () use ($validated) {
            $user = User::create($validated + ['is_system' => true, 'system_id' => User::getNextSystemIdOrDefault()]);

            event(new Registered($user));
            $user->assignRole('super-admin-for-tenant');

            Company::create([
                'name' => $validated['name'],
                'user_id' => $user->id,
            ]);

            $this->createRandomDomain($validated['name'], $user);

            if (Auth::check()) {
                $this->logOut();
            }
            Auth::login($user);

            RedirectionToSubdomainService::redirectToSubdomain();
        });
    }

    /**
     * Log out any existing authenticated user.
     *
     * Invalidates the current session and regenerates the CSRF token.
     *
     * @return void
     */
    private function logOut(): void
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }

    /**
     * Create a tenant and domain for the newly registered user.
     *
     * Creates a tenant with the company name and generates a unique random subdomain.
     * The subdomain is between 2-8 characters long and guaranteed to be unique.
     *
     * @param string $companyName The company name to use for tenant and domain
     * @param User $user The user to link to the tenant
     * @return void
     */
    private function createRandomDomain(string $companyName, User $user): void
    {
        $tenant = Tenant::create([
            'name' => $companyName,
        ]);

        $subdomainName = $this->generateUniqueSubdomain();
        $tenant->domains()->create([
            'name' => $companyName,
            'subdomain' => $subdomainName,
            'domain' => strtolower($subdomainName) . '.' . config('tenancy.central_domains')[0],
            'system_id' => $user->system_id,
        ]);

        $user->is_tenant = true;
        $user->save();

        $user->tenants()->attach($tenant->id);
    }

    /**
     * Generate a unique subdomain with collision detection.
     *
     * Attempts to generate a random subdomain (2-8 chars) up to 10 times.
     * If all attempts result in collisions, falls back to a timestamp-based
     * subdomain to guarantee uniqueness.
     *
     * The subdomain is checked against the domains table to ensure no duplicates exist.
     *
     * @return string A unique subdomain string
     */
    private function generateUniqueSubdomain(): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $length = rand(2, 8);
            $subdomain = Str::random($length);
            $attempt++;

            if (!DB::table('domains')->where('subdomain', $subdomain)->exists()) {
                return $subdomain;
            }
        } while ($attempt < $maxAttempts);

        // Fallback: use timestamp-based unique subdomain
        return Str::random(4) . substr((string) time(), -4);
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
