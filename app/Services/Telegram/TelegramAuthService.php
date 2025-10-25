<?php

namespace App\Services\Telegram;

use App\Models\TelegramSession;
use App\Repositories\TelegramSessionRepository;
use danog\MadelineProto\API;
use danog\MadelineProto\Exception;
use Illuminate\Support\Facades\Log;

class TelegramAuthService
{
    public function __construct(
        protected TelegramSessionRepository $sessionRepository,
        protected TelegramClientService $clientService,
        protected TelegramBugsnagService $bugsnag
    ) {}

    /**
     * Get or create session for user
     */
    public function getOrCreateSession(int $userId, ?string $ipAddress = null, ?string $userAgent = null): TelegramSession
    {
        $session = $this->sessionRepository->getActiveSession($userId);

        if (! $session) {
            $session = $this->sessionRepository->createSession($userId, $ipAddress, $userAgent);
        }

        // Update activity
        $this->sessionRepository->updateActivity($session);

        return $session;
    }

    /**
     * Generate QR code for login
     */
    public function generateQrCode(API $client, TelegramSession $session): ?string
    {
        try {
            $this->bugsnag->leaveBreadcrumb('Starting QR code generation', ['session_id' => $session->id]);

            // Check if already logged in
            if ($client->getAuthorization() === API::LOGGED_IN) {
                return null;
            }

            // Check if waiting for code (phone login in progress)
            if ($client->getAuthorization() === API::WAITING_CODE) {
                return null;
            }

            // Get QR login object
            $qrLogin = $client->qrLogin();

            if ($qrLogin) {
                // Generate SVG QR code (200px size, 2px border)
                $this->bugsnag->leaveBreadcrumb('QR code generated successfully', ['session_id' => $session->id]);

                return $qrLogin->getQRSvg(200, 2);
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('QR code generation failed', [
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyQrCodeError($e, $session);

            return null;
        }
    }

    /**
     * Initiate phone number login
     */
    public function initiatePhoneLogin(API $client, string $phone, TelegramSession $session): array
    {
        try {
            // Validate phone number format (E.164)
            if (! preg_match('/^\+[1-9]\d{1,14}$/', $phone)) {
                return [
                    'success' => false,
                    'error' => 'Invalid phone number format. Please use E.164 format (e.g., +1234567890)',
                ];
            }

            $this->bugsnag->leaveBreadcrumb('Initiating phone login', [
                'phone' => substr($phone, 0, 5).'***',
                'session_id' => $session->id,
            ]);

            Log::info('Initiating phone login', [
                'phone' => substr($phone, 0, 5).'***',
            ]);

            $result = $client->phoneLogin($phone);

            Log::info('Phone login result', [
                'result' => $result,
                'result_type' => gettype($result),
            ]);

            if ($result === false) {
                Log::warning('Phone login returned false');

                return [
                    'success' => false,
                    'error' => 'Failed to initiate phone login. Please try again or use QR code login.',
                ];
            }

            if ($result === API::LOGGED_IN) {
                Log::info('User already logged in via phone login');
                $this->bugsnag->leaveBreadcrumb('User already logged in via phone', ['session_id' => $session->id]);

                return [
                    'success' => true,
                    'logged_in' => true,
                    'message' => 'Successfully logged in to Telegram.',
                ];
            }

            Log::info('Phone login code sent successfully');

            // Determine where the code was sent based on the response
            $codeType = is_array($result) && isset($result['type']['_']) ? $result['type']['_'] : 'unknown';
            $message = 'Verification code sent. Please enter the code.';

            if ($codeType === 'auth.sentCodeTypeApp') {
                $message = 'Code sent to your Telegram app! Open Telegram on any device and check for a message from "Telegram" with your login code.';
            } elseif ($codeType === 'auth.sentCodeTypeSms') {
                $message = 'Code sent via SMS to your phone number. Please check your text messages.';
            } elseif ($codeType === 'auth.sentCodeTypeCall') {
                $message = 'You will receive a phone call with your verification code.';
            }

            $this->bugsnag->leaveBreadcrumb('Phone login code sent', [
                'session_id' => $session->id,
                'code_type' => $codeType,
            ]);

            return [
                'success' => true,
                'logged_in' => false,
                'code_required' => true,
                'message' => $message,
                'code_type' => $codeType,
            ];
        } catch (Exception $e) {
            // Handle flood wait errors
            if (strpos($e->getMessage(), 'FLOOD_WAIT') !== false || strpos($e->getMessage(), 'FloodWaitError') !== false) {
                preg_match('/(\d+)/', $e->getMessage(), $matches);
                $waitTime = $matches[1] ?? 'unknown';

                $this->bugsnag->notifyAuthError($e, $session, 'phone', [
                    'phone' => substr($phone, 0, 5).'***',
                    'flood_wait' => true,
                    'wait_time' => $waitTime,
                ]);

                return [
                    'success' => false,
                    'error' => "Too many requests. Please wait {$waitTime} seconds before trying again.",
                    'wait_time' => $waitTime,
                ];
            }

            Log::error('Phone login failed', [
                'phone' => substr($phone, 0, 5).'***',
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyAuthError($e, $session, 'phone', [
                'phone' => substr($phone, 0, 5).'***',
            ]);

            return [
                'success' => false,
                'error' => 'Login failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Complete phone login with verification code
     */
    public function completePhoneLogin(API $client, string $code, TelegramSession $session): array
    {
        try {
            // Validate code format (5 digits)
            if (! preg_match('/^\d{5}$/', $code)) {
                return [
                    'success' => false,
                    'error' => 'Invalid verification code format. Code must be exactly 5 digits.',
                ];
            }

            $this->bugsnag->leaveBreadcrumb('Completing phone login with verification code', [
                'session_id' => $session->id,
            ]);

            $result = $client->completePhoneLogin($code);

            if ($result === API::LOGGED_IN) {
                $this->bugsnag->leaveBreadcrumb('Phone login completed successfully', [
                    'session_id' => $session->id,
                ]);

                return [
                    'success' => true,
                    'message' => 'Successfully logged in to Telegram.',
                ];
            }

            return [
                'success' => false,
                'error' => 'Verification failed. Please check the code and try again.',
            ];
        } catch (\Throwable $e) {
            Log::error('Complete phone login failed', [
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyAuthError($e, $session, 'phone_verification');

            return [
                'success' => false,
                'error' => 'Verification failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Terminate session and logout
     */
    public function terminateSession(TelegramSession $session, ?API $client = null): array
    {
        try {
            $this->bugsnag->leaveBreadcrumb('Terminating Telegram session', [
                'session_id' => $session->id,
            ]);

            $success = $this->clientService->safeLogout($client, $session);

            if ($success) {
                $this->bugsnag->leaveBreadcrumb('Session terminated successfully', [
                    'session_id' => $session->id,
                ]);

                return [
                    'success' => true,
                    'message' => 'Successfully logged out from Telegram.',
                ];
            }

            return [
                'success' => false,
                'error' => 'Logout completed but with some errors. Session has been deactivated.',
            ];
        } catch (\Throwable $e) {
            Log::error('Session termination failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyAuthError($e, $session, 'logout');

            return [
                'success' => false,
                'error' => 'Failed to terminate session: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check if user has active Telegram session
     */
    public function hasActiveSession(int $userId): bool
    {
        $session = $this->sessionRepository->getActiveSession($userId);

        return $session !== null && $session->isValid();
    }

    /**
     * Validate session ownership
     */
    public function validateSessionOwnership(TelegramSession $session, int $userId): bool
    {
        return $this->sessionRepository->validateSessionOwnership($session, $userId);
    }
}
