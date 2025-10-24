<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark"/>

    <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
        <x-app-logo/>
    </a>

    @if(! tenant())
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('System platform')" class="grid">
                <flux:navlist.item icon="home" :href="config('app.url') . '/dashboard'" :current="request()->routeIs('dashboard')"
                                   wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                <flux:navlist.item icon="users" :href="config('app.url') . '/subdomains'"
                                   :current="request()->routeIs('subdomains.index')"
                                   wire:navigate>{{ __('Subdomains') }}</flux:navlist.item>
                <flux:navlist.item icon="users" :href="config('app.url') . '/integrations'"
                                   :current="request()->routeIs('integrations.index')"
                                   wire:navigate>{{ __('Integrations') }}</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    @else
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Tenancy platform')" class="grid">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('tenant.dashboard')"
                                   wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    @endif

    <flux:navlist variant="outline">
            @if(! tenant())
            <flux:navlist.group :heading="__('Super admin area')" class="grid">
                @if(auth()->user() && auth()->user()->hasRole('super-admin-for-system'))
                    <flux:navlist.item icon="users" :href="config('app.url') . '/users'"
                                       :current="request()->routeIs('users.index')"
                                       wire:navigate>{{ __('Users') }}</flux:navlist.item>

                    <flux:navlist.item icon="user-circle" :href="config('app.url') . '/roles'"
                                       :current="request()->routeIs('roles.index')"
                                       wire:navigate>{{ __('Roles') }}</flux:navlist.item>

                    <flux:navlist.item icon="adjustments-vertical" :href="config('app.url') . '/permissions'"
                                       :current="request()->routeIs('permissions.index')"
                                       wire:navigate>{{ __('Permissions') }}</flux:navlist.item>
                @endif
            </flux:navlist.group>
        @else
            <flux:navlist.item icon="user-circle" :href="route('roles.index')"
                               :current="request()->routeIs('roles.index')"
                               wire:navigate>{{ __('Roles') }}</flux:navlist.item>

            <flux:navlist.item icon="user-circle" :href="route('customers.index')"
                               :current="request()->routeIs('customers.index')"
                               wire:navigate>{{ __('Customers') }}</flux:navlist.item>
        @endif
    </flux:navlist>

    <flux:spacer/>

    @if(tenant() && auth()->check() && auth()->user()->isSystem())
        <flux:navlist variant="outline">
            <flux:navlist.item icon="book-open-text" href="{{ config('app.url') . '/dashboard' }}" target="_blank">
                {{ __('My account') }}
            </flux:navlist.item>
        </flux:navlist>
    @endif

    <!-- Desktop User Menu -->
    <flux:dropdown class="hidden lg:block" position="bottom" align="start">
        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevrons-up-down"
        />

        <flux:menu class="w-[220px]">
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator/>

            <flux:menu.radio.group>
                <flux:menu.item :href="route('settings.profile')" icon="cog"
                                wire:navigate>{{ __('Settings') }}</flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator/>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>
<!-- Mobile User Menu -->
{{--        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>--}}

        {{ $slot }}

        @fluxScripts

        <!-- Inactivity Tracker -->
        <x-inactivity-tracker />
    </body>
</html>
