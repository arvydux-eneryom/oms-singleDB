# BugSnag Error Monitoring Guide for Telegram Integration

## Table of Contents
1. [Introduction](#introduction)
2. [What is BugSnag?](#what-is-bugsnag)
3. [Why Use BugSnag for Telegram?](#why-use-bugsnag-for-telegram)
4. [Getting Started](#getting-started)
5. [Setup Instructions](#setup-instructions)
6. [Understanding Error Reports](#understanding-error-reports)
7. [Common Error Scenarios](#common-error-scenarios)
8. [Using the BugSnag Dashboard](#using-the-bugsnag-dashboard)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)
11. [FAQ](#faq)

---

## Introduction

This guide explains how BugSnag error monitoring works with the Telegram integration in your OMS (Operation Management System). BugSnag helps you identify, track, and fix errors in production before they impact your users.

---

## What is BugSnag?

BugSnag is an error monitoring and reporting platform that:
- **Captures errors** automatically when they occur
- **Provides context** about what the user was doing
- **Shows stack traces** to help debug issues
- **Alerts your team** when critical errors happen
- **Tracks error trends** over time

Think of it as a security camera for your application - it records what goes wrong so you can fix it.

---

## Why Use BugSnag for Telegram?

The Telegram integration involves complex operations:
- Authenticating with Telegram servers
- Managing user sessions
- Creating and managing channels
- Sending and receiving messages
- Handling QR codes and phone verification

When something goes wrong (API timeouts, authentication failures, etc.), BugSnag helps you:
1. **Know immediately** when an error occurs
2. **Understand the context** - which user, what they were doing
3. **See the error details** - stack traces, error messages
4. **Track if it's recurring** - is it affecting multiple users?
5. **Verify the fix** - did the error stop after deployment?

---

## Getting Started

### Prerequisites
- OMS application installed and running
- Admin access to your application
- Internet connection (to connect to BugSnag)

### Quick Setup (5 minutes)

1. **Create a BugSnag Account**
   - Go to [bugsnag.com](https://www.bugsnag.com/)
   - Click "Start Free Trial" (no credit card required)
   - Sign up with your work email

2. **Create a Project**
   - After signing in, click "Create Project"
   - Name it: "OMS Telegram Integration"
   - Select platform: "PHP"
   - Click "Create Project"

3. **Get Your API Key**
   - You'll see a screen with your API key
   - Copy the API key (looks like: `a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6`)
   - Keep this safe - you'll need it in the next step

4. **Configure Your Application**
   - Open your `.env` file
   - Add this line:
   ```bash
   BUGSNAG_API_KEY=your_api_key_here
   ```
   - Replace `your_api_key_here` with the key you copied
   - Save the file

5. **Test the Integration**
   - Try using the Telegram integration
   - If any error occurs, it should appear in your BugSnag dashboard
   - Check: [app.bugsnag.com](https://app.bugsnag.com/)

---

## Setup Instructions

### Detailed Configuration

#### Environment Variables

Open your `.env` file and add:

```bash
# Required: Your BugSnag API Key
BUGSNAG_API_KEY=your_api_key_from_bugsnag_dashboard

# Optional: Control which environments send errors (default: production,staging)
BUGSNAG_NOTIFY_RELEASE_STAGES=production,staging

# Optional: Track your application version
APP_VERSION=1.0.0
```

#### Environment-Specific Setup

**For Production:**
```bash
APP_ENV=production
BUGSNAG_API_KEY=your_production_key
BUGSNAG_NOTIFY_RELEASE_STAGES=production
```

**For Staging:**
```bash
APP_ENV=staging
BUGSNAG_API_KEY=your_staging_key
BUGSNAG_NOTIFY_RELEASE_STAGES=staging
```

**For Local Development:**
```bash
APP_ENV=local
# Option 1: Don't set API key (errors won't be sent)
# BUGSNAG_API_KEY=

# Option 2: Send errors to test in BugSnag
BUGSNAG_API_KEY=your_test_key
BUGSNAG_NOTIFY_RELEASE_STAGES=local,production,staging
```

#### Verifying Setup

After configuration, test the integration:

1. Restart your application (if using Laravel Octane/Herd)
2. Try to log in to Telegram in your OMS
3. Check your BugSnag dashboard
4. You should see breadcrumb entries even if no errors occurred

---

## Understanding Error Reports

### What Gets Tracked?

Every error report includes:

#### 1. **Error Details**
- Error type (e.g., "API Timeout", "Authentication Failed")
- Error message
- Full stack trace
- File and line number where error occurred

#### 2. **Telegram Context**
- **Session Information**
  - Session ID
  - Session status (active, expired, etc.)
  - Last activity time

- **Operation Details**
  - What was being attempted (login, send message, create channel)
  - Authentication method used (QR code, phone)
  - Channel or user involved

- **Sanitized Data**
  - Phone numbers (only first 5 digits shown: +1234***)
  - Usernames
  - Channel IDs

#### 3. **User Context**
- User ID in your system
- User's email (if available)
- IP address
- Browser/device information

#### 4. **Breadcrumbs Timeline**
A sequence of events leading to the error, for example:
```
1. [10:30:00] Initializing Telegram client (session_id: 123)
2. [10:30:02] Starting QR code generation
3. [10:30:05] QR code generated successfully
4. [10:30:10] Initiating phone login (phone: +1234***)
5. [10:30:12] ERROR: Phone login failed - Flood wait error
```

### Security & Privacy

BugSnag **NEVER** receives:
- ❌ Full phone numbers (only first 5 digits)
- ❌ Passwords
- ❌ Telegram API keys or hashes
- ❌ Session data
- ❌ Authentication tokens

This is automatically filtered by the integration.

---

## Common Error Scenarios

### 1. QR Code Generation Failure

**What you'll see in BugSnag:**
- Error type: `qr_code_generation`
- Context: Session ID, user attempting login

**Common causes:**
- Telegram API temporarily unavailable
- Network connectivity issues
- Session already authorized

**How to fix:**
- Check Telegram API status
- Verify network connectivity
- User should refresh and try again

---

### 2. Phone Login Flood Wait

**What you'll see in BugSnag:**
- Error type: `authentication`
- Context: Phone number (sanitized), flood wait time
- Error message: "Too many requests. Please wait X seconds"

**Common causes:**
- User tried to login too many times
- Telegram rate limiting

**How to fix:**
- User must wait the specified time
- Explain rate limiting to users
- Consider implementing client-side rate limiting

---

### 3. Channel Creation Failure

**What you'll see in BugSnag:**
- Error type: `channel_operation`
- Operation: `create_channel`
- Context: Channel title

**Common causes:**
- Invalid channel name
- Telegram API limits reached
- User doesn't have permission

**How to fix:**
- Validate channel names before submission
- Check Telegram account limits
- Verify user has proper permissions

---

### 4. Message Send Failure

**What you'll see in BugSnag:**
- Error type: `message_operation`
- Operation: `send_message_to_channel` or `send_message_to_user`
- Context: Channel ID or username, message length

**Common causes:**
- Message too long (>4096 characters)
- Invalid recipient
- Spam detection by Telegram

**How to fix:**
- Validate message length client-side
- Verify recipient exists
- Implement rate limiting for messages

---

### 5. Session Initialization Error

**What you'll see in BugSnag:**
- Error type: `client_initialization`
- Context: Session ID, session path

**Common causes:**
- Disk space issues
- Permission problems with session directory
- Corrupted session files

**How to fix:**
- Check disk space on server
- Verify directory permissions (should be 0700)
- Clear corrupted session files

---

## Using the BugSnag Dashboard

### Accessing the Dashboard

1. Go to [app.bugsnag.com](https://app.bugsnag.com/)
2. Sign in with your account
3. Select your "OMS Telegram Integration" project

### Dashboard Overview

#### Main Screen

![Dashboard Overview]

You'll see:
- **Error count** - Total errors in selected time period
- **Affected users** - How many users experienced errors
- **Error groups** - Errors grouped by type
- **Trend graph** - Error frequency over time

#### Error List

Each error shows:
- 🔴 **Severity** (error, warning, info)
- 📝 **Error message**
- 👥 **Number of users affected**
- 🕐 **First seen / Last seen**
- 📊 **Occurrence count**

### Viewing Error Details

Click on any error to see:

1. **Overview Tab**
   - Error message and type
   - Affected users count
   - Occurrence timeline
   - Stack trace

2. **Sessions Tab**
   - List of all sessions where this error occurred
   - User information
   - Session metadata

3. **Breadcrumbs Tab**
   - Timeline of events before error
   - User actions
   - System events

4. **Metadata Tab**
   - Telegram-specific data
   - Session information
   - Operation details

### Setting Up Alerts

1. Click **Settings** → **Alerts**
2. Choose alert type:
   - **Email** - Get notified via email
   - **Slack** - Post to Slack channel
   - **Webhook** - Custom integration

3. Configure conditions:
   - Alert on: "New error", "Error spike", "Critical error"
   - Frequency: "Immediately", "Daily digest"

4. **Recommended Setup:**
   - Email alert for critical errors (immediately)
   - Slack alert for new error types (immediately)
   - Daily digest for all errors

### Marking Errors as Resolved

When you fix an error:

1. Deploy the fix to production
2. Go to the error in BugSnag
3. Click **"Mark as Resolved"**
4. Add a comment explaining the fix

Benefits:
- If error occurs again, BugSnag will notify you (regression)
- Track which errors are fixed vs. ongoing
- Show progress to your team

---

## Best Practices

### 1. Regular Monitoring

**Daily** (5 minutes):
- Check for new critical errors
- Review errors affecting multiple users
- Mark resolved errors

**Weekly** (15 minutes):
- Review error trends
- Identify recurring issues
- Update alert settings if needed

**Monthly** (30 minutes):
- Analyze error patterns
- Review and clean up resolved errors
- Update documentation for common issues

### 2. Alert Configuration

Set up progressive alerts:

**Tier 1 - Critical** (Immediate Slack/Email):
- Authentication failures affecting >10 users
- Message send failures affecting >50 users
- Any database-related errors

**Tier 2 - Important** (Email within 1 hour):
- New error types
- Errors affecting 5-10 users
- Channel operation failures

**Tier 3 - Monitoring** (Daily digest):
- All other errors
- Warning-level issues
- Performance issues

### 3. Team Workflow

1. **On-Call Developer**
   - Receives critical alerts
   - Triages errors within 1 hour
   - Creates tickets for non-urgent issues

2. **Weekly Review**
   - Team reviews all unresolved errors
   - Prioritizes fixes
   - Assigns owners

3. **Post-Deployment**
   - Monitor BugSnag for 1 hour after deploy
   - Verify fixed errors stay resolved
   - Check for new errors introduced

### 4. Version Tracking

Tag your releases in BugSnag:

```bash
# In your .env
APP_VERSION=2.1.0
```

Benefits:
- See which version introduced an error
- Track if errors are fixed in new releases
- Compare error rates between versions

### 5. Custom Metadata

Enhance error reports with custom data:

```php
// In your controllers or Livewire components
$bugsnag->leaveBreadcrumb('User viewed Telegram dashboard', [
    'user_id' => Auth::id(),
    'has_active_session' => $hasSession,
    'channels_count' => $channelsCount,
]);
```

---

## Troubleshooting

### Errors Not Appearing in BugSnag

**Check 1: API Key**
```bash
# In your .env file
BUGSNAG_API_KEY=your_key_here  # Must be present and correct
```

**Check 2: Environment**
```bash
# Check if your environment is allowed to send errors
APP_ENV=production  # Should match BUGSNAG_NOTIFY_RELEASE_STAGES

# Default only notifies in production and staging
BUGSNAG_NOTIFY_RELEASE_STAGES=production,staging
```

**Check 3: Test the Connection**
```bash
# Run this command to test
php artisan tinker
>>> Bugsnag::notifyException(new \Exception('Test error'));
```

**Check 4: Clear Cache**
```bash
php artisan config:clear
php artisan cache:clear
```

---

### Too Many Errors Being Sent

**Solution 1: Adjust Severity Level**

Edit `config/bugsnag.php`:
```php
// Only send errors, not warnings or info
'logger_level' => env('BUGSNAG_LOGGER_LEVEL', 'error'),
```

**Solution 2: Add Filters**

Edit `config/bugsnag.php`:
```php
'filters' => [
    'password',
    'api_key',
    // Add more fields to filter
    'custom_sensitive_field',
],
```

**Solution 3: Use Error Sampling**

For high-volume errors, implement sampling:
```php
// Only report 10% of this specific error
if (rand(1, 10) === 1) {
    $bugsnag->notifyError($exception);
}
```

---

### Duplicate Error Reports

**Cause:** Same error reported multiple times

**Solution:** BugSnag groups errors automatically, but you can improve grouping:

1. **Use Consistent Error Messages**
   ```php
   // ❌ Bad: Dynamic error messages
   throw new Exception("Error for user $userId");

   // ✅ Good: Consistent message with metadata
   $exception = new Exception("User operation failed");
   $bugsnag->notifyError($exception, null, ['user_id' => $userId]);
   ```

2. **Set Error Fingerprints**
   ```php
   $bugsnag->notifyError($exception, null, [
       'grouping_hash' => 'telegram_auth_failure',
   ]);
   ```

---

### Privacy Concerns

**Q: What if sensitive data gets sent?**

**A:** The integration automatically filters:
- Passwords
- API keys and hashes
- Session data
- Full phone numbers
- Tokens

**Additional Protection:**

Edit `config/bugsnag.php` to add more filters:
```php
'filters' => [
    'password',
    'password_confirmation',
    'api_key',
    'api_hash',
    'api_id',
    'session_data',
    'token',
    // Add your own
    'credit_card',
    'ssn',
    'bank_account',
],
```

---

## FAQ

### General Questions

**Q: Does BugSnag slow down my application?**
A: No. Error reporting is asynchronous and adds <1ms overhead per request.

**Q: How much does BugSnag cost?**
A: Free tier: 7,500 events/month. Paid plans start at $49/month for more events and features.

**Q: Can I use BugSnag in development?**
A: Yes, but it's optional. Add your local environment to `BUGSNAG_NOTIFY_RELEASE_STAGES` to enable.

**Q: What happens if BugSnag is down?**
A: Your application continues working normally. Errors are logged locally and not sent to BugSnag.

---

### Technical Questions

**Q: Does BugSnag work with Laravel Octane?**
A: Yes, the integration is fully compatible with Laravel Octane and Herd.

**Q: Can I disable BugSnag temporarily?**
A: Yes, comment out or remove `BUGSNAG_API_KEY` from `.env`.

**Q: How long are errors stored?**
A: Free tier: 30 days. Paid plans: up to 1 year.

**Q: Can I export error data?**
A: Yes, BugSnag provides data export via API and dashboard.

---

### Telegram-Specific Questions

**Q: Why am I seeing many "Flood Wait" errors?**
A: Telegram has rate limits. Users trying to login too frequently trigger this. Consider:
- Adding client-side rate limiting
- Showing wait time to users
- Implementing exponential backoff

**Q: What does "Session initialization failed" mean?**
A: Usually permissions or disk space issues. Check:
- Server disk space
- Directory permissions (0700 for session directory)
- MadelineProto library version

**Q: Can I track successful operations too?**
A: Yes, use breadcrumbs to track successful operations:
```php
$bugsnag->leaveBreadcrumb('Channel created successfully', [
    'channel_id' => $channelId,
    'channel_title' => $title,
]);
```

---

## Additional Resources

### Documentation
- [BugSnag PHP Documentation](https://docs.bugsnag.com/platforms/php/)
- [BugSnag Laravel Documentation](https://docs.bugsnag.com/platforms/php/laravel/)
- [BugSnag Dashboard Guide](https://docs.bugsnag.com/product/)

### Support
- **BugSnag Support:** [support@bugsnag.com](mailto:support@bugsnag.com)
- **Documentation:** `/docs/BUGSNAG_INTEGRATION.md` (Technical reference)
- **Configuration:** `/config/bugsnag.php`

### Integration Files
- Service: `app/Services/Telegram/TelegramBugsnagService.php`
- Config: `config/bugsnag.php`
- Environment: `.env`

---

## Quick Reference

### Essential Commands

```bash
# Test BugSnag connection
php artisan tinker
>>> Bugsnag::notifyException(new \Exception('Test'));

# Clear configuration cache
php artisan config:clear

# View current configuration
php artisan config:show bugsnag
```

### Key Environment Variables

```bash
# Required
BUGSNAG_API_KEY=your_key_here

# Optional but recommended
BUGSNAG_NOTIFY_RELEASE_STAGES=production,staging
APP_VERSION=1.0.0

# For local testing only
BUGSNAG_NOTIFY_RELEASE_STAGES=local,production,staging
```

### Important URLs

- **Dashboard:** [app.bugsnag.com](https://app.bugsnag.com/)
- **Documentation:** [docs.bugsnag.com](https://docs.bugsnag.com/)
- **Status:** [status.bugsnag.com](https://status.bugsnag.com/)

---

## Changelog

### Version 1.0 (Current)
- Initial integration with Telegram services
- Automatic error tracking for all Telegram operations
- Security filters for sensitive data
- Breadcrumb tracking for user actions
- Comprehensive error categorization

---

**Need Help?**

If you encounter issues not covered in this guide:
1. Check the technical documentation: `BUGSNAG_INTEGRATION.md`
2. Review BugSnag's official documentation
3. Contact your development team
4. Email BugSnag support for platform issues

---

*Last Updated: 2025-10-25*
*Document Version: 1.0*
*For: OMS Telegram Integration*
