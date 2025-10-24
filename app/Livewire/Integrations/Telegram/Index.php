<?php

namespace App\Livewire\Integrations\Telegram;

use App\Models\TelegramSession;
use App\Repositories\TelegramSessionRepository;
use App\Services\Telegram\TelegramAuthService;
use App\Services\Telegram\TelegramChannelService;
use App\Services\Telegram\TelegramClientService;
use App\Services\Telegram\TelegramMessageService;
use danog\MadelineProto\API;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Index extends Component
{
    // State properties
    public int $telegramAuthState = 0; //  0 - not logged in, 1 - waiting code, 3 - logged in, 4 - logged out

    public array $channels = [];

    public string $qrSvg = '';

    public ?array $telegramLoggedUserData = [];

    public string $phone = '';

    public string $loginCode = '';

    public string $title = '';

    public string $description = '';

    // Services
    protected TelegramSessionRepository $sessionRepository;

    protected TelegramAuthService $authService;

    protected TelegramChannelService $channelService;

    protected TelegramMessageService $messageService;

    protected TelegramClientService $clientService;

    // Session tracking
    protected ?TelegramSession $currentSession = null;

    protected ?API $client = null;

    protected $rules = [
        'title' => 'required|string|max:128',
        'description' => 'required|string|max:255',
    ];

    public function boot(): void
    {
        $this->sessionRepository = new TelegramSessionRepository;
        $this->clientService = new TelegramClientService($this->sessionRepository);
        $this->authService = new TelegramAuthService($this->sessionRepository, $this->clientService);
        $this->channelService = new TelegramChannelService;
        $this->messageService = new TelegramMessageService;
    }

    public function mount()
    {
        $this->checkTelegramSession();
    }

    /**
     * Check Telegram session status
     */
    protected function checkTelegramSession()
    {
        try {
            // Only GET existing active session, don't create a new one automatically
            $this->currentSession = $this->sessionRepository->getActiveSession(Auth::id());

            if ($this->currentSession && $this->currentSession->isValid()) {
                // Initialize client
                $this->client = $this->clientService->initializeClient($this->currentSession);

                if ($this->client && $this->clientService->isAuthorized($this->client)) {
                    $this->telegramLoggedUserData = $this->clientService->getLoggedUserData($this->client);
                    $this->telegramAuthState = 3; // Logged in
                } else {
                    // Session exists but not authorized - show login screen
                    $this->showTelegramLogin();
                }
            } else {
                // No active session - show login screen without creating session
                $this->telegramAuthState = 0;
            }
        } catch (\Throwable $e) {
            Log::error('Telegram session check failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            // On error, just show login screen
            $this->telegramAuthState = 0;
        }
    }

    /**
     * Get QR code for login
     */
    public function getQrCode()
    {
        try {
            if (! $this->client) {
                $this->currentSession = $this->authService->getOrCreateSession(
                    Auth::id(),
                    request()->ip(),
                    request()->userAgent()
                );
                $this->client = $this->clientService->initializeClient($this->currentSession);
            }

            // Check if client initialization failed
            if (! $this->client) {
                Log::error('Failed to initialize Telegram client for QR code generation', [
                    'user_id' => Auth::id(),
                ]);
                session()->flash('error', 'Failed to initialize Telegram session. Please try again.');

                return;
            }

            // Check if already authorized
            if ($this->clientService->isAuthorized($this->client)) {
                $this->mount();

                return;
            }

            // Generate QR code SVG
            $qrSvg = $this->authService->generateQrCode($this->client);

            if ($qrSvg) {
                $this->qrSvg = $qrSvg;
            }
        } catch (\Throwable $e) {
            Log::error('QR code generation failed', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to get QR code.');
        }
    }

    /**
     * Send phone number for login
     */
    public function sendPhoneNumber()
    {
        $this->phone = preg_replace('/\s+/', '', $this->phone);
        $this->validate([
            'phone' => ['required', 'regex:/^\+\d{10,15}$/'],
        ]);

        try {
            if (! $this->client) {
                $this->currentSession = $this->authService->getOrCreateSession(
                    Auth::id(),
                    request()->ip(),
                    request()->userAgent()
                );
                $this->client = $this->clientService->initializeClient($this->currentSession);
            }

            // Check if client initialization failed
            if (! $this->client) {
                Log::error('Failed to initialize Telegram client for phone login', [
                    'user_id' => Auth::id(),
                ]);
                session()->flash('error', 'Failed to initialize Telegram session. Please try again.');

                return;
            }

            $result = $this->authService->initiatePhoneLogin($this->client, $this->phone);

            if ($result['success']) {
                if ($result['logged_in'] ?? false) {
                    $this->mount();
                    session()->flash('success', $result['message']);
                } else {
                    // Code is required - update state to show login code input
                    $this->telegramAuthState = 1;  // 1 = waiting for code
                    session()->flash('message', $result['message']);
                }
            } else {
                session()->flash('error', $result['error']);
            }
        } catch (\Throwable $e) {
            Log::error('Phone login failed', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'An unexpected error occurred.');
        }
    }

    /**
     * Complete phone login with code
     */
    public function sendCompletePhoneLogin()
    {
        $this->validate(['loginCode' => ['required', 'digits:5']]);

        try {
            if (! $this->client) {
                $this->currentSession = $this->authService->getOrCreateSession(Auth::id());
                $this->client = $this->clientService->initializeClient($this->currentSession);
            }

            // Check if client initialization failed
            if (! $this->client) {
                Log::error('Failed to initialize Telegram client for phone login completion', [
                    'user_id' => Auth::id(),
                ]);
                session()->flash('error', 'Failed to initialize Telegram session. Please try logging in again.');
                $this->mount();

                return;
            }

            $result = $this->authService->completePhoneLogin($this->client, (string) $this->loginCode);

            if ($result['success']) {
                session()->flash('message', $result['message']);
                $this->mount();
            } else {
                session()->flash('error', $result['error']);
                $this->mount();
            }
        } catch (\Throwable $e) {
            Log::error('Complete phone login failed', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'An error occurred: '.$e->getMessage());
            $this->mount();
        }
    }

    /**
     * Logout from Telegram
     */
    public function logoutFromTelegram()
    {
        try {
            // Get current session if not already set
            if (! $this->currentSession) {
                $this->currentSession = $this->sessionRepository->getActiveSession(Auth::id());
            }

            // Only call terminate if we have a session
            if ($this->currentSession) {
                $this->authService->terminateSession($this->currentSession, $this->client);
            }
        } catch (\Throwable $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // Always clear local state (even if termination failed or no session exists)
        $this->currentSession = null;
        $this->client = null;
        $this->telegramAuthState = 0;
        $this->telegramLoggedUserData = null;
        $this->channels = [];
        $this->qrSvg = '';
        $this->phone = '';
        $this->loginCode = '';
    }

    /**
     * Get user's Telegram channels
     */
    public function getTelegramChannels()
    {
        try {
            // Ensure we have a session
            if (! $this->currentSession) {
                $this->currentSession = $this->sessionRepository->getActiveSession(Auth::id());
            }

            if (! $this->client) {
                $this->client = $this->clientService->initializeClient($this->currentSession);
            }

            if (! $this->client) {
                Log::error('Failed to initialize Telegram client for getting channels', [
                    'user_id' => Auth::id(),
                ]);
                $this->channels = [];

                return;
            }

            $this->channels = $this->channelService->getChannels($this->client);
        } catch (\Throwable $e) {
            Log::error('Failed to get channels', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to get Telegram channels: '.$e->getMessage());
            $this->channels = [];
        }
    }

    /**
     * Create a new channel
     */
    public function createChannel()
    {
        $this->validate([
            'title' => ['required', 'string', 'min:1', 'max:128'],
            'description' => ['required', 'string', 'min:1', 'max:255'],
        ]);

        try {
            // Ensure we have a session
            if (! $this->currentSession) {
                $this->currentSession = $this->sessionRepository->getActiveSession(Auth::id());
            }

            if (! $this->client) {
                $this->client = $this->clientService->initializeClient($this->currentSession);
            }

            if (! $this->client) {
                Log::error('Failed to initialize Telegram client for channel creation', [
                    'user_id' => Auth::id(),
                ]);
                session()->flash('error', 'Failed to initialize Telegram session. Please try again.');

                return;
            }

            $result = $this->channelService->createChannel($this->client, $this->title, $this->description);

            if ($result['success']) {
                $this->title = '';
                $this->description = '';
                session()->flash('message', $result['message']);
                $this->getTelegramChannels();
            } else {
                session()->flash('error', $result['error']);
            }
        } catch (\Throwable $e) {
            Log::error('Channel creation failed', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Channel creation failed: '.$e->getMessage());
        }
    }

    /**
     * Delete a channel
     */
    public function deleteTelegramChannel($channelId)
    {
        try {
            // Ensure we have a session
            if (! $this->currentSession) {
                $this->currentSession = $this->sessionRepository->getActiveSession(Auth::id());
            }

            if (! $this->client) {
                $this->client = $this->clientService->initializeClient($this->currentSession);
            }

            if (! $this->client) {
                Log::error('Failed to initialize Telegram client for channel deletion', [
                    'user_id' => Auth::id(),
                ]);
                session()->flash('error', 'Failed to initialize Telegram session. Please try again.');

                return;
            }

            $result = $this->channelService->deleteChannel($this->client, $channelId);

            if ($result['success']) {
                session()->flash('message', $result['message']);
                $this->getTelegramChannels();
            } else {
                session()->flash('error', $result['error']);
            }
        } catch (\Throwable $e) {
            Log::error('Channel deletion failed', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'An unexpected error occurred.');
        }
    }

    /**
     * Send invite to user
     */
    public function sendChannelInviteToUser($channelId, $telegramUsername)
    {
        try {
            // Ensure we have a session
            if (! $this->currentSession) {
                $this->currentSession = $this->sessionRepository->getActiveSession(Auth::id());
            }

            if (! $this->client) {
                $this->client = $this->clientService->initializeClient($this->currentSession);
            }

            if (! $this->client) {
                Log::error('Failed to initialize Telegram client for sending invite', [
                    'user_id' => Auth::id(),
                ]);
                session()->flash('error', 'Failed to initialize Telegram session. Please try again.');

                return;
            }

            $result = $this->channelService->inviteUserToChannel($this->client, $channelId, $telegramUsername);

            if ($result['success']) {
                session()->flash('success', $result['message']);
            } else {
                session()->flash('error', $result['error']);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send invite', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to send invite: '.$e->getMessage());
        }
    }

    /**
     * Send message to channel
     */
    public function sendMessageToChannel($channelId)
    {
        try {
            // Ensure we have a session
            if (! $this->currentSession) {
                $this->currentSession = $this->sessionRepository->getActiveSession(Auth::id());
            }

            if (! $this->client) {
                $this->client = $this->clientService->initializeClient($this->currentSession);
            }

            if (! $this->client) {
                Log::error('Failed to initialize Telegram client for sending message', [
                    'user_id' => Auth::id(),
                ]);
                session()->flash('error', 'Failed to initialize Telegram session. Please try again.');

                return;
            }

            $message = 'Welcome to our channel. This is a message from Laravel system';
            $result = $this->messageService->sendMessageToChannel($this->client, $channelId, $message);

            if ($result['success']) {
                session()->flash('message', $result['message']);
            } else {
                session()->flash('error', $result['error']);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send message', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to send message: '.$e->getMessage());
        }
    }

    /**
     * Show Telegram login screen
     */
    public function showTelegramLogin(): void
    {
        // Just set state to show login screen
        // Don't automatically generate QR code to avoid creating sessions
        $this->telegramAuthState = 0;
    }

    /**
     * Render the component
     */
    public function render()
    {
        // Show dashboard if logged in
        if ($this->telegramAuthState === 3) {
            $this->getTelegramChannels();

            return view('livewire.integrations.telegram.dashboard');
        }

        // Show login screen for all other states (0 = not logged in, 1 = waiting for code)
        return view('livewire.integrations.telegram.index');
    }
}
