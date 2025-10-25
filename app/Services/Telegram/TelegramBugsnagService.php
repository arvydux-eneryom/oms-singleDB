<?php

namespace App\Services\Telegram;

use App\Models\TelegramSession;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Auth;
use Throwable;

class TelegramBugsnagService
{
    /**
     * Report an error to Bugsnag with Telegram-specific metadata
     */
    public function notifyError(
        Throwable $exception,
        ?TelegramSession $session = null,
        array $additionalMetadata = []
    ): void {
        if (! config('bugsnag.api_key')) {
            return;
        }

        Bugsnag::notifyException($exception, function ($report) use ($session, $additionalMetadata) {
            // Add Telegram integration context
            $report->addMetadata([
                'telegram' => array_merge([
                    'integration' => 'MadelineProto',
                    'session_id' => $session?->id,
                    'session_status' => $session?->is_active ? 'active' : 'inactive',
                    'session_path' => $session?->session_path,
                    'user_id' => $session?->user_id ?? Auth::id(),
                    'last_activity' => $session?->last_activity_at?->toDateTimeString(),
                ], $additionalMetadata),
            ]);

            // Set user information if available
            if ($session) {
                $report->setUser([
                    'id' => $session->user_id,
                    'session_id' => $session->id,
                ]);
            } elseif (Auth::check()) {
                $report->setUser([
                    'id' => Auth::id(),
                    'email' => Auth::user()->email ?? null,
                    'name' => Auth::user()->name ?? null,
                ]);
            }
        });
    }

    /**
     * Report authentication error
     */
    public function notifyAuthError(
        Throwable $exception,
        TelegramSession $session,
        string $authMethod,
        array $additionalContext = []
    ): void {
        $this->notifyError($exception, $session, array_merge([
            'auth_method' => $authMethod,
            'error_type' => 'authentication',
        ], $additionalContext));
    }

    /**
     * Report client initialization error
     */
    public function notifyClientError(
        Throwable $exception,
        TelegramSession $session,
        array $additionalContext = []
    ): void {
        $this->notifyError($exception, $session, array_merge([
            'error_type' => 'client_initialization',
        ], $additionalContext));
    }

    /**
     * Report channel operation error
     */
    public function notifyChannelError(
        Throwable $exception,
        ?TelegramSession $session = null,
        ?string $channelIdentifier = null,
        array $additionalContext = []
    ): void {
        $this->notifyError($exception, $session, array_merge([
            'error_type' => 'channel_operation',
            'channel_identifier' => $channelIdentifier,
        ], $additionalContext));
    }

    /**
     * Report message operation error
     */
    public function notifyMessageError(
        Throwable $exception,
        ?TelegramSession $session = null,
        array $additionalContext = []
    ): void {
        $this->notifyError($exception, $session, array_merge([
            'error_type' => 'message_operation',
        ], $additionalContext));
    }

    /**
     * Report session cleanup error
     */
    public function notifyCleanupError(
        Throwable $exception,
        TelegramSession $session,
        array $additionalContext = []
    ): void {
        $this->notifyError($exception, $session, array_merge([
            'error_type' => 'session_cleanup',
        ], $additionalContext));
    }

    /**
     * Report QR code generation error
     */
    public function notifyQrCodeError(
        Throwable $exception,
        TelegramSession $session,
        array $additionalContext = []
    ): void {
        $this->notifyError($exception, $session, array_merge([
            'error_type' => 'qr_code_generation',
        ], $additionalContext));
    }

    /**
     * Leave a breadcrumb for debugging
     */
    public function leaveBreadcrumb(string $message, array $metadata = [], string $type = 'log'): void
    {
        if (! config('bugsnag.api_key')) {
            return;
        }

        Bugsnag::leaveBreadcrumb($message, $type, $metadata);
    }

    /**
     * Set custom severity for the next error
     */
    public function setSeverity(string $severity): void
    {
        // Severity can be: 'error', 'warning', or 'info'
        // This is handled in the individual notify methods via callback
    }
}
