<?php

namespace App\Services\Telegram;

use danog\MadelineProto\API;
use Illuminate\Support\Facades\Log;

class TelegramChannelService
{
    public function __construct(
        protected TelegramBugsnagService $bugsnag
    ) {}

    /**
     * Get list of user's channels
     */
    public function getChannels(API $client): array
    {
        try {
            $this->bugsnag->leaveBreadcrumb('Fetching user channels from Telegram');

            $dialogs = $client->messages->getDialogs();
            $channels = [];

            foreach ($dialogs['chats'] ?? [] as $chat) {
                if (
                    isset($chat['_']) &&
                    ($chat['_'] === 'channel' || $chat['_'] === 'chat' || $chat['_'] === 'supergroup')
                ) {
                    $channels[] = [
                        'id' => $chat['id'] ?? null,
                        'title' => $chat['title'] ?? 'Unknown',
                        'username' => $chat['username'] ?? null,
                        'type' => $chat['_'],
                    ];
                }
            }

            $this->bugsnag->leaveBreadcrumb('Channels fetched successfully', ['count' => count($channels)]);

            return $channels;
        } catch (\Throwable $e) {
            Log::error('Failed to get channels', [
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyChannelError($e, null, null, [
                'operation' => 'get_channels',
            ]);

            return [];
        }
    }

    /**
     * Create a new Telegram channel
     */
    public function createChannel(API $client, string $title, string $description): array
    {
        try {
            // Sanitize inputs
            $title = trim(strip_tags($title));
            $description = trim(strip_tags($description));

            // Validate inputs
            if (empty($title) || strlen($title) > 128) {
                return [
                    'success' => false,
                    'error' => 'Channel title must be between 1 and 128 characters.',
                ];
            }

            if (empty($description) || strlen($description) > 255) {
                return [
                    'success' => false,
                    'error' => 'Channel description must be between 1 and 255 characters.',
                ];
            }

            $this->bugsnag->leaveBreadcrumb('Creating Telegram channel', ['title' => $title]);

            $result = $client->channels->createChannel([
                'broadcast' => true,
                'megagroup' => false,
                'title' => $title,
                'about' => $description,
            ]);

            $this->bugsnag->leaveBreadcrumb('Channel created successfully', ['title' => $title]);

            return [
                'success' => true,
                'message' => "Channel '{$title}' created successfully.",
                'channel' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to create channel', [
                'title' => $title ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyChannelError($e, null, null, [
                'operation' => 'create_channel',
                'title' => $title ?? 'unknown',
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create channel: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Delete a Telegram channel
     */
    public function deleteChannel(API $client, int|string $channelId): array
    {
        try {
            // Validate channel ID
            if (! is_numeric($channelId)) {
                return [
                    'success' => false,
                    'error' => 'Invalid channel ID.',
                ];
            }

            $this->bugsnag->leaveBreadcrumb('Deleting Telegram channel', ['channel_id' => $channelId]);

            $client->channels->deleteChannel(['channel' => (int) $channelId]);

            $this->bugsnag->leaveBreadcrumb('Channel deleted successfully', ['channel_id' => $channelId]);

            return [
                'success' => true,
                'message' => 'Channel deleted successfully.',
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to delete channel', [
                'channel_id' => $channelId,
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyChannelError($e, null, (string) $channelId, [
                'operation' => 'delete_channel',
            ]);

            return [
                'success' => false,
                'error' => 'Failed to delete channel: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Invite user to channel via direct message with invite link
     */
    public function inviteUserToChannel(API $client, int|string $channelId, string $username): array
    {
        try {
            // Validate channel ID
            if (! is_numeric($channelId)) {
                return [
                    'success' => false,
                    'error' => 'Invalid channel ID.',
                ];
            }

            // Sanitize and validate username
            $username = trim($username);

            if (empty($username)) {
                return [
                    'success' => false,
                    'error' => 'Please provide a Telegram username.',
                ];
            }

            // Remove @ symbol if present
            $username = ltrim($username, '@');

            // Validate username format (5-32 characters, alphanumeric + underscore)
            if (! preg_match('/^[a-zA-Z0-9_]{5,32}$/', $username)) {
                return [
                    'success' => false,
                    'error' => 'Invalid Telegram username format. Username must be 5-32 characters long and contain only letters, numbers, and underscores.',
                ];
            }

            // Generate invite link for the channel
            $inviteResult = $client->messages->exportChatInvite([
                'peer' => (int) $channelId,
            ]);

            $inviteLink = $inviteResult['link'] ?? null;

            if (! $inviteLink) {
                return [
                    'success' => false,
                    'error' => 'Failed to generate invite link for the channel.',
                ];
            }

            // Send direct message with invite link
            $message = "Hello! You've been invited to join a Telegram channel.\n\nClick the link below to join:\n{$inviteLink}";

            $client->messages->sendMessage([
                'peer' => '@'.$username,
                'message' => $message,
            ]);

            return [
                'success' => true,
                'message' => "Invitation link sent to @{$username} successfully.",
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to invite user to channel', [
                'channel_id' => $channelId,
                'username' => $username ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyChannelError($e, null, (string) $channelId, [
                'operation' => 'invite_user',
                'username' => $username ?? 'unknown',
            ]);

            return [
                'success' => false,
                'error' => 'Failed to send invitation: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get channel information
     */
    public function getChannelInfo(API $client, int|string $channelId): ?array
    {
        try {
            if (! is_numeric($channelId)) {
                return null;
            }

            $this->bugsnag->leaveBreadcrumb('Fetching channel info', ['channel_id' => $channelId]);

            $info = $client->getFullInfo(['id' => (int) $channelId]);

            return $info;
        } catch (\Throwable $e) {
            Log::error('Failed to get channel info', [
                'channel_id' => $channelId,
                'error' => $e->getMessage(),
            ]);

            $this->bugsnag->notifyChannelError($e, null, (string) $channelId, [
                'operation' => 'get_channel_info',
            ]);

            return null;
        }
    }
}
