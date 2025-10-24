@php
    $logoCollection = tenant() ? 'tenant_logo' : 'system_logo';
    $logoUrl = auth()->user()->getFirstMediaUrl($logoCollection);
@endphp

@if (!$logoUrl)
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black"/>
    </div>
@else
    <div class="flex aspect-square size-8 items-center justify-center rounded-md ">
        <div class="flex aspect-square size-10 items-center justify-center rounded-md">
            <img class="rounded-md" src="{{ $logoUrl }}"
                 alt="{{ __('User Logo') }}">
        </div>
    </div>
@endif
<livewire:user-name/>
