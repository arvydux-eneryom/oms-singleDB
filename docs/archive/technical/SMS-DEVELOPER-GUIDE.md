# SMS Service - Developer Guide

**Version:** 1.0
**Last Updated:** October 2025
**For:** Developers, DevOps, and Technical Staff

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Technology Stack](#technology-stack)
3. [Directory Structure](#directory-structure)
4. [Core Components](#core-components)
5. [Database Schema](#database-schema)
6. [Workflow Diagrams](#workflow-diagrams)
7. [API & Webhooks](#api--webhooks)
8. [Queue System](#queue-system)
9. [Testing](#testing)
10. [Deployment & Configuration](#deployment--configuration)
11. [Extending the System](#extending-the-system)
12. [Troubleshooting](#troubleshooting)
13. [Performance Optimization](#performance-optimization)

---

## Architecture Overview

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Application Layer                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  Livewire    │  │   Console    │  │   API        │      │
│  │  Components  │  │   Commands   │  │   Routes     │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                  │                  │               │
└─────────┼──────────────────┼──────────────────┼──────────────┘
          │                  │                  │
┌─────────▼──────────────────▼──────────────────▼──────────────┐
│                     Service Layer                             │
│  ┌──────────────────┐  ┌──────────────────────────────┐     │
│  │ SmsManagerService│  │   TwilioSmsService           │     │
│  │  (Business Logic)│  │   (Twilio Integration)       │     │
│  └────────┬─────────┘  └─────────┬────────────────────┘     │
│           │                       │                           │
└───────────┼───────────────────────┼───────────────────────────┘
            │                       │
┌───────────▼───────────────────────▼───────────────────────────┐
│                   Repository Layer                             │
│  ┌─────────────────┐ ┌──────────────────┐ ┌────────────────┐│
│  │ SmsMessage      │ │ SmsResponse      │ │ SentSms        ││
│  │ Repository      │ │ Repository       │ │ Question       ││
│  │                 │ │                  │ │ Repository     ││
│  └────────┬────────┘ └────────┬─────────┘ └────────┬───────┘│
└───────────┼──────────────────────┼───────────────────┼────────┘
            │                      │                   │
┌───────────▼──────────────────────▼───────────────────▼────────┐
│                        Data Layer                              │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────────┐│
│  │ sms_messages│ │sms_responses│ │ sent_sms_questions      ││
│  │             │ │             │ │                         ││
│  └─────────────┘ └─────────────┘ └─────────────────────────┘│
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│                    External Services                            │
│  ┌──────────────────┐  ┌────────────────────────────────┐     │
│  │  Twilio API      │  │  Twilio Webhooks               │     │
│  │  (Send SMS)      │  │  (Status Callbacks)            │     │
│  └──────────────────┘  └────────────────────────────────┘     │
└────────────────────────────────────────────────────────────────┘
```

### Design Patterns Used

1. **Repository Pattern**: Data access abstraction
2. **Service Layer**: Business logic separation
3. **DTO (Data Transfer Objects)**: Type-safe data passing
4. **Dependency Injection**: Loose coupling and testability
5. **Job/Queue Pattern**: Async processing for bulk operations

### Key Principles

- ✅ Single Responsibility Principle
- ✅ Dependency Inversion
- ✅ Separation of Concerns
- ✅ Fail-Safe Error Handling
- ✅ Comprehensive Logging

---

## Technology Stack

### Backend
- **Framework**: Laravel 11.x
- **Language**: PHP 8.3+
- **Database**: MySQL 8.0+
- **Queue**: Sync (Development) / Database (Production option)

### Frontend
- **UI Framework**: Livewire 3.x
- **CSS Framework**: Tailwind CSS
- **JavaScript**: Alpine.js (via Livewire)

### External Services
- **SMS Provider**: Twilio
- **Tunneling**: ngrok (Development webhooks)

### Development Tools
- **Testing**: PHPUnit with Laravel TestCase
- **Code Quality**: Pint (Laravel code formatter)
- **Package Manager**: Composer

---

## Directory Structure

```
oms/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── UpdateTwilioWebhook.php        # Twilio webhook updater
│   ├── DTOs/
│   │   ├── IncomingSmsData.php                # Incoming SMS DTO
│   │   └── OutgoingSmsStatusData.php          # Status callback DTO
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── TwilioWebhookController.php    # Webhook endpoints
│   │   └── Middleware/
│   │       └── VerifyTwilioWebhook.php        # Signature verification
│   ├── Jobs/
│   │   ├── SendBulkSmsJob.php                 # Bulk SMS job
│   │   └── SendSmsJob.php                     # Single SMS job
│   ├── Livewire/
│   │   └── Integrations/
│   │       └── SmsManager.php                 # Main UI component
│   ├── Models/
│   │   ├── SmsMessage.php                     # SMS message model
│   │   ├── SmsQuestion.php                    # Question model
│   │   ├── SmsResponse.php                    # Response model
│   │   └── SentSmsQuestion.php                # Sent question tracker
│   ├── Repositories/
│   │   ├── SentSmsQuestionRepository.php      # Question repo
│   │   ├── SmsMessageRepository.php           # Message repo
│   │   └── SmsResponseRepository.php          # Response repo
│   └── Services/
│       ├── SmsManagerService.php              # Business logic
│       ├── SmsServiceInterface.php            # SMS interface
│       └── TwilioSmsService.php               # Twilio implementation
├── config/
│   └── services.php                           # Twilio config
├── database/
│   └── migrations/
│       ├── *_create_sms_messages_table.php
│       ├── *_create_sms_questions_table.php
│       ├── *_create_sent_sms_questions_table.php
│       ├── *_create_sms_responses_table.php
│       ├── *_add_sent_sms_question_id_to_sms_responses_table.php
│       └── *_add_user_id_to_sent_sms_questions_table.php
├── resources/
│   └── views/
│       └── livewire/
│           └── integrations/
│               └── sms-manager.blade.php       # UI template
├── routes/
│   └── web.php                                # Routes definition
└── tests/
    └── Unit/
        └── Services/
            ├── SmsManagerServiceTest.php
            └── TwilioSmsServiceTest.php
```

---

## Core Components

### 1. Services

#### SmsServiceInterface

**Purpose**: Contract for SMS service implementations
**Location**: `app/Services/SmsServiceInterface.php`

```php
interface SmsServiceInterface
{
    public function send($to, $body, ?int $userId = null): MessageInstance;
    public function sendQueued(string $to, string $body, ?int $userId = null): void;
    public function handleIncomingSms(IncomingSmsData $data): Response;
    public function handleOutgoingSmsStatus(OutgoingSmsStatusData $data): Response;
}
```

**Methods:**
- `send()`: Synchronous SMS sending
- `sendQueued()`: Async SMS via queue
- `handleIncomingSms()`: Process incoming messages
- `handleOutgoingSmsStatus()`: Handle status callbacks

---

#### TwilioSmsService

**Purpose**: Twilio API integration
**Location**: `app/Services/TwilioSmsService.php`

**Key Responsibilities:**
- Send SMS via Twilio REST API
- Create database records for sent SMS
- Handle Twilio exceptions with user-friendly messages
- Fetch account balance

**Example Usage:**

```php
$smsService = app(TwilioSmsService::class);

// Send SMS
$message = $smsService->send('+37064626008', 'Hello!', auth()->id());

// Queue SMS
$smsService->sendQueued('+37064626008', 'Async message', auth()->id());

// Get balance
$balance = $smsService->getAccountBalance();
```

**Error Handling:**

```php
try {
    $message = $smsService->send($to, $body, $userId);
} catch (\RuntimeException $e) {
    // User-friendly error message
    // "Invalid phone number format."
    // "SMS service temporarily unavailable."
    // etc.
}
```

---

#### SmsManagerService

**Purpose**: Business logic for question-based SMS workflows
**Location**: `app/Services/SmsManagerService.php`

**Key Responsibilities:**
- Send question SMS with multiple-choice options
- Validate user responses
- Prevent duplicate responses
- Send confirmation messages
- Manage welcome messages (first-time vs. returning users)

**Example Usage:**

```php
$smsManager = app(SmsManagerService::class);

// Send question
$smsManager->sendQuestion('+37064626008', auth()->id());

// Process incoming answer
$data = new IncomingSmsData(/* ... */);
$valid = $smsManager->hasAnsweredSentQuestion($data); // true/false
```

**Duplicate Prevention Logic:**

```php
// Checks if user already answered THIS specific sent question instance
if ($this->smsResponseRepository->hasAnswered($sentSmsQuestion->id)) {
    return false; // Duplicate
}
```

---

### 2. Repositories

#### SmsMessageRepository

**Purpose**: CRUD operations for sms_messages table
**Location**: `app/Repositories/SmsMessageRepository.php`

**Key Methods:**

```php
// Create outgoing SMS record
public function createOutgoing(
    string $messageSid,
    string $to,
    string $from,
    string $body,
    string $status,
    string $accountSid,
    ?int $userId = null
): SmsMessage

// Create incoming SMS record
public function createIncoming(IncomingSmsData $data): SmsMessage

// Update SMS status
public function updateStatus(string $messageSid, string $status): bool

// Check if user has outgoing messages
public function hasOutgoingMessagesTo(string $phone): bool
```

---

#### SmsResponseRepository

**Purpose**: Manage question responses
**Location**: `app/Repositories/SmsResponseRepository.php`

**Key Methods:**

```php
// Create response record
public function create(
    SmsQuestion $question,
    SentSmsQuestion $sentSmsQuestion,
    string $phone,
    string $answer
): SmsResponse

// Check if question instance already answered
public function hasAnswered(int $sentSmsQuestionId): bool
```

---

#### SentSmsQuestionRepository

**Purpose**: Track sent questions
**Location**: `app/Repositories/SentSmsQuestionRepository.php`

**Key Methods:**

```php
// Create sent question record
public function create(
    string $to,
    int $smsQuestionId,
    ?int $userId = null
): SentSmsQuestion

// Get latest question sent to phone
public function getLatestForPhone(string $phone): ?SentSmsQuestion
```

---

### 3. Data Transfer Objects (DTOs)

#### IncomingSmsData

**Purpose**: Type-safe incoming SMS data
**Location**: `app/DTOs/IncomingSmsData.php`

```php
readonly class IncomingSmsData
{
    public function __construct(
        public string $messageSid,
        public string $accountSid,
        public string $from,
        public string $to,
        public string $body,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            messageSid: $data['MessageSid'],
            accountSid: $data['AccountSid'],
            from: $data['From'],
            to: $data['To'],
            body: $data['Body'],
        );
    }
}
```

---

#### OutgoingSmsStatusData

**Purpose**: Type-safe status callback data
**Location**: `app/DTOs/OutgoingSmsStatusData.php`

```php
readonly class OutgoingSmsStatusData
{
    public function __construct(
        public string $messageSid,
        public string $smsStatus,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            messageSid: $data['MessageSid'],
            smsStatus: $data['SmsStatus'],
        );
    }
}
```

---

### 4. Jobs

#### SendBulkSmsJob

**Purpose**: Queue processing for bulk SMS
**Location**: `app/Jobs/SendBulkSmsJob.php`

**Queue Configuration:**
- Uses `Batchable` trait for batch processing
- Implements `ShouldQueue` for async execution
- Dispatched with 1-second delay between messages

**Example:**

```php
$jobs = [];
foreach ($selectedUsers as $user) {
    $jobs[] = (new SendBulkSmsJob($user, $body, auth()->id()))
        ->delay(now()->addSecond());
}

Bus::batch($jobs)
    ->then(function (Batch $batch) {
        // All succeeded
    })
    ->catch(function (Batch $batch, \Throwable $e) {
        // Some failed
    })
    ->dispatch();
```

---

### 5. Middleware

#### VerifyTwilioWebhook

**Purpose**: Validate Twilio webhook signatures
**Location**: `app/Http/Middleware/VerifyTwilioWebhook.php`

**Security:**
- Validates X-Twilio-Signature header
- Uses Twilio's RequestValidator
- Rejects requests with invalid signatures

```php
public function handle(Request $request, Closure $next): Response
{
    $validator = new RequestValidator(config('services.twilio.token'));

    $signature = $request->header('X-Twilio-Signature', '');
    $url = $request->fullUrl();
    $data = $request->all();

    if (!$validator->validate($signature, $url, $data)) {
        Log::warning('Invalid Twilio webhook signature', [
            'ip' => $request->ip(),
            'url' => $url,
            'signature' => $signature,
        ]);

        return response('Forbidden', 403);
    }

    return $next($request);
}
```

---

## Database Schema

### sms_messages Table

**Purpose**: Store all SMS (incoming + outgoing)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| sms_sid | string(34) | Twilio message SID (unique) |
| status | string | queued, sent, delivered, undelivered |
| to | string | Recipient phone |
| from | string | Sender phone |
| body | text | Message content |
| message_sid | string(34) | Twilio message ID |
| account_sid | string(34) | Twilio account ID |
| message_type | string | incoming, outgoing |
| user_id | bigint (nullable) | User who sent (FK to users) |
| created_at | timestamp | When created |
| updated_at | timestamp | Last updated |

**Indexes:**
- `sms_sid` (unique)
- `message_type`
- `user_id`
- `created_at`

---

### sms_questions Table

**Purpose**: Store survey questions

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| question | text | Question text |
| options | json | Multiple choice options |
| created_at | timestamp | When created |
| updated_at | timestamp | Last updated |

**Example JSON:**

```json
{
  "1": "Basic",
  "2": "Standard",
  "3": "Premium",
  "4": "VIP"
}
```

---

### sent_sms_questions Table

**Purpose**: Track which questions were sent to whom

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| to | string | Recipient phone |
| sms_question_id | bigint | FK to sms_questions |
| user_id | bigint (nullable) | User who sent (FK to users) |
| created_at | timestamp | When sent |
| updated_at | timestamp | Last updated |

**Purpose**: Links sent questions to specific sending instances

---

### sms_responses Table

**Purpose**: Store user responses to questions

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| question_id | bigint | FK to sms_questions |
| sent_sms_question_id | bigint (nullable) | FK to sent_sms_questions |
| phone | string | Responder's phone |
| answer | string | User's answer (e.g., "1") |
| plain_answer | string | Human-readable answer (e.g., "Premium") |
| created_at | timestamp | When answered |
| updated_at | timestamp | Last updated |

**Key Relationship:**
- `sent_sms_question_id` links response to specific question instance
- Allows same question to be answered multiple times (different days)

---

## Workflow Diagrams

### 1. Send Single SMS Flow

```
┌─────────────┐
│   User UI   │
└──────┬──────┘
       │ 1. Click "Send SMS"
       │    (to: +37064626008, body: "Hello")
       ▼
┌──────────────────────┐
│ SmsManager Livewire  │
│ - Validate inputs    │
│ - Trim whitespace    │
└──────┬───────────────┘
       │ 2. Call send()
       ▼
┌──────────────────────┐
│ TwilioSmsService     │
│ - Trim phone         │
│ - Call Twilio API    │
└──────┬───────────────┘
       │ 3. Twilio REST API
       ▼
┌──────────────────────┐
│   Twilio Cloud       │
│ - Queue message      │
│ - Return MessageSID  │
└──────┬───────────────┘
       │ 4. MessageInstance
       ▼
┌──────────────────────┐
│ SmsMessageRepository │
│ - Create DB record   │
│ - Status: "queued"   │
└──────┬───────────────┘
       │ 5. Success
       ▼
┌──────────────────────┐
│   User UI            │
│ ✅ "SMS sent!"       │
│ 📊 Shows in history  │
└──────────────────────┘

       ⏱️ (2-10 seconds later)

┌──────────────────────┐
│   Twilio Cloud       │
│ - Sends webhook      │
│ - Status: "sent"     │
└──────┬───────────────┘
       │ 6. POST /twilio/sms/outgoing/status
       ▼
┌──────────────────────┐
│ VerifyTwilio         │
│ Webhook Middleware   │
│ - Check signature    │
└──────┬───────────────┘
       │ 7. Valid
       ▼
┌──────────────────────┐
│ TwilioWebhook        │
│ Controller           │
│ - Parse data         │
└──────┬───────────────┘
       │ 8. Update status
       ▼
┌──────────────────────┐
│ SmsMessageRepository │
│ - Find by MessageSID │
│ - Update: "sent"     │
└──────────────────────┘

       ⏱️ (few seconds later)

       Similar flow for "delivered" status
```

---

### 2. Question SMS Flow

```
┌─────────────┐
│   User UI   │
└──────┬──────┘
       │ 1. Click "Send Question"
       │    (to: +37064626008)
       ▼
┌──────────────────────┐
│ SmsManagerService    │
│ - Get random question│
│ - Format options     │
│ - Add welcome msg    │
└──────┬───────────────┘
       │ 2. Formatted message
       │    "Welcome back!
       │     Which plan?
       │     1. Basic
       │     2. Standard..."
       ▼
┌──────────────────────┐
│ TwilioSmsService     │
│ - Send via API       │
└──────┬───────────────┘
       │ 3. Message sent
       ▼
┌──────────────────────┐
│ SentSmsQuestion      │
│ Repository           │
│ - Create tracker     │
│ - Link user_id       │
└──────────────────────┘

       ⏱️ (User receives & replies "3")

┌──────────────────────┐
│   Twilio Cloud       │
│ - Receives SMS "3"   │
│ - Sends webhook      │
└──────┬───────────────┘
       │ 4. POST /twilio/sms/incoming
       ▼
┌──────────────────────┐
│ VerifyTwilio         │
│ Webhook Middleware   │
└──────┬───────────────┘
       │ 5. Valid
       ▼
┌──────────────────────┐
│ TwilioWebhook        │
│ Controller           │
│ - Create DTO         │
└──────┬───────────────┘
       │ 6. IncomingSmsData
       ▼
┌──────────────────────┐
│ TwilioSmsService     │
│ - Save incoming SMS  │
└──────┬───────────────┘
       │ 7. Call handler
       ▼
┌──────────────────────┐
│ SmsManagerService    │
│ hasAnsweredSent      │
│ Question()           │
└──────┬───────────────┘
       │ 8. Validate
       │    ✅ Valid option?
       │    ✅ Not duplicate?
       ▼
┌──────────────────────┐
│ SmsResponse          │
│ Repository           │
│ - Create response    │
│ - answer: "3"        │
│ - plain: "Premium"   │
└──────┬───────────────┘
       │ 9. Send confirmation
       ▼
┌──────────────────────┐
│ TwilioSmsService     │
│ - Send: "You         │
│   selected: Premium" │
└──────────────────────┘
```

---

### 3. Webhook Signature Verification

```
┌──────────────────────┐
│   Twilio Cloud       │
└──────┬───────────────┘
       │ 1. POST webhook with
       │    X-Twilio-Signature header
       ▼
┌──────────────────────┐
│   Laravel App        │
│   (behind ngrok)     │
└──────┬───────────────┘
       │ 2. Middleware intercept
       ▼
┌──────────────────────────────────────┐
│ VerifyTwilioWebhook Middleware       │
│                                      │
│  1. Get X-Twilio-Signature header    │
│  2. Get full URL (with HTTPS!)       │
│  3. Get POST data                    │
│                                      │
│  $validator->validate(              │
│      $signature,                     │
│      $url,      ← Must be HTTPS!     │
│      $data                           │
│  )                                   │
└──────┬───────────────────────────────┘
       │
       ▼
  ┌─────────┐
  │ Valid?  │
  └────┬────┘
       │
   ┌───▼───┐
   │ YES   │ NO
   │       │
   ▼       ▼
┌─────┐  ┌─────────────┐
│ 200 │  │ 403 Forbidden│
│ OK  │  │ + Log warning│
└─────┘  └─────────────┘
```

**Critical**: URL must be HTTPS! Ngrok provides this. Local HTTP URLs will fail validation.

---

## API & Webhooks

### Twilio Webhook Endpoints

#### 1. Incoming SMS Webhook

**Endpoint:** `POST /twilio/sms/incoming`
**Purpose:** Receive incoming SMS from Twilio
**Middleware:** `VerifyTwilioWebhook`

**Request (from Twilio):**

```http
POST /twilio/sms/incoming HTTP/1.1
Host: ae8d057ed880.ngrok-free.app
X-Twilio-Signature: abc123...
Content-Type: application/x-www-form-urlencoded

MessageSid=SM123...
AccountSid=AC456...
From=%2B37064626008
To=%2B447426914907
Body=3
```

**Response:**

```http
HTTP/1.1 200 OK
```

**Handler Logic:**

```php
public function handleIncomingSms(Request $request): Response
{
    $data = IncomingSmsData::fromRequest($request->all());

    // Save incoming SMS to database
    $response = $this->smsService->handleIncomingSms($data);

    // Process if it's an answer to question
    $smsManagerService = app(SmsManagerService::class);
    $smsManagerService->hasAnsweredSentQuestion($data);

    return $response;
}
```

---

#### 2. Outgoing SMS Status Callback

**Endpoint:** `POST /twilio/sms/outgoing/status`
**Purpose:** Receive status updates for sent SMS
**Middleware:** `VerifyTwilioWebhook`

**Request (from Twilio):**

```http
POST /twilio/sms/outgoing/status HTTP/1.1
Host: ae8d057ed880.ngrok-free.app
X-Twilio-Signature: xyz789...
Content-Type: application/x-www-form-urlencoded

MessageSid=SM123...
SmsStatus=delivered
```

**Response:**

```http
HTTP/1.1 200 OK
```

**Handler Logic:**

```php
public function handleOutgoingSmsStatus(Request $request): Response
{
    $data = OutgoingSmsStatusData::fromRequest($request->all());

    return $this->smsService->handleOutgoingSmsStatus($data);
}
```

---

### Webhook Configuration

#### Development (ngrok)

1. **Start ngrok:**
   ```bash
   ngrok http 80
   ```

2. **Get forwarding URL:**
   ```
   Forwarding: https://ae8d057ed880.ngrok-free.app -> http://localhost:80
   ```

3. **Update Twilio webhooks:**
   ```bash
   php artisan twilio:update-webhook
   # Enter: ae8d057ed880.ngrok-free.app
   ```

4. **Add to central_domains:**
   ```php
   // config/tenancy.php
   'central_domains' => [
       'localhost',
       'ae8d057ed880.ngrok-free.app',
   ],
   ```

#### Production

1. **Set webhooks in Twilio Console:**
   - Incoming: `https://yourdomain.com/twilio/sms/incoming`
   - Status: `https://yourdomain.com/twilio/sms/outgoing/status`

2. **Ensure HTTPS:**
   - SSL certificate required
   - Webhook signature validation requires HTTPS

---

## Queue System

### Queue Configuration

#### Development: Sync Queue

**File:** `.env`

```env
QUEUE_CONNECTION=sync
```

**Behavior:**
- Jobs execute immediately (synchronous)
- No worker needed
- Good for development/testing
- Bulk SMS sends all messages in one request

#### Production: Database Queue

**File:** `.env`

```env
QUEUE_CONNECTION=database
```

**Setup:**

1. **Create jobs table:**
   ```bash
   php artisan queue:table
   php artisan queue:batches-table
   php artisan migrate
   ```

2. **Start queue worker:**
   ```bash
   php artisan queue:work --tries=3 --timeout=60
   ```

3. **Use Supervisor (recommended):**
   ```ini
   [program:laravel-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /path/to/artisan queue:work --sleep=3 --tries=3
   autostart=true
   autorestart=true
   user=www-data
   numprocs=2
   redirect_stderr=true
   stdout_logfile=/path/to/worker.log
   ```

**Behavior:**
- Jobs queued to database
- Worker processes jobs asynchronously
- Better performance for bulk operations
- Supports batching and retries

---

### Job Monitoring

#### Check Queue Status

```bash
# Pending jobs
php artisan queue:monitor database

# Failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry [job-id]

# Clear failed jobs
php artisan queue:flush
```

#### Check Batch Status

```php
use Illuminate\Support\Facades\DB;

$batches = DB::table('job_batches')
    ->where('finished_at', null)
    ->get();

foreach ($batches as $batch) {
    echo "Batch: {$batch->id}\n";
    echo "Total: {$batch->total_jobs}\n";
    echo "Pending: {$batch->pending_jobs}\n";
    echo "Failed: {$batch->failed_jobs}\n";
}
```

---

## Testing

### Test Structure

```
tests/
└── Unit/
    └── Services/
        ├── SmsManagerServiceTest.php
        └── TwilioSmsServiceTest.php
```

### Running Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test --filter=SmsManagerServiceTest

# With coverage (requires Xdebug)
XDEBUG_MODE=coverage php artisan test --coverage
```

### Example Test: Sending SMS

```php
public function test_it_can_send_sms()
{
    // Arrange
    $twilioMock = Mockery::mock(Client::class);
    $messagesMock = Mockery::mock();

    $twilioMock->messages = $messagesMock;

    $messagesMock->shouldReceive('create')
        ->once()
        ->with(
            '+37064626008',
            Mockery::on(function ($args) {
                return $args['from'] === config('services.twilio.from')
                    && $args['body'] === 'Test message';
            })
        )
        ->andReturn($this->createMessageInstance());

    $this->app->instance(Client::class, $twilioMock);

    // Act
    $service = app(TwilioSmsService::class);
    $message = $service->send('+37064626008', 'Test message', 1);

    // Assert
    $this->assertInstanceOf(MessageInstance::class, $message);
    $this->assertDatabaseHas('sms_messages', [
        'to' => '+37064626008',
        'body' => 'Test message',
        'user_id' => 1,
    ]);
}
```

### Example Test: Question Validation

```php
public function test_it_validates_answer_correctly()
{
    // Arrange
    $question = SmsQuestion::factory()->create([
        'options' => ['1' => 'Option 1', '2' => 'Option 2'],
    ]);

    $sentQuestion = SentSmsQuestion::factory()->create([
        'to' => '+37064626008',
        'sms_question_id' => $question->id,
    ]);

    $data = new IncomingSmsData(
        messageSid: 'SM123',
        accountSid: 'AC456',
        from: '+37064626008',
        to: '+447426914907',
        body: '1'
    );

    // Act
    $service = app(SmsManagerService::class);
    $valid = $service->hasAnsweredSentQuestion($data);

    // Assert
    $this->assertTrue($valid);
    $this->assertDatabaseHas('sms_responses', [
        'question_id' => $question->id,
        'sent_sms_question_id' => $sentQuestion->id,
        'phone' => '+37064626008',
        'answer' => '1',
        'plain_answer' => 'Option 1',
    ]);
}
```

---

## Deployment & Configuration

### Environment Variables

**File:** `.env`

```env
# Twilio Configuration
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_PHONE_NUMBER=+447426914907

# Queue Configuration
QUEUE_CONNECTION=sync  # or 'database' for production

# Cache Configuration
CACHE_STORE=database
CACHE_DRIVER=database

# Multi-tenancy
TENANCY_ENABLED=true
```

### Twilio Configuration

**File:** `config/services.php`

```php
'twilio' => [
    'sid' => env('TWILIO_ACCOUNT_SID'),
    'token' => env('TWILIO_AUTH_TOKEN'),
    'from' => env('TWILIO_PHONE_NUMBER'),

    // Webhook paths (relative)
    'incoming_sms_url_path' => '/twilio/sms/incoming',
    'outgoing_sms_status_callback_url_path' => '/twilio/sms/outgoing/status',

    // Full webhook URLs (computed)
    'sms_common_url' => env('APP_URL'),
    'outgoing_sms_status_callback_url' => env('APP_URL') . '/twilio/sms/outgoing/status',
],
```

### Proxy Trust Configuration

**File:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    // Trust all proxies (ngrok) for HTTPS detection
    $middleware->trustProxies(at: '*', headers:
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO
    );
})
```

**Why needed:** ngrok forwards HTTPS → HTTP. Laravel needs to trust proxy headers to get correct URL scheme for webhook signature validation.

### Multi-tenancy Configuration

**File:** `config/tenancy.php`

```php
'central_domains' => [
    'localhost',
    'ae8d057ed880.ngrok-free.app', // Development
    'your-production-domain.com',  // Production
],
```

### CSRF Exemption

**File:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        '/twilio/sms/*',  // Exempt Twilio webhooks
    ]);
})
```

### Rate Limiting

**File:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    // SMS sending rate limit: 10 per minute per user
    $middleware->throttleApi('sms-sending', function (Request $request) {
        return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)
            ->by($request->user()?->id ?: $request->ip());
    });
})
```

**Usage in routes:**

```php
Route::post('integrations/sms/send-question', [SmsManagerService::class, 'sendQuestion'])
    ->middleware('throttle:sms-sending');
```

---

## Extending the System

### Adding a New SMS Provider

1. **Create new service class:**

```php
// app/Services/CustomSmsService.php
class CustomSmsService implements SmsServiceInterface
{
    public function send($to, $body, ?int $userId = null): MessageInstance
    {
        // Your implementation
    }

    public function sendQueued(string $to, string $body, ?int $userId = null): void
    {
        // Your implementation
    }

    // ... implement other methods
}
```

2. **Bind in service container:**

```php
// app/Providers/AppServiceProvider.php
public function register()
{
    $this->app->bind(
        SmsServiceInterface::class,
        CustomSmsService::class
    );
}
```

3. **Add configuration:**

```php
// config/services.php
'custom_sms' => [
    'api_key' => env('CUSTOM_SMS_API_KEY'),
    'api_secret' => env('CUSTOM_SMS_SECRET'),
],
```

---

### Adding New Question Types

1. **Add to questions table:**

```php
SmsQuestion::create([
    'question' => 'Rate our service',
    'options' => [
        '1' => 'Very Poor',
        '2' => 'Poor',
        '3' => 'Good',
        '4' => 'Very Good',
        '5' => 'Excellent',
    ],
]);
```

2. **Modify validation if needed:**

```php
// app/Services/SmsManagerService.php
if (!isset($smsQuestion->options[$answer])) {
    // Handle invalid answer
}
```

---

### Adding SMS Templates

1. **Create template service:**

```php
class SmsTemplateService
{
    public function orderShipped(string $orderNumber): string
    {
        return "Your order #{$orderNumber} has been shipped!";
    }

    public function appointmentReminder(string $date, string $time): string
    {
        return "Reminder: Your appointment is on {$date} at {$time}.";
    }
}
```

2. **Use in controllers:**

```php
$template = app(SmsTemplateService::class);
$message = $template->orderShipped('12345');
$smsService->send($phone, $message, $userId);
```

---

### Adding Scheduled SMS

1. **Create command:**

```php
// app/Console/Commands/SendScheduledSms.php
class SendScheduledSms extends Command
{
    public function handle()
    {
        $scheduled = ScheduledSms::where('send_at', '<=', now())
            ->where('sent', false)
            ->get();

        foreach ($scheduled as $sms) {
            $this->smsService->send($sms->to, $sms->body, $sms->user_id);
            $sms->update(['sent' => true]);
        }
    }
}
```

2. **Schedule in Kernel:**

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('sms:send-scheduled')
        ->everyMinute();
}
```

---

## Troubleshooting

### Common Issues

#### 1. Invalid Twilio Webhook Signature

**Symptoms:**
- Logs show: `Invalid Twilio webhook signature`
- Webhooks return 403 Forbidden

**Causes:**
- URL scheme mismatch (HTTP vs HTTPS)
- Incorrect auth token
- Proxy not trusted

**Solutions:**

```php
// Ensure proxy is trusted (bootstrap/app.php)
$middleware->trustProxies(at: '*', headers:
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_PROTO
);

// Verify .env has correct token
TWILIO_AUTH_TOKEN=your_actual_token

// Check ngrok is running with HTTPS
ngrok http 80
```

---

#### 2. Bulk SMS Not Sending

**Symptoms:**
- Click "Send SMS to Selected" - nothing happens
- No messages in queue
- No logs

**Causes:**
- Queue worker not running (if QUEUE_CONNECTION=database)
- JavaScript errors
- Livewire issues

**Solutions:**

```bash
# Check queue connection
grep QUEUE_CONNECTION .env

# If 'database', start worker
php artisan queue:work

# Or change to sync for development
sed -i 's/QUEUE_CONNECTION=database/QUEUE_CONNECTION=sync/' .env
php artisan config:clear
```

---

#### 3. Character Counter Not Updating

**Symptoms:**
- Counter shows "160 / 160 characters" always
- Doesn't update while typing

**Causes:**
- Missing `wire:model.live`
- JavaScript disabled
- Livewire not loaded

**Solutions:**

```blade
<!-- Ensure .live modifier is present -->
<textarea wire:model.live="body">
```

---

#### 4. Duplicate Answer Prevention Too Aggressive

**Symptoms:**
- Users can't answer same question on different days
- Always shows "Duplicate answer detected"

**Causes:**
- Using `question_id` instead of `sent_sms_question_id`
- Missing foreign key relationship

**Solutions:**

```php
// Check duplicate detection logic
$this->smsResponseRepository->hasAnswered($sentSmsQuestion->id);
// NOT: hasAnswered($smsQuestion->id, $phone)

// Verify migration ran
php artisan migrate:status | grep sent_sms_question_id
```

---

## Performance Optimization

### Database Indexing

**Critical indexes:**

```sql
-- sms_messages table
CREATE INDEX idx_sms_messages_user_created ON sms_messages(user_id, created_at);
CREATE INDEX idx_sms_messages_type ON sms_messages(message_type);
CREATE UNIQUE INDEX idx_sms_messages_sid ON sms_messages(sms_sid);

-- sent_sms_questions table
CREATE INDEX idx_sent_questions_phone_created ON sent_sms_questions(to, created_at);

-- sms_responses table
CREATE INDEX idx_responses_sent_question ON sms_responses(sent_sms_question_id);
```

### Query Optimization

**Eager loading:**

```php
// Bad: N+1 query problem
$messages = SmsMessage::all();
foreach ($messages as $message) {
    echo $message->user->name; // N queries
}

// Good: Eager load
$messages = SmsMessage::with('user')->get();
foreach ($messages as $message) {
    echo $message->user->name; // 1 query
}
```

### Caching

**Account balance:**

```php
// Already implemented - 5 minute cache
$balance = cache()->remember('twilio_account_balance', 300, function () {
    return $smsService->getAccountBalance();
});
```

**Add caching for questions:**

```php
// Cache questions for 1 hour
$questions = cache()->remember('sms_questions', 3600, function () {
    return SmsQuestion::all();
});
```

### Queue Optimization

**Batch processing:**

```php
// Process in chunks of 100
$users->chunk(100, function ($chunk) {
    $jobs = [];
    foreach ($chunk as $user) {
        $jobs[] = new SendBulkSmsJob($user->phone, $body, $userId);
    }
    Bus::batch($jobs)->dispatch();
});
```

**Delay between messages:**

```php
// Avoid rate limits - stagger sends
foreach ($users as $index => $user) {
    SendBulkSmsJob::dispatch($user, $body, $userId)
        ->delay(now()->addSeconds($index * 2));
}
```

---

## Security Best Practices

### 1. Webhook Signature Validation

✅ **Always verify** Twilio webhook signatures
✅ **Use HTTPS** in production
✅ **Log** invalid signature attempts

### 2. Input Validation

```php
// Validate phone numbers
$this->validate([
    'to' => 'required|regex:/^\+\d{1,15}$/',
]);

// Sanitize message body
$body = strip_tags($body);
$body = substr($body, 0, 160);
```

### 3. Rate Limiting

```php
// Prevent abuse
$middleware->throttleApi('sms-sending', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->id);
});
```

### 4. User Authorization

```php
// Ensure users can only see their own messages
SmsMessage::where('user_id', auth()->id())->get();
```

### 5. Environment Variables

✅ **Never commit** `.env` to git
✅ **Use** `.env.example` as template
✅ **Rotate** Twilio auth tokens periodically

---

## Monitoring & Logging

### Log Levels

```php
// Info: Normal operations
Log::info('SMS sent successfully', ['to' => $to, 'sid' => $sid]);

// Warning: Non-critical issues
Log::warning('Invalid webhook signature', ['ip' => $ip]);

// Error: Failures
Log::error('SMS send failed', ['error' => $e->getMessage()]);
```

### Key Metrics to Monitor

1. **SMS Send Success Rate**
   ```php
   $total = SmsMessage::count();
   $delivered = SmsMessage::where('status', 'delivered')->count();
   $rate = ($delivered / $total) * 100;
   ```

2. **Average Delivery Time**
   ```sql
   SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_seconds
   FROM sms_messages
   WHERE status = 'delivered';
   ```

3. **Failed Message Rate**
   ```php
   $failed = SmsMessage::where('status', 'undelivered')->count();
   ```

4. **Question Response Rate**
   ```php
   $sent = SentSmsQuestion::count();
   $responded = SmsResponse::distinct('sent_sms_question_id')->count();
   $rate = ($responded / $sent) * 100;
   ```

---

## API Reference

### TwilioSmsService

```php
/**
 * Send SMS synchronously
 *
 * @param string $to Phone number in E.164 format
 * @param string $body Message content (max 160 chars)
 * @param int|null $userId User ID for tracking
 * @return MessageInstance Twilio message object
 * @throws RuntimeException If send fails
 */
public function send($to, $body, ?int $userId = null): MessageInstance

/**
 * Queue SMS for async sending
 *
 * @param string $to Phone number
 * @param string $body Message content
 * @param int|null $userId User ID
 */
public function sendQueued(string $to, string $body, ?int $userId = null): void

/**
 * Get Twilio account balance
 *
 * @return array ['balance' => '15.42', 'currency' => 'USD', 'formatted' => '15.42 USD']
 */
public function getAccountBalance(): array
```

### SmsManagerService

```php
/**
 * Send question SMS with multiple choice options
 *
 * @param string $to Recipient phone
 * @param int|null $userId User sending
 */
public function sendQuestion(string $to, ?int $userId = null): void

/**
 * Validate and process incoming SMS answer
 *
 * @param IncomingSmsData $data Incoming SMS data
 * @return bool True if valid answer was saved
 */
public function hasAnsweredSentQuestion(IncomingSmsData $data): bool
```

---

## Changelog

### Version 1.0 (October 2025)

**Features:**
- ✅ Single SMS sending
- ✅ Bulk SMS with queue support
- ✅ Question-based SMS surveys
- ✅ Real-time character counter
- ✅ SMS history tracking
- ✅ Status tracking (Queued → Sent → Delivered)
- ✅ Account balance monitoring
- ✅ Webhook signature verification
- ✅ Rate limiting
- ✅ Duplicate answer prevention
- ✅ Auto-whitespace trimming

**Technical Improvements:**
- Repository pattern implementation
- DTO usage for type safety
- Comprehensive test coverage
- Multi-tenancy support
- Ngrok webhook support
- Proxy trust configuration

**Bug Fixes:**
- Fixed webhook signature validation with ngrok
- Fixed duplicate answer detection
- Fixed character counter real-time updates
- Fixed SMS history not showing questions
- Fixed bulk SMS not executing (queue issue)

---

**End of Developer Guide**
