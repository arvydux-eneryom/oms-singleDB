<?php

namespace App\Services\Telegram;

use danog\MadelineProto\API;
use Illuminate\Support\Facades\Log;

class TelegramMessageService
{
    /**
     * Send message to a channel
     */
    public function sendMessageToChannel(API $client, int|string $channelId, string $message): array
    {
        try {
            // Validate channel ID
            if (! is_numeric($channelId)) {
                return [
                    'success' => false,
                    'error' => 'Invalid channel ID.',
                ];
            }

            // Validate message
            $message = trim($message);
            if (empty($message)) {
                return [
                    'success' => false,
                    'error' => 'Message cannot be empty.',
                ];
            }

            if (strlen($message) > 4096) {
                return [
                    'success' => false,
                    'error' => 'Message is too long. Maximum length is 4096 characters.',
                ];
            }

            // Send message
            $result = $client->messages->sendMessage([
                'peer' => (int) $channelId,
                'message' => $message,
            ]);

            return [
                'success' => true,
                'message' => 'Message sent successfully.',
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to send message to channel', [
                'channel_id' => $channelId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to send message: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Send message to a user
     */
    public function sendMessageToUser(API $client, string $username, string $message): array
    {
        try {
            // Sanitize and validate username
            $username = trim(ltrim($username, '@'));

            if (empty($username)) {
                return [
                    'success' => false,
                    'error' => 'Please provide a Telegram username.',
                ];
            }

            // Validate username format
            if (! preg_match('/^[a-zA-Z0-9_]{5,32}$/', $username)) {
                return [
                    'success' => false,
                    'error' => 'Invalid Telegram username format.',
                ];
            }

            // Validate message
            $message = trim($message);
            if (empty($message)) {
                return [
                    'success' => false,
                    'error' => 'Message cannot be empty.',
                ];
            }

            if (strlen($message) > 4096) {
                return [
                    'success' => false,
                    'error' => 'Message is too long. Maximum length is 4096 characters.',
                ];
            }

            // Send message
            $result = $client->messages->sendMessage([
                'peer' => '@'.$username,
                'message' => $message,
            ]);

            return [
                'success' => true,
                'message' => 'Message sent successfully.',
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to send message to user', [
                'username' => $username ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to send message: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Forward message to a channel or user
     */
    public function forwardMessage(API $client, int $fromPeer, int $messageId, int|string $toPeer): array
    {
        try {
            $result = $client->messages->forwardMessages([
                'from_peer' => $fromPeer,
                'id' => [$messageId],
                'to_peer' => $toPeer,
            ]);

            return [
                'success' => true,
                'message' => 'Message forwarded successfully.',
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to forward message', [
                'from_peer' => $fromPeer,
                'message_id' => $messageId,
                'to_peer' => $toPeer,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to forward message: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Edit message in a channel
     */
    public function editMessage(API $client, int|string $peer, int $messageId, string $newMessage): array
    {
        try {
            // Validate message
            $newMessage = trim($newMessage);
            if (empty($newMessage)) {
                return [
                    'success' => false,
                    'error' => 'Message cannot be empty.',
                ];
            }

            if (strlen($newMessage) > 4096) {
                return [
                    'success' => false,
                    'error' => 'Message is too long. Maximum length is 4096 characters.',
                ];
            }

            $result = $client->messages->editMessage([
                'peer' => $peer,
                'id' => $messageId,
                'message' => $newMessage,
            ]);

            return [
                'success' => true,
                'message' => 'Message edited successfully.',
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to edit message', [
                'peer' => $peer,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to edit message: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Delete messages from a channel
     */
    public function deleteMessages(API $client, int|string $peer, array $messageIds): array
    {
        try {
            $client->channels->deleteMessages([
                'channel' => $peer,
                'id' => $messageIds,
            ]);

            return [
                'success' => true,
                'message' => 'Messages deleted successfully.',
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to delete messages', [
                'peer' => $peer,
                'message_ids' => $messageIds,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to delete messages: '.$e->getMessage(),
            ];
        }
    }
}
