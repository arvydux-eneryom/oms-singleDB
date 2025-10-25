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
        protected TelegramSessionRepository $sessionRepository,
        protected TelegramBugsnagService $bugsnag
    ) {}

    /**
     * Initialize MadelineProto API client for a user session
     */
    public function initializeClient(TelegramSession $session): ?API
    {
        try {
            $this->bugsnag->leaveBreadcrumb('Initializing Telegram client', [
                'session_id' => $session->id,
            ]);

            if (! is_dir($session->session_path)) {
                mkdir($session->session_path, 0700, true);
            }

            $settings = new Settings;

            // Configure app info
            $appInfo = new AppInfo;
            $appInfo->setApiId((int) config('services.telegram.api_id'));
            $appInfo->setApiHash(config('services.telegram.api_hash'));
            $settings->setAppInfo($appInfo);

            $client = new API($session->session_path.'/session.madeline', $settings);

            $this->bugsnag->leaveBreadcrumb('Telegram client initialized successfully', [
                'session_id' => $session->id,
            ]);

            return $client;
        } catch (\Throwable $e) {
            Log::error('Failed to initialize Telegram client', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->bugsnag->notifyClientError($e, $session);

            return null;
        }
    }

    /**
     * Check if a client is authorized
     */
    public function isAuthorized(?API $client): bool
    {
        if (! $client) {
            return false;
        }

        try {
            $authorization = $client->getAuthorization();

            return $authorization === API::LOGGED_IN;
        } catch (\Throwable $e) {
            Log::error('Error checking authorization', [
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyError($e, null, [
                'operation' => 'check_authorization',
            ]);

            return false;
        }
    }

    /**
     * Get logged in user data from Telegram
     */
    public function getLoggedUserData(?API $client): ?array
    {
        if (! $client || ! $this->isAuthorized($client)) {
            return null;
        }

        try {
            $this->bugsnag->leaveBreadcrumb('Fetching logged user data from Telegram');

            $fullInfo = $client->getSelf();

            $this->bugsnag->leaveBreadcrumb('User data fetched successfully');

            return $fullInfo;
        } catch (\Throwable $e) {
            Log::error('Error getting user data', [
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyError($e, null, [
                'operation' => 'get_user_data',
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
            $this->bugsnag->leaveBreadcrumb('Starting safe logout', [
                'session_id' => $session->id,
            ]);

            if ($client && $this->isAuthorized($client)) {
                $client->logout();
            }

            $this->sessionRepository->deactivateSession($session);

            // Cleanup session files
            if (is_dir($session->session_path)) {
                $this->cleanupSessionDirectory($session->session_path);
            }

            $this->bugsnag->leaveBreadcrumb('Safe logout completed', [
                'session_id' => $session->id,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Error during safe logout', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyError($e, $session, [
                'operation' => 'safe_logout',
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
            $this->bugsnag->leaveBreadcrumb('Cleaning up session directory', [
                'path' => basename($path),
            ]);

            $lockFiles = [
                $path.'/ipcState.php.lock',
                $path.'/lightState.php.lock',
                $path.'/safe.php.lock',
            ];

            foreach ($lockFiles as $lockFile) {
                if (file_exists($lockFile)) {
                    @unlink($lockFile);
                }
            }

            // Remove session files
            $files = glob($path.'/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }

            // Remove directory
            @rmdir($path);

            $this->bugsnag->leaveBreadcrumb('Session directory cleaned up', [
                'path' => basename($path),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to cleanup session directory', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyError($e, null, [
                'operation' => 'cleanup_session_directory',
                'path' => basename($path),
            ]);
        }
    }
}
