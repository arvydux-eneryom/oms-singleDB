# Telegram QR Code Login - Troubleshooting Guide

## Issue Fixed
QR code login was not working due to locked MadelineProto session files.

## What Was Done

### 1. Removed Lock Files
MadelineProto creates lock files to prevent concurrent access. When sessions don't close properly, these locks persist and block new logins.

**Solution**: Created `telegram:cleanup` command to remove stuck lock files.

### 2. Improved Security (Minimal Changes)
- Changed directory permissions from `0755` to `0700` (owner-only access)
- Updated to use `config()` instead of direct `env()` calls
- Added better error logging

### 3. Added Cleanup Command
**Command**: `php artisan telegram:cleanup`

This command:
- Removes all `*.lock` files from session directories
- Allows users to log in again
- Can be run with `--force` flag to skip confirmation

## How to Use

### If QR Code Login Stops Working

1. **Run the cleanup command**:
   ```bash
   php artisan telegram:cleanup --force
   ```

2. **Refresh the page** in your browser

3. **Try scanning the QR code again**

### Manual Cleanup (Alternative)
If the command doesn't work, manually remove lock files:

```bash
find storage/app/telegram/sessions -name "*.lock" -delete
```

## Common Issues

### Issue: "Could not connect to MadelineProto"
**Cause**: IPC server couldn't start or proc_open is disabled
**Solution**:
1. Verify `proc_open` is enabled: `php -r "echo (function_exists('proc_open') ? 'OK' : 'DISABLED');"`
2. Check `open_basedir` restrictions in `php.ini`
3. Ensure CLI and web PHP versions match

### Issue: "Session is busy"
**Cause**: Another process is using the session
**Solution**:
1. Run `php artisan telegram:cleanup --force`
2. Wait 30 seconds
3. Try again

### Issue: QR code doesn't appear
**Cause**: Session already exists and is logged in, or initialization failed
**Solution**:
1. Check browser console for errors
2. Check `storage/logs/laravel.log` for errors
3. Try logging out first, then log in again

### Issue: QR code appears but scanning doesn't work
**Cause**: QR code expired (they expire after ~30 seconds)
**Solution**:
- The page auto-refreshes every 3 seconds
- Wait for a new QR code to appear
- Scan quickly

## Prevention

### Avoid Lock File Issues
1. **Always log out properly** - Use the "Log out" button instead of closing the browser
2. **Don't run multiple instances** - Telegram doesn't support multiple sessions with the same credentials
3. **Run cleanup periodically**:
   ```bash
   # Add to cron (daily at 3 AM)
   0 3 * * * cd /path/to/project && php artisan telegram:cleanup --force
   ```

### For Development
When developing, if you frequently stop/restart the server:
```bash
# Before starting dev server
php artisan telegram:cleanup --force

# Start your dev server
php artisan serve
```

## Session Management

### View Current Sessions
```bash
ls -la storage/app/telegram/sessions/
```

### Delete a Specific User's Session
```bash
# Replace X with the user ID
rm -rf storage/app/telegram/sessions/user-X
```

### Delete All Sessions (Nuclear Option)
```bash
rm -rf storage/app/telegram/sessions/*
php artisan db:table telegram_sessions --truncate  # if using database
```

**Warning**: This will log out all users!

## Technical Details

### MadelineProto Lock Files
MadelineProto uses three lock files per session:
- `ipcState.php.lock` - IPC server state
- `lightState.php.lock` - Light client state
- `safe.php.lock` - Safe serialization lock

These prevent concurrent access but can get stuck if:
- PHP process crashes
- Server is forcefully killed
- Network interruption during save
- Multiple processes try to access the same session

### Session Directory Structure
```
storage/app/telegram/sessions/
├── user-2/              # Old format (predictable)
│   ├── session.madeline
│   ├── ipcState.php
│   └── *.lock files
└── mKLF.../             # New format (random - more secure)
    ├── session.madeline
    └── ...
```

## Monitoring

### Check for Stuck Sessions
```bash
# Find sessions with lock files older than 1 hour
find storage/app/telegram/sessions -name "*.lock" -mmin +60
```

### Check MadelineProto Logs
```bash
tail -f public/MadelineProto.log
```

## Getting Help

If issues persist:
1. Check `storage/logs/laravel.log`
2. Check `public/MadelineProto.log`
3. Verify Telegram API credentials in `.env`
4. Ensure user has proper permissions on `storage/` directory
5. Try creating a fresh session in a different browser/incognito mode

## Changes Made to Code

### File: `app/Livewire/Integrations/Telegram/Index.php`
- Line 390: Changed permissions to `0700` (was `0755`)
- Line 376-377: Use `config()` with `env()` fallback
- Line 401-403: Improved error logging

### New File: `app/Console/Commands/CleanupTelegramSessions.php`
- Added `telegram:cleanup` command for easy lock file removal

### File: `.gitignore`
- Added `MadelineProto.log` to prevent log file commits

---

**Last Updated**: 2025-10-11
**Status**: ✅ QR Code Login Working
