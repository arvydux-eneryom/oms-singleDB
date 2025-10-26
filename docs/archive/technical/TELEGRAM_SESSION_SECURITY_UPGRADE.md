# Telegram Session Security Upgrade

## Overview
This document describes the security improvements made to the Telegram integration session storage system to address critical vulnerabilities identified in the security review.

## Problem Statement
The original implementation had several critical security issues:

1. **Predictable Session Paths**: Sessions were stored using predictable user IDs (`user-{id}`)
2. **Weak File Permissions**: Directories created with 0755 permissions (world-readable)
3. **No Session Expiration**: Sessions persisted indefinitely with no cleanup
4. **No Ownership Validation**: No verification that sessions belong to authenticated users
5. **Direct env() Usage**: Configuration values read directly from env instead of config

## Solution Architecture

### 1. Database-Backed Session Management
Created a new `telegram_sessions` table to track session metadata:

```sql
- user_id: Foreign key to users table
- session_identifier: 64-character random string (unique)
- session_path: Full path to session directory
- last_activity_at: Timestamp of last use
- expires_at: Session expiration timestamp
- is_active: Boolean flag for active sessions
- ip_address: IP address of session creator
- user_agent: User agent of session creator
```

### 2. TelegramSession Model
**Location**: `app/Models/TelegramSession.php`

Features:
- Automatic session identifier generation using `Str::random(64)`
- Validation methods: `isExpired()`, `isValid()`
- Query scopes: `active()`, `expired()`, `forUser()`
- Automatic timestamp tracking

### 3. TelegramSessionRepository
**Location**: `app/Repositories/TelegramSessionRepository.php`

Provides centralized session management:
- `createSession()`: Creates new session with random identifier
- `getActiveSession()`: Retrieves active session for user
- `validateSessionOwnership()`: Verifies session belongs to user
- `deactivateSession()`: Deactivates session and cleans up files
- `cleanupExpiredSessions()`: Batch cleanup of expired sessions

### 4. Updated Livewire Component
**Location**: `app/Livewire/Integrations/Telegram/Index.php`

Key Changes:
- Uses `TelegramSessionRepository` for all session operations
- Validates session ownership before operations
- Uses `config()` instead of `env()` for configuration
- Improved error handling and logging
- Automatic session activity tracking

### 5. Configuration Management
**Location**: `config/services.php`

Added Telegram configuration section:
```php
'telegram' => [
    'api_id' => env('TELEGRAM_API_ID'),
    'api_hash' => env('TELEGRAM_API_HASH'),
    'session_dir' => env('TELEGRAM_SESSION_DIR', 'telegram/sessions'),
    'session_expires_days' => env('TELEGRAM_SESSION_EXPIRES_DAYS', 30),
],
```

### 6. Automated Session Cleanup
**Location**: `app/Jobs/CleanupExpiredTelegramSessions.php`

- Scheduled to run daily at 2:00 AM
- Automatically deactivates expired sessions
- Removes session files from storage
- Logs cleanup results

## Security Improvements

| Issue | Before | After |
|-------|--------|-------|
| Session Path | `storage/app/telegram/sessions/user-{id}` | `storage/app/telegram/sessions/{random-64-chars}` |
| Permissions | 0755 (world-readable) | 0700 (owner-only) |
| Expiration | Never | Configurable (default 30 days) |
| Ownership | No validation | Validated on every access |
| Cleanup | Manual | Automated daily |
| Config | env() calls | config() with caching support |

## Migration Guide

### Step 1: Run Migration
```bash
php artisan migrate
```

This creates the `telegram_sessions` table.

### Step 2: Clear Old Sessions (Optional)
```bash
# Backup existing sessions if needed
rm -rf storage/app/telegram/sessions/user-*
```

### Step 3: Clear Config Cache
```bash
php artisan config:clear
php artisan config:cache
```

### Step 4: Set Up Scheduler
Ensure Laravel's scheduler is running in production:

```bash
# Add to crontab:
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing

### Manual Testing
1. Log in to Telegram via the integration
2. Verify session is created in database: `SELECT * FROM telegram_sessions;`
3. Check session path uses random identifier
4. Verify file permissions: `ls -la storage/app/telegram/sessions/`
5. Log out and verify session is deactivated

### Cleanup Testing
```bash
# Run cleanup manually
php artisan queue:work --once
# Or dispatch directly
php artisan tinker
>>> dispatch(new \App\Jobs\CleanupExpiredTelegramSessions);
```

## Configuration Options

### Environment Variables
```env
TELEGRAM_API_ID=your_api_id
TELEGRAM_API_HASH=your_api_hash
TELEGRAM_SESSION_DIR=telegram/sessions
TELEGRAM_SESSION_EXPIRES_DAYS=30
```

### Adjusting Session Expiration
Change `TELEGRAM_SESSION_EXPIRES_DAYS` to your desired value:
- Development: 7 days
- Production: 30-90 days
- High-security: 1-7 days

## Monitoring

### Check Active Sessions
```sql
SELECT
    user_id,
    session_identifier,
    last_activity_at,
    expires_at,
    DATEDIFF(expires_at, NOW()) as days_until_expiration
FROM telegram_sessions
WHERE is_active = 1;
```

### Monitor Cleanup
```sql
SELECT DATE(created_at) as date, COUNT(*) as sessions_created
FROM telegram_sessions
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

## Rollback Plan

If issues occur, you can rollback:

1. Revert the migration:
```bash
php artisan migrate:rollback
```

2. Restore old code from git:
```bash
git checkout HEAD~1 -- app/Livewire/Integrations/Telegram/Index.php
```

3. Remove new files:
```bash
rm app/Models/TelegramSession.php
rm app/Repositories/TelegramSessionRepository.php
rm app/Jobs/CleanupExpiredTelegramSessions.php
```

## Future Improvements

1. **Multi-Device Support**: Allow multiple concurrent sessions per user
2. **Session Revocation**: Add UI for users to view/revoke active sessions
3. **Security Alerts**: Notify users of new session creations
4. **Geographic Tracking**: Store and display session locations
5. **Session Refresh**: Implement automatic session renewal
6. **Rate Limiting**: Add per-user session creation limits

## Troubleshooting

### Issue: "Session ownership validation failed"
**Cause**: Session belongs to different user or is expired
**Solution**: Log out and log in again

### Issue: Sessions not cleaning up
**Cause**: Scheduler not running
**Solution**: Verify cron job is active: `crontab -l`

### Issue: Permission denied when creating directory
**Cause**: PHP lacks write permissions to storage directory
**Solution**: `chmod -R 775 storage && chown -R www-data:www-data storage`

## Support

For issues or questions regarding this implementation, please:
1. Check logs: `storage/logs/laravel.log`
2. Review diagnostics: Session stats available in repository
3. Contact development team with session_id and timestamp

---

**Last Updated**: 2025-10-11
**Version**: 1.0.0
**Author**: Security Upgrade Initiative
