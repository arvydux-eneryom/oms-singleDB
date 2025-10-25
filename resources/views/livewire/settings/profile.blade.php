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
    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
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

        if ($this->logo) {
            $this->validate([
                'logo' => 'image|max:2048',
            ], [
                'logo.max' => 'The logo must not exceed 2MB in size.',
            ]);

            $company = Auth::user()->company;
            $company->clearMediaCollection('logo'); // Optional: clear previous logo
            $company->addMedia($this->logo->getRealPath())
                ->usingFileName($this->logo->getClientOriginalName())
                ->withResponsiveImages()
                ->toMediaCollection('logo');
        }

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
        <h4 class="block text-sm font-medium">{{ __('Current Logo:') }}</h4>
        @if (Auth::user()->company?->getFirstMediaUrl('logo'))
            <div class="flex aspect-square size-30 items-center justify-center rounded-md">
                <img class="rounded-md" src="{{ url(Auth::user()->company?->getFirstMediaUrl('logo')) }}"
                     alt="{{ __('Company Logo') }}">
            </div>
        @else
            <span class="text-sm text-zinc-500 dark:text-white/70"
                  data-flux-subheading="">
                No logo
            </span>
        @endif

        <form wire:submit="updateProfileInformation" enctype="multipart/form-data" class="my-6 w-full space-y-6">
            <flux:input type="file" name="logo" wire:model="logo"/>
            @error('logo')
            <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
            @enderror
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
