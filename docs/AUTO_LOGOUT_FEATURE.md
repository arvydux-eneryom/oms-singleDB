# Auto-Logout After Inactivity Feature

## Overview

The auto-logout feature automatically logs out users after a period of inactivity to enhance security. Users receive a warning before being logged out, giving them the option to extend their session.

## Features

- **Configurable Timeout**: Set custom inactivity timeout (default: 24 hours)
- **Warning Modal**: Shows a countdown timer 5 minutes before logout
- **Activity Tracking**: Monitors mouse, keyboard, scroll, touch, and click events
- **Debounced Events**: Prevents excessive tracking with 500ms debounce
- **Livewire Integration**: Resets timer on Livewire AJAX requests
- **Cross-Tab Synchronization**: Activity in one tab extends session in all tabs
- **Proper Error Handling**: Graceful fallback if logout fails
- **Modern UI**: Built with Alpine.js and Flux UI components

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
# Enable/disable auto-logout feature
AUTH_INACTIVITY_ENABLED=true

# Inactivity timeout in seconds (default: 86400 = 24 hours)
AUTH_INACTIVITY_TIMEOUT=86400

# Warning time in seconds before logout (default: 300 = 5 minutes)
AUTH_INACTIVITY_WARNING=300
```

### Configuration File

The feature is configured in `config/auth.php`:

```php
'inactivity' => [
    'enabled' => env('AUTH_INACTIVITY_ENABLED', true),
    'timeout' => env('AUTH_INACTIVITY_TIMEOUT', 86400), // 24 hours
    'warning' => env('AUTH_INACTIVITY_WARNING', 300),   // 5 minutes
],
```

## Architecture

### Components

#### 1. **Inactivity Tracker Component**
- **Location**: `resources/views/components/inactivity-tracker.blade.php`
- **Technology**: Alpine.js + Flux UI
- **Responsibilities**:
  - Track user activity
  - Manage timers
  - Show warning modal
  - Handle logout

#### 2. **Configuration**
- **Location**: `config/auth.php`
- **Purpose**: Centralized configuration for feature

#### 3. **Integration**
- **Location**: `resources/views/components/layouts/app/sidebar.blade.php`
- **Integration Point**: End of body tag

### How It Works

1. **Initialization**
   - Component loads when authenticated user visits any page
   - Sets up activity listeners and timers
   - Initializes cross-tab synchronization via localStorage

2. **Activity Tracking**
   - Monitors: `mousedown`, `keydown`, `scroll`, `touchstart`, `click`
   - Debounces events (500ms) to prevent excessive updates
   - Records timestamp in localStorage for cross-tab sync
   - Resets timer on Livewire AJAX requests

3. **Warning Phase** (5 minutes before logout)
   - Shows modal with countdown timer
   - User can click "Stay Logged In" to extend session
   - User can click "Log Out Now" for immediate logout
   - Countdown displays in MM:SS format

4. **Logout**
   - Sends POST request to `/logout` endpoint
   - Includes CSRF token for security
   - Clears all timers and localStorage
   - Redirects to login page
   - Graceful error handling if request fails

### Technical Details

#### Timer Management

```javascript
// Warning timer: Shows modal before timeout
warningTimer = setTimeout(() => {
    showWarningModal();
}, timeout - warning);

// Logout timer: Logs out user after timeout
timer = setTimeout(() => {
    logout();
}, timeout);
```

#### Cross-Tab Synchronization

```javascript
// Write activity to localStorage
localStorage.setItem('last_activity_timestamp', Date.now());

// Listen for changes in other tabs
window.addEventListener('storage', (e) => {
    if (e.key === 'last_activity_timestamp') {
        resetTimer(); // Activity in another tab resets this tab's timer
    }
});
```

#### Livewire Integration

```javascript
if (window.Livewire) {
    Livewire.hook('request', () => {
        recordActivity();
    });
}
```

## Usage Examples

###  Example 1: Default Configuration (24 Hours)

```env
AUTH_INACTIVITY_ENABLED=true
AUTH_INACTIVITY_TIMEOUT=86400
AUTH_INACTIVITY_WARNING=300
```

- User inactive for 23 hours 55 minutes → Warning modal appears
- User has 5 minutes to click "Stay Logged In" or get logged out

### Example 2: Shorter Timeout for Testing (1 Hour)

```env
AUTH_INACTIVITY_ENABLED=true
AUTH_INACTIVITY_TIMEOUT=3600
AUTH_INACTIVITY_WARNING=300
```

- User inactive for 55 minutes → Warning modal appears
- User has 5 minutes to extend session

### Example 3: Very Short Timeout for Development (5 Minutes)

```env
AUTH_INACTIVITY_ENABLED=true
AUTH_INACTIVITY_TIMEOUT=300
AUTH_INACTIVITY_WARNING=60
```

- User inactive for 4 minutes → Warning modal appears
- User has 1 minute to extend session

### Example 4: Disable Feature

```env
AUTH_INACTIVITY_ENABLED=false
```

- Feature completely disabled
- No tracking or auto-logout

## Testing

### Running Tests

```bash
# Run all inactivity tracker tests
./test tests/Feature/Auth/InactivityTrackerTest.php

# Run with testdox for readable output
./test tests/Feature/Auth/InactivityTrackerTest.php --testdox
```

### Test Coverage

The feature includes 25 comprehensive tests covering:

- ✅ Configuration loading
- ✅ Component rendering (enabled/disabled states)
- ✅ Guest vs authenticated user behavior
- ✅ Timeout values propagation
- ✅ CSRF token inclusion
- ✅ Logout route configuration
- ✅ Warning modal content
- ✅ Activity event listeners
- ✅ Cross-tab synchronization
- ✅ Livewire integration
- ✅ Debounced activity tracking
- ✅ Countdown timer functionality
- ✅ Session extension
- ✅ Error handling
- ✅ Logout endpoint functionality
- ✅ Fetch headers configuration
- ✅ Time formatting
- ✅ Timer cleanup
- ✅ Login redirect
- ✅ Alpine.js integration
- ✅ Flux modal API usage
- ✅ Layout integration

## User Experience

### Normal Flow

1. User logs in
2. User works normally - every interaction resets the timer
3. User inactive for (timeout - warning) seconds
4. Warning modal appears with countdown
5. User clicks "Stay Logged In"
6. Timer resets, user continues working

### Logout Flow

1. User logs in
2. User leaves computer/tab
3. After (timeout - warning) seconds, warning modal appears
4. User doesn't respond
5. After warning period expires, automatic logout
6. User redirected to login page

### Multi-Tab Behavior

1. User has app open in 3 tabs
2. User actively uses Tab 1
3. Timers in Tab 2 and Tab 3 automatically reset
4. All tabs stay logged in as long as one is active

## Security Considerations

### What It Protects Against

- ✅ Unauthorized access to unattended sessions
- ✅ Session hijacking from abandoned workstations
- ✅ Compliance violations from long-running sessions

### Implementation Security

- ✅ CSRF token validation on logout
- ✅ Server-side session invalidation
- ✅ Proper error handling
- ✅ Secure credential handling
- ✅ No sensitive data in localStorage

### What It Doesn't Protect Against

- ❌ Active session hijacking
- ❌ XSS attacks
- ❌ CSRF attacks (handled separately)
- ❌ Man-in-the-middle attacks

## Troubleshooting

### Issue: Users Getting Logged Out Too Quickly

**Solution**: Increase `AUTH_INACTIVITY_TIMEOUT` value

```env
# Increase from 24 hours to 48 hours
AUTH_INACTIVITY_TIMEOUT=172800
```

### Issue: Warning Modal Not Appearing

**Possible Causes**:
1. Feature disabled in config
2. JavaScript errors in console
3. Alpine.js not loaded

**Solutions**:
1. Check `AUTH_INACTIVITY_ENABLED=true`
2. Open browser console and check for errors
3. Verify Alpine.js is loaded: Check for `window.Alpine` in console

### Issue: Timer Not Resetting on Activity

**Possible Causes**:
1. JavaScript errors blocking event listeners
2. Debounce delay too long

**Solutions**:
1. Check browser console for errors
2. Verify event listeners are attached
3. Check network tab for failed requests

### Issue: Cross-Tab Sync Not Working

**Possible Causes**:
1. localStorage disabled
2. Browser privacy mode
3. Different domains/subdomains

**Solutions**:
1. Check if localStorage is available
2. Disable private/incognito mode
3. Ensure all tabs are on same domain

## Best Practices

### Recommended Timeouts

| Use Case | Timeout | Warning | Rationale |
|----------|---------|---------|-----------|
| **High Security** | 15 minutes | 2 minutes | Financial/medical apps |
| **Standard Office** | 1-2 hours | 5 minutes | Typical enterprise apps |
| **Low Security** | 8-24 hours | 5 minutes | Internal tools |
| **Development** | 5 minutes | 1 minute | Testing purposes |

### User Communication

**Do**:
- ✅ Inform users about the feature in documentation
- ✅ Set reasonable timeouts based on use case
- ✅ Provide clear warning before logout
- ✅ Allow users to extend their session easily

**Don't**:
- ❌ Set timeout too short (< 15 minutes)
- ❌ Hide the feature without documentation
- ❌ Make warning time too short (< 1 minute)
- ❌ Disable the "extend session" button

## Future Enhancements

### Potential Improvements

1. **Server-Side Validation**
   - Track last activity server-side
   - Validate timeout on each request
   - More secure than client-only

2. **User Preferences**
   - Allow users to set their own timeout
   - Remember preference per device
   - Respect user's workflow

3. **Activity Analytics**
   - Track inactivity patterns
   - Optimize timeout based on usage
   - Identify unusual activity

4. **Grace Period**
   - Allow brief reconnection after logout
   - Restore unsaved work
   - Better UX for edge cases

5. **Mobile Support**
   - Adjust behavior for mobile devices
   - Handle app backgrounding
   - Battery-efficient tracking

## Maintenance

### Regular Checks

- Review timeout settings quarterly
- Monitor user feedback on timeouts
- Check error logs for logout failures
- Update tests when adding features

### Monitoring

**Key Metrics**:
- Auto-logout frequency
- Warning modal interactions
- Session extension rate
- User complaints about timeouts

## Support

### Common Questions

**Q: Can I disable auto-logout for specific users?**
A: Not currently implemented. Consider adding role-based configuration.

**Q: Does this work with "Remember Me"?**
A: Yes, independent of remember token. Tracks session activity only.

**Q: What happens if user has multiple devices?**
A: Each device/browser has independent timer. Not synced across devices.

**Q: Can I customize the warning modal?**
A: Yes, edit `resources/views/components/inactivity-tracker.blade.php`.

## License

This feature is part of the OMS (Operation Management System) and follows the same license as the main application.

## Changelog

### Version 1.0.0 (2025-01-24)
- ✅ Initial implementation
- ✅ Alpine.js + Flux UI integration
- ✅ Cross-tab synchronization
- ✅ Livewire integration
- ✅ Comprehensive test coverage (25 tests)
- ✅ Complete documentation

## Credits

**Developed by**: Claude (Anthropic)
**Architecture**: Modern Alpine.js + Flux UI
**Testing**: PHPUnit Feature Tests
**Framework**: Laravel 11 + Livewire 3
