@if (!auth()->user()->company?->getFirstMediaUrl('logo'))
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black"/>
    </div>
@else
    <div class="flex aspect-square size-8 items-center justify-center rounded-md ">
        <div class="flex aspect-square size-10 items-center justify-center rounded-md">
            <img class="rounded-md" src="{{ url(auth()->user()->company->getFirstMediaUrl('logo')) }}"
                 alt="{{ __('Company Logo') }}">
        </div>
    </div>
@endif
<livewire:user-name/>
