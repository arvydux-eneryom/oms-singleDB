# BugSnag Integration with Telegram

This document describes the BugSnag error tracking integration for the Telegram integration feature.

## Overview

BugSnag has been integrated into all Telegram-related services to provide comprehensive error tracking and monitoring. This helps identify and debug issues with the Telegram integration in production.

## Installation

BugSnag is already installed via Composer:

```bash
composer require bugsnag/bugsnag-laravel
```

## Configuration

### Environment Variables

Add the following to your `.env` file:

```bash
# BugSnag Configuration
BUGSNAG_API_KEY=your_bugsnag_api_key_here

# Optional: Specify which environments should send errors to BugSnag (default: production,staging)
# BUGSNAG_NOTIFY_RELEASE_STAGES=production,staging

# Optional: Set your application version for tracking
# APP_VERSION=1.0.0
```

### Getting Your BugSnag API Key

1. Sign up for a free account at [bugsnag.com](https://www.bugsnag.com/)
2. Create a new project for your application
3. Copy the API key from your project settings
4. Add it to your `.env` file as `BUGSNAG_API_KEY`

## Features

### Automatic Error Tracking

All errors in the following Telegram services are automatically tracked:

- **TelegramAuthService**: QR code generation, phone login, verification, session termination
- **TelegramClientService**: Client initialization, authorization checks, user data retrieval, logout
- **TelegramChannelService**: Channel operations (get, create, delete, invite)
- **TelegramMessageService**: Message operations (send, forward, edit, delete)

### Contextual Metadata

Each error reported to BugSnag includes relevant context:

- **Session Information**: Session ID, status, path, user ID
- **Operation Details**: The specific operation that failed
- **Telegram-Specific Data**: Channel IDs, usernames, phone numbers (sanitized)
- **User Information**: Authenticated user details when available

### Breadcrumbs

The integration leaves breadcrumbs at key points in the Telegram flow:

- Session initialization
- Authentication attempts
- Channel operations
- Message operations
- Client state changes

These breadcrumbs help trace the sequence of events leading to an error.

### Error Types

Errors are categorized by type:

- `authentication`: Login and auth-related errors
- `client_initialization`: Client setup errors
- `channel_operation`: Channel management errors
- `message_operation`: Message handling errors
- `session_cleanup`: Session cleanup errors
- `qr_code_generation`: QR code generation errors

## Security

The following sensitive data is automatically filtered and will not be sent to BugSnag:

- Passwords
- API keys and hashes
- API IDs
- Session data
- Tokens
- Phone numbers (only first 5 digits + *** are logged)

This is configured in `config/bugsnag.php`:

```php
'filters' => ['password', 'password_confirmation', 'api_key', 'api_hash', 'api_id', 'session_data', 'token'],
```

## Usage

### Automatic Tracking

Errors are automatically tracked - no additional code needed. All Telegram service methods already include BugSnag integration.

### Manual Tracking (if needed)

If you need to manually track an error outside the service layer:

```php
use App\Services\Telegram\TelegramBugsnagService;

$bugsnag = new TelegramBugsnagService();

// Track a general error
$bugsnag->notifyError($exception, $session, [
    'custom_key' => 'custom_value'
]);

// Track specific error types
$bugsnag->notifyAuthError($exception, $session, 'qr_login', [...]);
$bugsnag->notifyChannelError($exception, $session, 'channel_123', [...]);
$bugsnag->notifyMessageError($exception, $session, [...]);

// Leave breadcrumbs
$bugsnag->leaveBreadcrumb('User action description', [
    'key' => 'value'
]);
```

## Testing

To test that BugSnag is working:

1. Set `BUGSNAG_API_KEY` in your `.env`
2. Set `BUGSNAG_NOTIFY_RELEASE_STAGES=local,production,staging` to enable notifications in local environment
3. Trigger a Telegram operation
4. Check your BugSnag dashboard for the error report

## Disabling in Development

By default, BugSnag only reports errors in `production` and `staging` environments. To disable it completely:

1. Remove or comment out `BUGSNAG_API_KEY` from `.env`
2. Or set `BUGSNAG_NOTIFY_RELEASE_STAGES=production` to only notify in production

## Service Architecture

```
TelegramBugsnagService
    ├── Used by: TelegramAuthService
    ├── Used by: TelegramClientService
    ├── Used by: TelegramChannelService
    └── Used by: TelegramMessageService
```

All Telegram services are injected with `TelegramBugsnagService` via dependency injection.

## Viewing Errors

1. Log in to your BugSnag dashboard
2. Navigate to your project
3. View errors grouped by:
   - Error type
   - Release stage (environment)
   - User affected
   - First/last seen
4. Click on an error to see:
   - Stack trace
   - Breadcrumbs timeline
   - Session and user metadata
   - Telegram-specific context

## Best Practices

1. **Monitor Regularly**: Check your BugSnag dashboard regularly for new errors
2. **Set Up Alerts**: Configure email/Slack alerts for critical errors
3. **Track Fixes**: Mark errors as resolved in BugSnag when fixed
4. **Use Releases**: Tag your deployments with version numbers for better tracking
5. **Review Breadcrumbs**: Use breadcrumbs to understand the user flow leading to errors

## Troubleshooting

### Errors not appearing in BugSnag

1. Verify `BUGSNAG_API_KEY` is set correctly
2. Check that your environment is in `BUGSNAG_NOTIFY_RELEASE_STAGES`
3. Ensure the error is actually occurring (check Laravel logs)
4. Verify network connectivity to BugSnag API

### Too many errors

1. Adjust `BUGSNAG_LOGGER_LEVEL` to only track errors (default: `error`)
2. Review filters in `config/bugsnag.php`
3. Set up error grouping rules in BugSnag dashboard

## Additional Resources

- [BugSnag Laravel Documentation](https://docs.bugsnag.com/platforms/php/laravel/)
- [BugSnag Dashboard](https://app.bugsnag.com/)
- Project Configuration: `/config/bugsnag.php`
