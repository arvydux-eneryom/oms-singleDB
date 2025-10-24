<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header Section --}}
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Integrations</h1>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
            Connect your favorite tools and services to streamline your workflow
        </p>
    </div>

    {{-- Integrations Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {{-- Telegram Integration --}}
        <a href="{{ route('integrations.telegram.index') }}"
           wire:navigate
           class="group relative flex flex-col items-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-950/30 dark:to-blue-950/30 p-6 shadow-sm hover:shadow-lg hover:border-sky-300 dark:hover:border-sky-700 transition-all duration-200 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-sky-400/20 to-blue-500/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-400 via-blue-500 to-blue-600 mb-4 shadow-lg shadow-blue-500/30 group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">
                <svg class="h-11 w-11 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="m20.665 3.717-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l.002.001-.314 4.692c.46 0 .663-.211.921-.46l2.211-2.15 4.599 3.397c.848.467 1.457.227 1.668-.785l3.019-14.228c.309-1.239-.473-1.8-1.282-1.434z"/>
                </svg>
            </div>
            <h3 class="relative text-base font-semibold text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                Telegram
            </h3>
            <p class="relative mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
                Connect Telegram for messaging
            </p>
            <div class="relative mt-4">
                <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-400 border border-green-200 dark:border-green-800">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                    Active
                </span>
            </div>
        </a>

        {{-- SMS Integration --}}
        <a href="{{ route('integrations.sms-manager.index') }}"
           wire:navigate
           class="group relative flex flex-col items-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-950/30 dark:to-purple-950/30 p-6 shadow-sm hover:shadow-lg hover:border-violet-300 dark:hover:border-violet-700 transition-all duration-200 overflow-hidden">
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-violet-400/20 to-purple-500/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 via-purple-500 to-fuchsia-600 mb-4 shadow-lg shadow-purple-500/30 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-300">
                <svg class="h-11 w-11 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <h3 class="relative text-base font-semibold text-zinc-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                SMS Manager
            </h3>
            <p class="relative mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
                Send and manage SMS messages
            </p>
            <div class="relative mt-4">
                <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-400 border border-green-200 dark:border-green-800">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                    Active
                </span>
            </div>
        </a>

        {{-- Coming Soon - Email Integration --}}
        <div class="relative flex flex-col items-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm opacity-60">
            <div class="flex h-24 w-24 items-center justify-center rounded-lg bg-gradient-to-br from-zinc-400 to-zinc-500 mb-4">
                <svg class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                Email
            </h3>
            <p class="mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
                Email integration and automation
            </p>
            <div class="mt-4">
                <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                    Coming Soon
                </span>
            </div>
        </div>

        {{-- Coming Soon - Slack Integration --}}
        <div class="relative flex flex-col items-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm opacity-60">
            <div class="flex h-24 w-24 items-center justify-center rounded-lg bg-gradient-to-br from-zinc-400 to-zinc-500 mb-4">
                <svg class="h-12 w-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5.042 15.165a2.528 2.528 0 01-2.52 2.523A2.528 2.528 0 010 15.165a2.527 2.527 0 012.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 012.521-2.52 2.527 2.527 0 012.521 2.52v6.313A2.528 2.528 0 018.834 24a2.528 2.528 0 01-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 01-2.521-2.52A2.528 2.528 0 018.834 0a2.528 2.528 0 012.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 012.521 2.521 2.528 2.528 0 01-2.521 2.521H2.522A2.528 2.528 0 010 8.834a2.528 2.528 0 012.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 012.522-2.521A2.528 2.528 0 0124 8.834a2.528 2.528 0 01-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 01-2.523 2.521 2.527 2.527 0 01-2.52-2.521V2.522A2.527 2.527 0 0115.165 0a2.528 2.528 0 012.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 012.523 2.522A2.528 2.528 0 0115.165 24a2.527 2.527 0 01-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 01-2.52-2.523 2.526 2.526 0 012.52-2.52h6.313A2.527 2.527 0 0124 15.165a2.528 2.528 0 01-2.522 2.523h-6.313z"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                Slack
            </h3>
            <p class="mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
                Team communication and alerts
            </p>
            <div class="mt-4">
                <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                    Coming Soon
                </span>
            </div>
        </div>

        {{-- Coming Soon - WhatsApp Integration --}}
        <div class="relative flex flex-col items-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm opacity-60">
            <div class="flex h-24 w-24 items-center justify-center rounded-lg bg-gradient-to-br from-zinc-400 to-zinc-500 mb-4">
                <svg class="h-12 w-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                WhatsApp
            </h3>
            <p class="mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
                WhatsApp business messaging
            </p>
            <div class="mt-4">
                <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                    Coming Soon
                </span>
            </div>
        </div>

        {{-- Coming Soon - Webhook Integration --}}
        <div class="relative flex flex-col items-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm opacity-60">
            <div class="flex h-24 w-24 items-center justify-center rounded-lg bg-gradient-to-br from-zinc-400 to-zinc-500 mb-4">
                <svg class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                Webhooks
            </h3>
            <p class="mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
                Custom webhook integrations
            </p>
            <div class="mt-4">
                <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                    Coming Soon
                </span>
            </div>
        </div>

        {{-- Coming Soon - Calendar Integration --}}
        <div class="relative flex flex-col items-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm opacity-60">
            <div class="flex h-24 w-24 items-center justify-center rounded-lg bg-gradient-to-br from-zinc-400 to-zinc-500 mb-4">
                <svg class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                Calendar
            </h3>
            <p class="mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
                Calendar sync and scheduling
            </p>
            <div class="mt-4">
                <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                    Coming Soon
                </span>
            </div>
        </div>

        {{-- Coming Soon - API Integration --}}
        <div class="relative flex flex-col items-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm opacity-60">
            <div class="flex h-24 w-24 items-center justify-center rounded-lg bg-gradient-to-br from-zinc-400 to-zinc-500 mb-4">
                <svg class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                API Access
            </h3>
            <p class="mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
                RESTful API for custom integration
            </p>
            <div class="mt-4">
                <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                    Coming Soon
                </span>
            </div>
        </div>
    </div>

    {{-- Help Text --}}
    <div class="mt-12 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 p-6">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Need a custom integration?</h3>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    We're constantly adding new integrations. If you need a specific integration that's not listed here, please contact support and let us know.
                </p>
            </div>
        </div>
    </div>
</div>
