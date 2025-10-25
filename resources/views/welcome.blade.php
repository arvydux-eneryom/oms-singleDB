<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    <title>{{ config('app.name') }}</title>
</head>

<body class="dark:bg-linear-to-b min-h-screen bg-white antialiased dark:from-neutral-950 dark:to-neutral-900">
    <div class="flex min-h-screen flex-col items-center justify-center p-6">
        <div class="flex flex-col items-center gap-8 text-center">
            <x-app-logo-icon class="size-16 fill-current text-black dark:text-white" />

            <h1 class="text-3xl font-semibold text-zinc-900 dark:text-white">
                Operation Management System
            </h1>

            @if (Route::has('login'))
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="rounded-md bg-zinc-900 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="rounded-md border border-zinc-900 px-6 py-2.5 text-sm font-semibold text-zinc-900 transition-colors hover:bg-zinc-900 hover:text-white dark:border-white dark:text-white dark:hover:bg-white dark:hover:text-zinc-900">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="rounded-md bg-zinc-900 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </div>

    @fluxScripts
</body>

</html>
