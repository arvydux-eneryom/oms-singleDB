<?php

namespace App\Repositories;

use App\Models\TelegramSession;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TelegramSessionRepository
{
    public function createSession(
        int $userId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?int $expiresInDays = null,
        bool $deactivateExisting = false
    ): TelegramSession {
        // Only deactivate existing sessions if explicitly requested
        if ($deactivateExisting) {
            $this->deactivateUserSessions($userId);
        }

        $sessionIdentifier = TelegramSession::generateSecureIdentifier();
        $relativeDir = config('services.telegram.session_dir', 'telegram/sessions');
        $sessionPath = storage_path("app/{$relativeDir}/{$sessionIdentifier}");

        // Use config value if not provided
        $expiresInDays = $expiresInDays ?? config('services.telegram.session_expires_days', 30);

        // Create session directory with secure permissions
        if (!is_dir($sessionPath)) {
            if (!mkdir($sessionPath, 0700, true) && !is_dir($sessionPath)) {
                throw new \RuntimeException("Failed to create session directory: {$sessionPath}");
            }
        }

        return TelegramSession::create([
            'user_id' => $userId,
            'session_identifier' => $sessionIdentifier,
            'session_path' => $sessionPath,
            'last_activity_at' => now(),
            'expires_at' => now()->addDays($expiresInDays),
            'is_active' => true,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function getActiveSession(int $userId): ?TelegramSession
    {
        return TelegramSession::forUser($userId)
            ->active()
            ->first();
    }

    public function getSessionByIdentifier(string $identifier): ?TelegramSession
    {
        return TelegramSession::where('session_identifier', $identifier)
            ->active()
            ->first();
    }

    public function updateActivity(TelegramSession $session): void
    {
        $session->update(['last_activity_at' => now()]);
    }

    public function deactivateSession(TelegramSession $session): void
    {
        $session->update(['is_active' => false]);

        // Clean up session files
        $this->cleanupSessionFiles($session);
    }

    public function deactivateUserSessions(int $userId): void
    {
        $sessions = TelegramSession::forUser($userId)
            ->active()
            ->get();

        foreach ($sessions as $session) {
            $this->deactivateSession($session);
        }
    }

    public function cleanupExpiredSessions(): int
    {
        $expiredSessions = TelegramSession::expired()->get();
        $count = 0;

        foreach ($expiredSessions as $session) {
            try {
                $this->deactivateSession($session);
                $count++;
            } catch (\Throwable $e) {
                Log::error('Failed to cleanup expired session: ' . $e->getMessage(), [
                    'session_id' => $session->id,
                ]);
            }
        }

        return $count;
    }

    protected function cleanupSessionFiles(TelegramSession $session): void
    {
        try {
            if (is_dir($session->session_path)) {
                File::deleteDirectory($session->session_path);
                Log::info('Session files cleaned up', [
                    'session_id' => $session->id,
                    'path' => $session->session_path,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to cleanup session files: ' . $e->getMessage(), [
                'session_id' => $session->id,
                'path' => $session->session_path,
            ]);
        }
    }

    public function validateSessionOwnership(TelegramSession $session, int $userId): bool
    {
        return $session->user_id === $userId && $session->isValid();
    }

    public function getSessionStats(int $userId): array
    {
        $activeSession = $this->getActiveSession($userId);

        return [
            'has_active_session' => $activeSession !== null,
            'session_id' => $activeSession?->id,
            'last_activity' => $activeSession?->last_activity_at,
            'expires_at' => $activeSession?->expires_at,
        ];
    }
}
