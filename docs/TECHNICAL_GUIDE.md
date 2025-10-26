# OMS Technical Guide

**For Developers & Technical Staff**

---

## System Overview

**OMS (Operation Management System)** is a multi-tenant Laravel application using a single-database architecture with subdomain-based tenant isolation. Built with Laravel 11, PHP 8.3+, Livewire 3, and MySQL 8.

---

## Core Architecture

### Multi-Tenancy

**Pattern**: Single database with tenant isolation via `tenant_id` scoping

**Key Components**:
- **Tenant Identification**: Subdomain-based (e.g., `company1.localhost`)
- **Central Domain**: `localhost` for system administration
- **Package**: [Laravel Tenancy](https://tenancyforlaravel.com/)

**Database Tables**:
```
tenants         - Tenant organizations
domains         - Subdomain mappings (with system_id & user_id)
users           - Global users (flags: is_system, is_tenant)
tenant_user     - Pivot table for user-tenant relationships
```

**Isolation Mechanisms**:
1. `TenantScope` - Automatic query filtering by `tenant_id`
2. `InitializeTenancyByDomain` middleware
3. `PreventAccessFromCentralDomains` middleware
4. `system_id` filtering for central app queries

**Bootstrappers** (`config/tenancy.php`):
- `CacheTenancyBootstrapper` - Tagged cache per tenant
- `FilesystemTenancyBootstrapper` - Isolated file storage
- `QueueTenancyBootstrapper` - Tenant context in queued jobs

**Routes**:
- `routes/web.php` - Central app (subdomain management, system users)
- `routes/tenant.php` - Tenant app (tenant-specific features)

### User Types

1. **System Users** (`is_system = true`)
   - Create/manage subdomains
   - Unique `system_id` groups their tenants
   - Access: Central app routes

2. **Tenant Users** (`is_tenant = true`)
   - Belong to one or more tenants (via `tenant_user` pivot)
   - Access: Tenant subdomain routes only

3. **Dual Users** (both flags true)
   - Can access both central and tenant areas
   - Auto-created when system user creates first subdomain

---

## Key Features

### 1. Customer Management
**Location**: `app/Livewire/Customers/`

**Features**:
- Full CRUD with soft deletes
- Multiple phones/emails per customer (with types)
- Service delivery & billing addresses
- Search, filter, sort, bulk operations
- CSV export
- Statistics dashboard

**Models**: `Customer`, `CustomerPhone`, `CustomerEmail`, `ServiceAddress`, `BillingAddress`

### 2. SMS Integration (Twilio)
**Location**: `app/Services/`, `app/Livewire/Integrations/`

**Architecture Pattern**: Service Layer + Repository Pattern

**Core Services**:
- `TwilioSmsService` - Twilio API integration
- `SmsManagerService` - Question-based SMS workflows

**Features**:
- Single/bulk SMS sending (160 char limit)
- Question-based SMS with multiple-choice responses
- Real-time status tracking (Queued → Sent → Delivered)
- Webhook handling for status callbacks
- Account balance checking

**Repositories**:
- `SmsMessageRepository` - Message CRUD
- `SmsResponseRepository` - Response tracking
- `SentSmsQuestionRepository` - Question instances

**Jobs**:
- `SendSmsJob` - Single SMS async
- `SendBulkSmsJob` - Bulk sending

**Webhook**: `TwilioWebhookController` with signature verification middleware

### 3. Telegram Integration
**Location**: `app/Services/Telegram/`, `app/Livewire/Integrations/Telegram/`

**Architecture**: Service Layer (599-line component refactored into 4 services)

**Services**:
- `TelegramClientService` - MadelineProto lifecycle
- `TelegramAuthService` - QR/phone authentication
- `TelegramChannelService` - Channel operations
- `TelegramMessageService` - Messaging

**Security**: Random 64-char session identifiers (cryptographically secure)

**Session Management**:
- Database tracking (`telegram_sessions` table)
- 30-day expiration (configurable)
- Automated cleanup job
- 0700 file permissions

### 4. Security Features

**Session Security**:
- Auto-logout after 24h inactivity (configurable)
- 5-minute warning before logout
- Cross-tab activity synchronization
- Secure session paths for Telegram integration

**Access Control**:
- Spatie Laravel Permission (tenant-scoped roles)
- Middleware: `auth`, `verified`, `role`, `permission`
- Tenant ownership validation

**Data Isolation**:
- Automatic tenant scoping via `TenantScope`
- `system_id` filtering in central app
- Email uniqueness per tenant (not global)

### 5. Custom Features

**Logo Upload**:
- Separate logos for system & tenant scopes
- Supports: JPG, PNG, GIF, SVG (max 2MB)
- Media library integration

**My Account Link**:
- System admins: Quick navigation from tenant to central dashboard
- Opens in new tab, maintains authentication

---

## Development Setup

### Requirements
- PHP 8.3+
- MySQL 8.0+
- Composer
- Node.js & NPM
- Redis (optional, for queues)

### Installation
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
```

### Configuration

**Twilio** (`.env`):
```env
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_PHONE_NUMBER=your_number
TWILIO_WEBHOOK_URL=https://your-app.test/twilio/webhook
```

**Telegram** (`.env`):
```env
TELEGRAM_API_ID=your_api_id
TELEGRAM_API_HASH=your_api_hash
TELEGRAM_SESSION_EXPIRES_DAYS=30
```

**Tenancy** (`config/tenancy.php`):
- Change `central_domains` for production
- Configure bootstrappers as needed

### Local Development

**Subdomains**:
Use `.test` domains with Laravel Herd or Valet:
- Central: `http://oms.test`
- Tenant: `http://company1.oms.test`

**Webhooks** (Twilio):
```bash
ngrok http 80
# Update webhook URL: php artisan twilio:update-webhook
```

---

## Code Organization

### Design Patterns

1. **Service Layer Pattern**
   - Business logic extracted from controllers/components
   - Reusable across contexts (Livewire, API, Jobs)
   - Example: `TelegramAuthService`, `SmsManagerService`

2. **Repository Pattern**
   - Data access abstraction
   - Clean separation from business logic
   - Example: `SmsMessageRepository`, `TelegramSessionRepository`

3. **DTO Pattern**
   - Type-safe data transfer
   - Example: `IncomingSmsData`, `OutgoingSmsStatusData`

### Livewire Components

**Location**: `app/Livewire/`

**Key Components**:
- `Customers/*` - Customer management (Index, Create, Edit, Delete)
- `Users/*` - User management
- `Subdomains/*` - Subdomain management (Index, Create, Edit, Redirect)
- `Integrations/SmsManager` - SMS interface
- `Integrations/Telegram/Index` - Telegram interface

**Best Practices**:
- Delegate to services for business logic
- Keep components focused on UI state
- Use form validation
- Real-time updates with Livewire events

### Testing

**Location**: `tests/`

**Test Suites**:
- `Unit/` - Service & repository tests
- `Feature/` - Livewire component & integration tests

**Run Tests**:
```bash
php artisan test                    # All tests
php artisan test --testsuite=Unit   # Unit only
php artisan test --filter=Sms       # Specific feature
```

**Coverage** (requires Xdebug):
```bash
XDEBUG_MODE=coverage php artisan test --coverage
```

**Testing Tenancy**:
```php
// Switch tenant context in tests
tenancy()->initialize($tenant);

// Assert tenant context
$this->assertEquals($tenant->id, tenant('id'));
```

---

## Database

### Migrations

**SMS Tables**:
- `sms_messages` - All SMS (incoming/outgoing)
- `sms_questions` - Question templates
- `sent_sms_questions` - Question instances sent to users
- `sms_responses` - User responses

**Telegram Tables**:
- `telegram_sessions` - Session tracking with random identifiers

**Customer Tables**:
- `customers` - Base customer data
- `customer_phones` - Multiple phones with types
- `customer_emails` - Multiple emails with types
- `service_addresses` - Delivery locations
- `billing_addresses` - Billing info

### Seeders

**Location**: `database/seeders/`

**Key Seeders**:
- `UserSeeder` - Super admin (`super-admin@example.com` / `password`)
- `RoleSeeder` - Default roles
- `PermissionSeeder` - Default permissions
- `SmsQuestionSeeder` - Sample SMS questions

**Run Seeders**:
```bash
php artisan db:seed
php artisan db:seed --class=SmsQuestionSeeder
```

---

## Security

### Authentication
- Laravel Breeze for auth scaffolding
- Email verification required
- Password reset functionality

### Authorization
- Spatie Laravel Permission
- Tenant-scoped roles/permissions
- Middleware: `role:admin`, `permission:view-customers`

### Session Security
- Predictable path prevention (Telegram: random 64-char identifiers)
- File permissions: 0700 (owner-only)
- Database session tracking
- Automated expiration cleanup

### Webhook Security
- Twilio: Signature verification via `VerifyTwilioWebhook` middleware
- IP whitelisting (optional)

---

## Monitoring & Debugging

### Logging
Laravel logging configured in `config/logging.php`:
- Default: `storage/logs/laravel.log`
- Channels: `single`, `daily`, `stack`

**Service Logs**:
```php
Log::info('SMS sent', ['to' => $to, 'sid' => $sid]);
Log::error('Telegram auth failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
```

### Error Monitoring
BugSnag integration available (see archived docs)

### Performance
- Use `php artisan optimize` for production
- Enable cache drivers (Redis recommended)
- Queue long-running tasks (bulk SMS, etc.)

---

## Deployment

### Production Checklist
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure proper database credentials
- [ ] Set up queue workers: `php artisan queue:work --queue=default,bulk-sms`
- [ ] Configure scheduler: Add cron `* * * * * cd /path && php artisan schedule:run`
- [ ] Set up HTTPS/SSL
- [ ] Configure central domain in `config/tenancy.php`
- [ ] Set secure file permissions (0644 files, 0755 dirs)
- [ ] Enable caching: `php artisan config:cache && php artisan route:cache`

### Queue Workers
For SMS and async tasks:
```bash
php artisan queue:work --tries=3 --timeout=60
```

### Scheduled Tasks
Daily cleanup for expired Telegram sessions (runs at 2:00 AM):
```php
// routes/console.php
Schedule::job(new CleanupExpiredTelegramSessions)->daily()->at('02:00');
```

---

## Extending the System

### Adding New SMS Provider
1. Implement `SmsServiceInterface`
2. Bind in `AppServiceProvider`:
   ```php
   $this->app->bind(SmsServiceInterface::class, YourSmsService::class);
   ```

### Adding New Tenant Feature
1. Create migration with `tenant_id` column
2. Apply `TenantScope` to model
3. Create Livewire component
4. Add route to `routes/tenant.php`
5. Test tenant isolation

### Adding New Permission
1. Add to `PermissionSeeder`
2. Run: `php artisan db:seed --class=PermissionSeeder`
3. Assign to roles
4. Protect routes/components with middleware

---

## Troubleshooting

**Tenancy Issues**:
```bash
# Clear tenant cache
php artisan tenancy:clear-cache

# Check current tenant
php artisan tinker
>>> tenant()
```

**SMS Not Sending**:
- Check `.env` Twilio credentials
- Verify phone format (+country code)
- Check logs: `tail -f storage/logs/laravel.log`
- Test Twilio account: `app(TwilioSmsService::class)->getAccountBalance()`

**Telegram Sessions**:
```bash
# Cleanup lock files
php artisan telegram:cleanup --force

# Check sessions
sqlite3 database/database.sqlite "SELECT * FROM telegram_sessions;"
```

**Permission Denied** (Telegram):
```bash
chmod -R 775 storage
chown -R www-data:www-data storage  # or your web server user
```

---

## Code Quality

### Formatting
Laravel Pint:
```bash
./vendor/bin/pint                 # Format all
./vendor/bin/pint app/Services    # Format specific dir
./vendor/bin/pint --test          # Check without fixing
```

### Static Analysis
PHPStan (if configured):
```bash
./vendor/bin/phpstan analyse
./vendor/bin/phpstan analyse app/Services --level=5
```

---

## Key Files Reference

| File | Purpose |
|------|---------|
| `config/tenancy.php` | Tenancy configuration |
| `config/services.php` | External service credentials (Twilio, Telegram) |
| `app/Providers/TenancyServiceProvider.php` | Tenancy event listeners |
| `app/Models/Scopes/TenantScope.php` | Automatic tenant filtering |
| `routes/web.php` | Central app routes |
| `routes/tenant.php` | Tenant app routes |
| `.env` | Environment configuration |

---

## Useful Commands

```bash
# User management
php artisan tinker
>>> User::where('email', 'user@example.com')->first()->assignRole('admin')

# Telegram session cleanup
php artisan telegram:cleanup --force

# Twilio webhook update
php artisan twilio:update-webhook

# Check tenants
php artisan tinker
>>> Tenant::with('domains', 'users')->get()

# Clear caches
php artisan optimize:clear

# Run tests with coverage
XDEBUG_MODE=coverage php artisan test --coverage-html coverage/
```

---

## Resources

- **Laravel Docs**: https://laravel.com/docs
- **Laravel Tenancy**: https://tenancyforlaravel.com/
- **Livewire**: https://livewire.laravel.com/
- **Spatie Permission**: https://spatie.be/docs/laravel-permission/
- **Twilio Docs**: https://www.twilio.com/docs/sms
- **MadelineProto**: https://docs.madelineproto.xyz/

---

**Version**: 1.0
**Last Updated**: January 2025
**Maintained By**: OMS Development Team
