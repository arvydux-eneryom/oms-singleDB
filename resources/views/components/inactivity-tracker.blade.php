@props([
    'enabled' => config('auth.inactivity.enabled', true),
    'timeout' => config('auth.inactivity.timeout', 86400),
    'warning' => config('auth.inactivity.warning', 300),
])

@if($enabled)
<div
    x-data="inactivityTracker({
        timeout: {{ $timeout }},
        warning: {{ $warning }},
        logoutUrl: '{{ route('logout') }}',
        csrfToken: '{{ csrf_token() }}'
    })"
    x-init="init()"
    x-cloak
>
    <!-- Warning Modal -->
    <flux:modal
        name="inactivity-warning"
        class="max-w-md"
        :closable="false"
    >
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <flux:icon.exclamation-triangle class="w-12 h-12 text-amber-500" />
                </div>
                <div class="flex-1">
                    <flux:heading size="lg">{{ __('Session Expiring Soon') }}</flux:heading>
                    <flux:subheading class="mt-2">
                        {{ __('Your session will expire due to inactivity in:') }}
                    </flux:subheading>
                </div>
            </div>

            <div class="flex items-center justify-center">
                <div class="text-center">
                    <div class="text-6xl font-bold text-zinc-900 dark:text-zinc-100" x-text="formatTime(remainingSeconds)"></div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">{{ __('minutes:seconds') }}</div>
                </div>
            </div>

            <flux:text>
                {{ __('Click "Stay Logged In" to continue your session, or you will be automatically logged out.') }}
            </flux:text>

            <div class="flex gap-3">
                <flux:button
                    variant="primary"
                    class="flex-1"
                    @click="extendSession()"
                >
                    {{ __('Stay Logged In') }}
                </flux:button>

                <flux:button
                    variant="ghost"
                    @click="logout()"
                >
                    {{ __('Log Out Now') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('inactivityTracker', (config) => ({
        timeout: config.timeout * 1000, // Convert to milliseconds
        warning: config.warning * 1000,
        logoutUrl: config.logoutUrl,
        csrfToken: config.csrfToken,
        timer: null,
        warningTimer: null,
        showWarning: false,
        remainingSeconds: 0,
        countdownInterval: null,
        debounceTimer: null,
        storageKey: 'last_activity_timestamp',

        init() {
            this.setupActivityListeners();
            this.setupStorageListener();
            this.setupLivewireListener();
            this.resetTimer();
        },

        setupActivityListeners() {
            const events = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];

            events.forEach(event => {
                document.addEventListener(event, () => this.debouncedActivity(), { passive: true });
            });
        },

        setupStorageListener() {
            // Listen for activity in other tabs
            window.addEventListener('storage', (e) => {
                if (e.key === this.storageKey && e.newValue) {
                    const lastActivity = parseInt(e.newValue);
                    const now = Date.now();

                    // If activity happened in another tab recently, reset our timer
                    if (now - lastActivity < 1000) {
                        this.resetTimer();
                    }
                }
            });
        },

        setupLivewireListener() {
            // Reset timer on Livewire navigation and requests
            if (window.Livewire) {
                Livewire.hook('request', () => {
                    this.recordActivity();
                });
            }
        },

        debouncedActivity() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.recordActivity();
            }, 500); // Debounce for 500ms
        },

        recordActivity() {
            // Update localStorage to sync with other tabs
            localStorage.setItem(this.storageKey, Date.now().toString());

            if (this.showWarning) {
                this.extendSession();
            } else {
                this.resetTimer();
            }
        },

        resetTimer() {
            this.clearTimers();

            // Set warning timer
            this.warningTimer = setTimeout(() => {
                this.showWarningModal();
            }, this.timeout - this.warning);

            // Set logout timer
            this.timer = setTimeout(() => {
                this.logout();
            }, this.timeout);
        },

        showWarningModal() {
            this.showWarning = true;
            this.remainingSeconds = Math.floor(this.warning / 1000);

            // Show the modal
            $flux.modal('inactivity-warning').show();

            // Start countdown
            this.countdownInterval = setInterval(() => {
                this.remainingSeconds--;

                if (this.remainingSeconds <= 0) {
                    clearInterval(this.countdownInterval);
                }
            }, 1000);
        },

        extendSession() {
            this.showWarning = false;
            this.remainingSeconds = 0;

            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
            }

            // Hide modal
            $flux.modal('inactivity-warning').close();

            // Reset timers
            this.resetTimer();
        },

        clearTimers() {
            if (this.timer) {
                clearTimeout(this.timer);
                this.timer = null;
            }

            if (this.warningTimer) {
                clearTimeout(this.warningTimer);
                this.warningTimer = null;
            }

            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
            }
        },

        async logout() {
            this.clearTimers();

            try {
                const response = await fetch(this.logoutUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                if (response.ok || response.redirected) {
                    // Clear activity tracking
                    localStorage.removeItem(this.storageKey);

                    // Redirect to login
                    window.location.href = '{{ route('login') }}';
                } else {
                    console.error('Logout failed:', response.status);
                    // Still redirect to login on failure
                    window.location.href = '{{ route('login') }}';
                }
            } catch (error) {
                console.error('Logout error:', error);
                // Fallback to login page
                window.location.href = '{{ route('login') }}';
            }
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }
    }));
});
</script>
@endif
