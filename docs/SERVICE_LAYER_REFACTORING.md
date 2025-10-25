# Service Layer Refactoring - Telegram Integration

## Overview
This document describes the service layer architecture implemented for the Telegram integration, moving business logic out of the Livewire component into dedicated service classes.

## Problem Statement
The original `Index.php` Livewire component contained 599 lines of code with tightly coupled business logic:
- MadelineProto client initialization
- Authentication logic (QR code, phone login)
- Channel management operations
- Message sending functionality
- Session management

This made the code:
- **Hard to test** - Business logic mixed with UI concerns
- **Difficult to maintain** - Single responsibility principle violated
- **Not reusable** - Logic couldn't be used outside Livewire context
- **Hard to understand** - Too many responsibilities in one class

## Solution Architecture

### Service Layer Pattern
We've implemented a service layer that separates concerns into specialized services:

```
app/Services/Telegram/
├── TelegramClientService.php      # MadelineProto client management
├── TelegramAuthService.php        # Authentication logic
├── TelegramChannelService.php     # Channel operations
└── TelegramMessageService.php     # Messaging operations
```

### 1. TelegramClientService
**Responsibility**: MadelineProto API client lifecycle management

**Key Methods**:
- `initializeClient(TelegramSession $session): ?API`
  - Initializes MadelineProto client with session
  - Sets up secure directories (0700 permissions)
  - Handles configuration from config/services.php

- `isAuthorized(?API $client): bool`
  - Checks if client is logged into Telegram
  - Returns true only if authorization === API::LOGGED_IN

- `getLoggedUserData(?API $client): ?array`
  - Retrieves logged-in user information from Telegram
  - Returns user profile data (name, username, phone)

- `safeLogout(?API $client, TelegramSession $session): bool`
  - Logs out from Telegram server
  - Deactivates session in database
  - Cleans up session files and lock files
  - Handles errors gracefully

- `cleanupSessionDirectory(string $path): void`
  - Removes MadelineProto lock files
  - Deletes session files
  - Removes session directory

**Benefits**:
- Centralized client management
- Consistent error handling
- Reusable across different contexts
- Testable without Livewire

### 2. TelegramAuthService
**Responsibility**: User authentication and session management

**Key Methods**:
- `getOrCreateSession(int $userId, ?string $ip, ?string $userAgent): TelegramSession`
  - Gets existing active session or creates new one
  - Updates last activity timestamp
  - Returns TelegramSession model

- `generateQrCode(API $client): ?array`
  - Generates QR code login URL
  - Returns ['url' => '...', 'expires' => timestamp]
  - Handles MadelineProto qrLogin() response

- `initiatePhoneLogin(API $client, string $phone): array`
  - Validates phone number (E.164 format)
  - Initiates phone-based login
  - Returns structured response:
    ```php
    [
        'success' => bool,
        'logged_in' => bool,     // Already logged in
        'code_required' => bool,  // Code needed
        'error' => string,
        'message' => string,
        'wait_time' => int        // For flood errors
    ]
    ```

- `completePhoneLogin(API $client, string $code): array`
  - Validates code format (5 digits)
  - Completes phone login with verification code
  - Returns success/error response

- `terminateSession(TelegramSession $session, ?API $client): array`
  - Logs out from Telegram
  - Deactivates session in database
  - Cleans up session files
  - Returns success/error response

- `hasActiveSession(int $userId): bool`
  - Checks if user has valid active session
  - Quick boolean check without initialization

- `validateSessionOwnership(TelegramSession $session, int $userId): bool`
  - Security check: ensures session belongs to user
  - Prevents session hijacking

**Benefits**:
- Centralized authentication logic
- Consistent validation
- Structured error responses
- Easy to add new auth methods (2FA, etc.)

### 3. TelegramChannelService
**Responsibility**: Channel management operations

**Key Methods**:
- `getChannels(API $client): array`
  - Retrieves user's channels from Telegram
  - Filters dialogs to show only channels
  - Returns array of channel data

- `createChannel(API $client, string $title, string $description): array`
  - Validates and sanitizes inputs
  - Title: 1-128 characters
  - Description: 1-255 characters
  - Creates broadcast channel
  - Returns success/error response

- `deleteChannel(API $client, int|string $channelId): array`
  - Validates channel ID (numeric)
  - Deletes channel from Telegram
  - Returns success/error response

- `inviteUserToChannel(API $client, int|string $channelId, string $username): array`
  - Validates channel ID
  - Sanitizes username (removes @, trims whitespace)
  - Validates username format (5-32 chars, alphanumeric + underscore)
  - Sends invite to channel
  - Returns success/error response

- `getChannelInfo(API $client, int|string $channelId): ?array`
  - Retrieves detailed channel information
  - Returns channel metadata

**Benefits**:
- All channel operations in one place
- Consistent validation rules
- Input sanitization for security
- Easy to extend with new operations

### 4. TelegramMessageService
**Responsibility**: Message sending and management

**Key Methods**:
- `sendMessageToChannel(API $client, int|string $channelId, string $message): array`
  - Validates channel ID (numeric)
  - Validates message (not empty, max 4096 chars)
  - Sends message to channel
  - Returns success/error response

- `sendMessageToUser(API $client, string $username, string $message): array`
  - Validates username format
  - Validates message content
  - Sends direct message to user
  - Returns success/error response

- `forwardMessage(API $client, int $fromPeer, int $messageId, int|string $toPeer): array`
  - Forwards message from one chat to another
  - Returns success/error response

- `editMessage(API $client, int|string $peer, int $messageId, string $newMessage): array`
  - Validates new message content
  - Edits existing message
  - Returns success/error response

- `deleteMessages(API $client, int|string $peer, array $messageIds): array`
  - Deletes multiple messages from channel
  - Returns success/error response

**Benefits**:
- Message operations separated from UI
- Consistent message validation
- Reusable for API endpoints
- Easy to add new message features (media, polls, etc.)

## Refactored Livewire Component

### Before vs After Comparison

**Before** (`Index.php` - 599 lines):
```php
class Index extends Component
{
    // 20+ public/protected properties
    // 25+ methods mixing UI and business logic
    // Tightly coupled to MadelineProto
    // Hard to test
    // Hard to understand
}
```

**After** (`IndexRefactored.php` - ~340 lines):
```php
class IndexRefactored extends Component
{
    // Services (injected)
    protected TelegramAuthService $authService;
    protected TelegramChannelService $channelService;
    protected TelegramMessageService $messageService;
    protected TelegramClientService $clientService;

    // UI state only
    public int $telegramAuthState = 0;
    public array $channels = [];
    public string $phone = '';
    public int $loginCode = 0;

    // Delegating to services
    public function sendPhoneNumber()
    {
        $result = $this->authService->initiatePhoneLogin($this->client, $this->phone);

        if ($result['success']) {
            session()->flash('success', $result['message']);
        } else {
            session()->flash('error', $result['error']);
        }
    }
}
```

### Key Improvements

1. **Separation of Concerns**
   - Component focuses on UI state and user interaction
   - Services handle business logic and external API calls
   - Clear responsibility boundaries

2. **Testability**
   - Services can be unit tested independently
   - Component tests can mock services
   - No need to mock MadelineProto in component tests

3. **Reusability**
   - Services can be used in:
     - Artisan commands
     - Queue jobs
     - API controllers
     - Other Livewire components

4. **Maintainability**
   - Each service has single responsibility
   - Changes to Telegram API only affect relevant service
   - Easy to locate and fix bugs

5. **Consistency**
   - All services return structured response arrays
   - Consistent error handling
   - Consistent logging

## Migration Guide

### Step 1: Test Current Implementation
```bash
php artisan test --filter=Telegram
```

Ensure all tests pass before refactoring.

### Step 2: Replace Component
```bash
# Backup original
cp app/Livewire/Integrations/Telegram/Index.php app/Livewire/Integrations/Telegram/Index.php.backup

# Use refactored version
cp app/Livewire/Integrations/Telegram/IndexRefactored.php app/Livewire/Integrations/Telegram/Index.php
```

### Step 3: Test Refactored Implementation
```bash
php artisan test --filter=Telegram
```

All tests should still pass.

### Step 4: Manual Testing
1. Test QR code login
2. Test phone number login
3. Test channel creation
4. Test channel deletion
5. Test sending messages
6. Test inviting users
7. Test logout

### Step 5: Update Routes (if needed)
Routes should work without changes, but verify:
```php
Route::get('/integrations/telegram', Index::class)
    ->middleware(['auth'])
    ->name('integrations.telegram.index');
```

## Benefits of Service Layer

### 1. Single Responsibility Principle
Each service has one job:
- TelegramClientService: Client lifecycle
- TelegramAuthService: Authentication
- TelegramChannelService: Channel operations
- TelegramMessageService: Messaging

### 2. Open/Closed Principle
Services are open for extension, closed for modification:
- Add new authentication methods without changing existing code
- Add new message types without affecting channel operations

### 3. Dependency Inversion
Component depends on abstractions (services), not concrete implementations:
```php
// Easy to swap implementations
class Index extends Component
{
    protected TelegramAuthService $authService; // Could be interface
}
```

### 4. Testability
```php
// Unit test for service
public function test_phone_login_validates_format()
{
    $service = new TelegramAuthService(...);
    $result = $service->initiatePhoneLogin($mockClient, '123'); // Invalid

    $this->assertFalse($result['success']);
    $this->assertStringContainsString('E.164', $result['error']);
}

// Component test with mock service
public function test_send_phone_number()
{
    $mockAuthService = Mockery::mock(TelegramAuthService::class);
    $mockAuthService->shouldReceive('initiatePhoneLogin')
        ->andReturn(['success' => true, 'message' => 'Code sent']);

    Livewire::test(Index::class)
        ->set('phone', '+1234567890')
        ->call('sendPhoneNumber')
        ->assertSessionHas('success');
}
```

### 5. Reusability
Services can be used anywhere:

```php
// In Artisan command
class SendTelegramAnnouncement extends Command
{
    public function handle(TelegramMessageService $messageService)
    {
        $messageService->sendMessageToChannel($client, 123, 'Announcement');
    }
}

// In API controller
class TelegramApiController extends Controller
{
    public function __construct(
        protected TelegramChannelService $channelService
    ) {}

    public function listChannels(Request $request)
    {
        $channels = $this->channelService->getChannels($client);
        return response()->json($channels);
    }
}

// In Queue job
class SendBulkMessages implements ShouldQueue
{
    public function handle(TelegramMessageService $messageService)
    {
        foreach ($this->messages as $message) {
            $messageService->sendMessageToChannel(...);
        }
    }
}
```

## Testing Strategy

### Unit Tests for Services
Each service should have comprehensive unit tests:

```php
// tests/Unit/Services/TelegramAuthServiceTest.php
class TelegramAuthServiceTest extends TestCase
{
    public function test_initiate_phone_login_validates_format()
    public function test_initiate_phone_login_handles_flood_wait()
    public function test_complete_phone_login_validates_code()
    public function test_terminate_session_cleans_up_files()
}

// tests/Unit/Services/TelegramChannelServiceTest.php
class TelegramChannelServiceTest extends TestCase
{
    public function test_create_channel_sanitizes_inputs()
    public function test_delete_channel_validates_id()
    public function test_invite_user_validates_username_format()
}
```

### Integration Tests for Livewire Component
Test that component correctly uses services:

```php
// tests/Feature/TelegramIntegrationTest.php (updated)
class TelegramIntegrationTest extends TestCase
{
    public function test_phone_login_flow_works_end_to_end()
    public function test_channel_creation_works_end_to_end()
    public function test_logout_cleans_up_properly()
}
```

## Performance Considerations

### Service Instantiation
Services are instantiated in `boot()` method:
```php
public function boot(): void
{
    $this->sessionRepository = new TelegramSessionRepository();
    $this->clientService = new TelegramClientService($this->sessionRepository);
    $this->authService = new TelegramAuthService($this->sessionRepository, $this->clientService);
    // ...
}
```

**Optimization**: Use Laravel's service container:
```php
// In AppServiceProvider
public function register()
{
    $this->app->singleton(TelegramClientService::class);
    $this->app->singleton(TelegramAuthService::class);
    // ...
}

// In component
public function __construct(
    protected TelegramAuthService $authService,
    protected TelegramChannelService $channelService
) {}
```

### Caching
Services can implement caching for expensive operations:
```php
public function getChannels(API $client): array
{
    return Cache::remember('telegram.channels.' . auth()->id(), 300, function() use ($client) {
        return $this->fetchChannelsFromTelegram($client);
    });
}
```

## Future Enhancements

### 1. Service Interfaces
Define interfaces for services to enable swapping implementations:
```php
interface TelegramAuthInterface
{
    public function initiatePhoneLogin(API $client, string $phone): array;
    public function completePhoneLogin(API $client, string $code): array;
}

class TelegramAuthService implements TelegramAuthInterface
{
    // Implementation
}

// Easy to create mock implementations for testing
class MockTelegramAuthService implements TelegramAuthInterface
{
    // Mock implementation
}
```

### 2. Event Dispatching
Services can dispatch events for logging/monitoring:
```php
class TelegramAuthService
{
    public function terminateSession(...): array
    {
        $result = $this->clientService->safeLogout($client, $session);

        event(new TelegramSessionTerminated($session, auth()->user()));

        return ['success' => true, 'message' => 'Logged out successfully.'];
    }
}
```

### 3. Rate Limiting
Add rate limiting to services:
```php
class TelegramMessageService
{
    public function sendMessageToChannel(...): array
    {
        if (RateLimiter::tooManyAttempts('telegram:send:' . auth()->id(), 10)) {
            return [
                'success' => false,
                'error' => 'Too many messages sent. Please wait before trying again.',
            ];
        }

        RateLimiter::hit('telegram:send:' . auth()->id());

        // Send message...
    }
}
```

### 4. Async Operations
Use queues for long-running operations:
```php
class TelegramChannelService
{
    public function createChannel(...): array
    {
        dispatch(new CreateTelegramChannel($client, $title, $description))
            ->onQueue('telegram');

        return [
            'success' => true,
            'message' => 'Channel creation queued. You will be notified when complete.',
        ];
    }
}
```

## Bug Fixes

### Issue: Infinite Page Reload (Fixed)

**Problem**: After initial refactoring, the page would infinitely reload when accessing the Telegram integration URL.

**Root Cause**:
1. The `getQrCode()` method passed `$this->client` to `generateQrCode()` without checking if client initialization succeeded
2. The service's `generateQrCode()` treated `qrLogin()` result as an array, but it actually returns an object with `getQRSvg()` method

**Fix Applied**:
1. Added null checks after client initialization in all methods:
   - `getQrCode()`
   - `sendPhoneNumber()`
   - `sendCompletePhoneLogin()`
   - `getTelegramChannels()`
   - `createChannel()`
   - `deleteTelegramChannel()`
   - `sendChannelInviteToUser()`
   - `sendMessageToChannel()`

2. Fixed `TelegramAuthService::generateQrCode()` to return SVG string directly:
```php
public function generateQrCode(API $client): ?string
{
    try {
        if ($client->getAuthorization() === API::LOGGED_IN) {
            return null;
        }

        if ($client->getAuthorization() === API::WAITING_CODE) {
            return null;
        }

        $qrLogin = $client->qrLogin();

        if ($qrLogin) {
            return $qrLogin->getQRSvg(200, 2);
        }

        return null;
    } catch (\Throwable $e) {
        Log::error('QR code generation failed', ['error' => $e->getMessage()]);
        return null;
    }
}
```

3. Updated component to use SVG string directly instead of placeholder method

**Status**: ✅ Fixed and tested

### Issue: Logout Button Not Working (Fixed)

**Problem**: The logout button was failing with error "Argument #1 ($session) must be of type App\Models\TelegramSession, null given"

**Root Cause**:
- `$this->currentSession` was null when `logoutFromTelegram()` was called
- This happens when component state is lost or logout is called multiple times

**Fix Applied**:
Added null check and session retrieval in `logoutFromTelegram()`:
```php
public function logoutFromTelegram()
{
    try {
        // Get current session if not already set
        if (!$this->currentSession) {
            $this->currentSession = $this->sessionRepository->getActiveSession(Auth::id());
        }

        // Only call terminate if we have a session
        if ($this->currentSession) {
            $result = $this->authService->terminateSession($this->currentSession, $this->client);
            // Handle result...
        } else {
            session()->flash('message', 'Already logged out.');
        }

        // Clear local state regardless
        $this->currentSession = null;
        $this->client = null;
        // ...
    } catch (\Throwable $e) {
        // Still clear state even on error
        // ...
    }
}
```

**Status**: ✅ Fixed

### Issue: Failed to Get Channels (Fixed)

**Problem**: "Call to undefined method danog\MadelineProto\API::getDialogs()"

**Root Cause**:
- Used incorrect MadelineProto API path `$client->getDialogs()`
- Should be `$client->messages->getDialogs()`
- Response structure was also incorrect

**Fix Applied**:
Updated `TelegramChannelService::getChannels()` to:
```php
public function getChannels(API $client): array
{
    try {
        $dialogs = $client->messages->getDialogs();  // Fixed: was $client->getDialogs()
        $channels = [];

        foreach ($dialogs['chats'] ?? [] as $chat) {  // Fixed: iterate over 'chats' array
            if (
                isset($chat['_']) &&  // Fixed: check '_' field
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

        return $channels;
    } catch (\Throwable $e) {
        Log::error('Failed to get channels', ['error' => $e->getMessage()]);
        return [];
    }
}
```

**Status**: ✅ Fixed

### Issue: Login Code Input Not Showing (Fixed)

**Problem**: After entering phone number, the login code input field didn't appear, making it impossible to complete phone login.

**Root Cause**:
- The `sendPhoneNumber()` method wasn't setting `$this->telegramAuthState = 1` when code was required
- The view checks `@if($telegramAuthState === 1)` to show the login code input
- Without this state change, the input never appeared

**Fix Applied**:
```php
public function sendPhoneNumber()
{
    // ... validation and client initialization ...

    $result = $this->authService->initiatePhoneLogin($this->client, $this->phone);

    if ($result['success']) {
        if ($result['logged_in'] ?? false) {
            $this->mount();
            session()->flash('success', $result['message']);
        } else {
            // Code is required - update state to show login code input
            $this->telegramAuthState = 1;  // ✅ ADDED THIS LINE
            session()->flash('message', $result['message']);
        }
    }
}
```

**Location**: `app/Livewire/Integrations/Telegram/Index.php:174`

**Status**: ✅ Fixed

**Follow-up Issue**: Login code input showed "0" instead of being empty

After the initial fix, the login code input appeared correctly, but showed "0" in the field because the property was defined as `public int $loginCode = 0;` on line 24.

**Fix Applied**:
Changed the property type from `int` to `string` and initialized it as empty string:
```php
// Before:
public int $loginCode = 0;

// After:
public string $loginCode = '';
```

**Location**: `app/Livewire/Integrations/Telegram/Index.php:24`

The validation rule `digits:5` continues to work correctly with string input, and the cast to string on line 211 `(string) $this->loginCode` remains valid.

**Additional Improvement**: Enhanced user messaging

Added clearer messaging to indicate where the verification code is sent. Telegram can send codes via:
- **auth.sentCodeTypeApp** - Code sent inside Telegram app (most common)
- **auth.sentCodeTypeSms** - Code sent via SMS text message
- **auth.sentCodeTypeCall** - Code delivered via phone call

Updated message:
```php
if ($codeType === 'auth.sentCodeTypeApp') {
    $message = 'Code sent to your Telegram app! Open Telegram on any device and check for a message from "Telegram" with your login code.';
}
```

**Location**: `app/Services/Telegram/TelegramAuthService.php:113-131`

**Status**: ✅ Fixed and Verified

### Issue: Logout Button Causing Jump/Twitch and Page Freeze (Fixed)

**Problem**: After clicking logout, the page would "jump" and twitch, and when revisiting the page it would freeze or infinitely reload.

**Root Causes**:
1. Logout was using `redirect()` to the same page, causing a jarring page reload
2. After logout, calling `mount()` attempted to recreate sessions immediately
3. `checkTelegramSession()` was calling `getOrCreateSession()`, auto-creating sessions on page load
4. The render method checked `!$this->client` and showed connection-message view with JavaScript redirect, causing infinite loop
5. Active sessions remained in database after logout

**Fixes Applied**:

1. **Removed redirect from logout** (Lines 262, 280):
```php
// Before:
return $this->redirect(route('integrations.telegram.index'), navigate: true);

// After:
// Don't call mount() - just let Livewire re-render with cleared state
```

2. **Changed session retrieval to not auto-create** (Line 65):
```php
// Before:
$this->currentSession = $this->authService->getOrCreateSession(Auth::id(), ...);

// After:
$this->currentSession = $this->sessionRepository->getActiveSession(Auth::id());
```

3. **Fixed showTelegramLogin() to not auto-generate QR** (Lines 457-462):
```php
// Before:
public function showTelegramLogin(): void
{
    $this->getQrCode(); // This creates a session!
}

// After:
public function showTelegramLogin(): void
{
    // Just set state to show login screen
    // Don't automatically generate QR code to avoid creating sessions
    $this->telegramAuthState = 0;
}
```

4. **Fixed render() method to avoid infinite redirect** (Lines 467-477):
```php
// Before:
public function render()
{
    if (!$this->client) {
        return view('livewire.integrations.telegram.connection-message'); // Has JS redirect!
    }
    // ...
}

// After:
public function render()
{
    // Show dashboard if logged in
    if ($this->telegramAuthState === 3) {
        $this->getTelegramChannels();
        return view('livewire.integrations.telegram.dashboard');
    }

    // Show login screen for all other states (0 = not logged in, 1 = waiting for code)
    return view('livewire.integrations.telegram.index');
}
```

**Key Improvements**:
- Sessions are only created when user explicitly clicks "Generate QR Code" or submits phone number
- Logout smoothly clears state without page reload
- Page loads correctly after logout without freezing or infinite reload
- Better UX with smooth Livewire transitions instead of full page reloads

**Status**: ✅ Fixed and Tested

## Conclusion

The service layer refactoring provides:
- ✅ **Better organization** - Clear separation of concerns
- ✅ **Easier testing** - Services can be unit tested
- ✅ **More maintainable** - Single responsibility per service
- ✅ **Reusable code** - Services work in any context
- ✅ **Consistent API** - All services return structured responses
- ✅ **Easier to extend** - Add new features without modifying existing code
- ✅ **Robust error handling** - Null checks prevent runtime errors

The refactored code is production-ready and follows Laravel best practices.

---

**Document Version**: 1.1.0
**Last Updated**: 2025-10-12
**Author**: Service Layer Refactoring Initiative
