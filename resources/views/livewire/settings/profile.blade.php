<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $logo;
    public ?string $tenantId = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->tenantId = tenant()?->id;
    }

    /**
     * Get the logo collection name based on current context (system vs tenant)
     */
    protected function getLogoCollection(): string
    {
        return $this->tenantId ? 'tenant_logo' : 'system_logo';
    }

    /**
     * Get current logo URL for display
     */
    public function getCurrentLogoUrl(): ?string
    {
        $collection = $this->getLogoCollection();
        return Auth::user()->getFirstMediaUrl($collection) ?: null;
    }

    /**
     * Save logo when user clicks "Save Logo" button
     */
    public function saveLogo()
    {
        $this->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'logo.required' => 'Please select a logo to upload.',
            'logo.image' => 'The file must be an image.',
            'logo.mimes' => 'The logo must be a file of type: jpeg, png, jpg, gif, svg.',
            'logo.max' => 'The logo must not exceed 2MB in size.',
        ]);

        try {
            $user = Auth::user();
            $collection = $this->getLogoCollection();

            // Debug logging
            \Log::info('Logo upload debug', [
                'tenant_from_helper' => tenant() ? tenant()->id : 'null',
                'tenant_from_component' => $this->tenantId ?? 'null',
                'collection' => $collection,
                'user_id' => $user->id,
            ]);

            // Clear previous logo for this scope
            $user->clearMediaCollection($collection);

            // Upload new logo to scope-specific collection
            $user->addMedia($this->logo)
                ->usingFileName($this->logo->getClientOriginalName())
                ->toMediaCollection($collection);

            session()->flash('logo-uploaded', 'Logo uploaded successfully! (Collection: ' . $collection . ')');
            $this->logo = null; // Clear the input

            // Refresh the component to show new logo
            $this->dispatch('logo-updated');
        } catch (\Exception $e) {
            \Log::error('Logo upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            session()->flash('logo-error', 'The logo failed to upload. Please try again.');
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <!-- Logo Upload Section -->
        <div class="mb-8 border-b border-zinc-200 dark:border-zinc-700 pb-8">
            <flux:subheading class="mb-4">{{ __('Logo') }}</flux:subheading>

            <div class="flex items-start gap-6">
                <!-- Current Logo Display -->
                <div class="flex-shrink-0">
                    @if ($this->getCurrentLogoUrl())
                        <img class="rounded-lg w-24 h-24 object-contain border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800"
                             src="{{ $this->getCurrentLogoUrl() }}"
                             alt="{{ __('User Logo') }}">
                    @else
                        <div class="flex aspect-square w-24 h-24 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                            <x-app-logo-icon class="size-12 fill-current text-zinc-400 dark:text-zinc-500"/>
                        </div>
                    @endif
                </div>

                <!-- Upload Controls -->
                <div class="flex-1 space-y-4">
                    <div>
                        <label for="logo-input" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            {{ __('Upload new logo') }}
                        </label>

                        <div class="relative">
                            <input
                                type="file"
                                wire:model="logo"
                                id="logo-input"
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml"
                                class="block w-full text-sm text-zinc-900 dark:text-zinc-100
                                       border border-zinc-300 dark:border-zinc-600
                                       rounded-lg cursor-pointer
                                       bg-white dark:bg-zinc-800
                                       file:mr-4 file:py-2 file:px-4
                                       file:rounded-l-lg file:border-0
                                       file:text-sm file:font-medium
                                       file:bg-zinc-100 dark:file:bg-zinc-700
                                       file:text-zinc-700 dark:file:text-zinc-300
                                       hover:file:bg-zinc-200 dark:hover:file:bg-zinc-600
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                            />
                        </div>

                        <flux:text class="mt-2">
                            {{ __('Accepted formats: JPG, PNG, GIF, SVG. Max size: 2MB.') }}
                        </flux:text>

                        @error('logo')
                            <flux:error class="mt-2">{{ $message }}</flux:error>
                        @enderror
                    </div>

                    <!-- Preview -->
                    @if($logo)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <flux:text class="font-medium mb-2">{{ __('Preview:') }}</flux:text>
                            @if(method_exists($logo, 'isPreviewable') && $logo->isPreviewable())
                                <img src="{{ $logo->temporaryUrl() }}"
                                     class="rounded-lg w-24 h-24 object-contain border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900">
                            @else
                                <flux:text class="text-zinc-500">{{ __('File selected:') }} {{ $logo->getClientOriginalName() }}</flux:text>
                            @endif
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-start">
                            <flux:button
                                wire:click="saveLogo"
                                variant="primary"
                                type="button"
                                :disabled="!$logo"
                                wire:loading.attr="disabled"
                                wire:target="saveLogo">
                                <span wire:loading.remove wire:target="saveLogo">{{ __('Save Logo') }}</span>
                                <span wire:loading wire:target="saveLogo">{{ __('Saving...') }}</span>
                            </flux:button>
                        </div>

                        @if(session('logo-uploaded'))
                            <flux:text class="text-green-600 dark:text-green-400 font-medium">
                                {{ session('logo-uploaded') }}
                            </flux:text>
                        @endif

                        @if(session('logo-error'))
                            <flux:error>{{ session('logo-error') }}</flux:error>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Information Form -->
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            @if(auth()->user()->tenants->isNotEmpty())
                <flux:input wire:model="subdomain" :label="__('Subdomain')" type="text" autofocus autocomplete="name"/>
            @endif
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name"/>

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email"/>

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer"
                                       wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form/>
    </x-settings.layout>
</section>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('logo-updated', () => {
            // Reload the page to update all logo instances
            window.location.reload();
        });
    });
</script>
