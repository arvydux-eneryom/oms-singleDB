<?php

namespace App\Services\Telegram;

use danog\MadelineProto\API;
use Illuminate\Support\Facades\Log;

class TelegramMessageService
{
    public function __construct(
        protected TelegramBugsnagService $bugsnag
    ) {}

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
            $this->bugsnag->leaveBreadcrumb('Sending message to channel', [
                'channel_id' => $channelId,
                'message_length' => strlen($message),
            ]);

            $result = $client->messages->sendMessage([
                'peer' => (int) $channelId,
                'message' => $message,
            ]);

            $this->bugsnag->leaveBreadcrumb('Message sent to channel successfully', [
                'channel_id' => $channelId,
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

            $this->bugsnag->notifyMessageError($e, null, [
                'operation' => 'send_message_to_channel',
                'channel_id' => $channelId,
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
            $this->bugsnag->leaveBreadcrumb('Sending message to user', [
                'username' => $username,
                'message_length' => strlen($message),
            ]);

            $result = $client->messages->sendMessage([
                'peer' => '@'.$username,
                'message' => $message,
            ]);

            $this->bugsnag->leaveBreadcrumb('Message sent to user successfully', [
                'username' => $username,
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

            $this->bugsnag->notifyMessageError($e, null, [
                'operation' => 'send_message_to_user',
                'username' => $username ?? 'unknown',
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
            $this->bugsnag->leaveBreadcrumb('Forwarding message', [
                'from_peer' => $fromPeer,
                'message_id' => $messageId,
                'to_peer' => $toPeer,
            ]);

            $result = $client->messages->forwardMessages([
                'from_peer' => $fromPeer,
                'id' => [$messageId],
                'to_peer' => $toPeer,
            ]);

            $this->bugsnag->leaveBreadcrumb('Message forwarded successfully');

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

            $this->bugsnag->notifyMessageError($e, null, [
                'operation' => 'forward_message',
                'from_peer' => $fromPeer,
                'message_id' => $messageId,
                'to_peer' => $toPeer,
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

            $this->bugsnag->leaveBreadcrumb('Editing message', [
                'peer' => $peer,
                'message_id' => $messageId,
            ]);

            $result = $client->messages->editMessage([
                'peer' => $peer,
                'id' => $messageId,
                'message' => $newMessage,
            ]);

            $this->bugsnag->leaveBreadcrumb('Message edited successfully');

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

            $this->bugsnag->notifyMessageError($e, null, [
                'operation' => 'edit_message',
                'peer' => $peer,
                'message_id' => $messageId,
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
            $this->bugsnag->leaveBreadcrumb('Deleting messages', [
                'peer' => $peer,
                'count' => count($messageIds),
            ]);

            $client->channels->deleteMessages([
                'channel' => $peer,
                'id' => $messageIds,
            ]);

            $this->bugsnag->leaveBreadcrumb('Messages deleted successfully');

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

            $this->bugsnag->notifyMessageError($e, null, [
                'operation' => 'delete_messages',
                'peer' => $peer,
                'message_ids_count' => count($messageIds),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to delete messages: '.$e->getMessage(),
            ];
        }
    }
}
