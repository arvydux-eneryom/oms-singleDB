<?php

namespace App\Services\Telegram;

use App\Models\TelegramSession;
use App\Repositories\TelegramSessionRepository;
use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use Illuminate\Support\Facades\Log;

class TelegramClientService
{
    public function __construct(
        protected TelegramSessionRepository $sessionRepository
    ) {}

    /**
     * Initialize MadelineProto API client for a user session
     */
    public function initializeClient(TelegramSession $session): ?API
    {
        try {
            if (!is_dir($session->session_path)) {
                mkdir($session->session_path, 0700, true);
            }

            $settings = new Settings();

            // Configure app info
            $appInfo = new AppInfo();
            $appInfo->setApiId((int) config('services.telegram.api_id'));
            $appInfo->setApiHash(config('services.telegram.api_hash'));
            $settings->setAppInfo($appInfo);

            return new API($session->session_path . '/session.madeline', $settings);
        } catch (\Throwable $e) {
            Log::error('Failed to initialize Telegram client', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Check if a client is authorized
     */
    public function isAuthorized(?API $client): bool
    {
        if (!$client) {
            return false;
        }

        try {
            $authorization = $client->getAuthorization();
            return $authorization === API::LOGGED_IN;
        } catch (\Throwable $e) {
            Log::error('Error checking authorization', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get logged in user data from Telegram
     */
    public function getLoggedUserData(?API $client): ?array
    {
        if (!$client || !$this->isAuthorized($client)) {
            return null;
        }

        try {
            $fullInfo = $client->getSelf();
            return $fullInfo;
        } catch (\Throwable $e) {
            Log::error('Error getting user data', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Safely logout from Telegram and cleanup
     */
    public function safeLogout(?API $client, TelegramSession $session): bool
    {
        try {
            if ($client && $this->isAuthorized($client)) {
                $client->logout();
            }

            $this->sessionRepository->deactivateSession($session);

            // Cleanup session files
            if (is_dir($session->session_path)) {
                $this->cleanupSessionDirectory($session->session_path);
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Error during safe logout', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            // Still deactivate the session even if logout fails
            $this->sessionRepository->deactivateSession($session);

            return false;
        }
    }

    /**
     * Clean up session directory and lock files
     */
    protected function cleanupSessionDirectory(string $path): void
    {
        try {
            $lockFiles = [
                $path . '/ipcState.php.lock',
                $path . '/lightState.php.lock',
                $path . '/safe.php.lock',
            ];

            foreach ($lockFiles as $lockFile) {
                if (file_exists($lockFile)) {
                    @unlink($lockFile);
                }
            }

            // Remove session files
            $files = glob($path . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }

            // Remove directory
            @rmdir($path);
        } catch (\Throwable $e) {
            Log::warning('Failed to cleanup session directory', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
