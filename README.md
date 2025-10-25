# OMS - Operation Management System

A modern multi-tenant operation management system built with Laravel 12, Livewire, and Tailwind CSS. This application provides comprehensive customer management, SMS integration, Telegram integration, and role-based access control across multiple isolated tenant environments.

## Features

### Core Features
- **Multi-Tenancy**: Complete tenant isolation using single database architecture with automatic subdomain routing
- **Customer Management**: Full CRUD operations with contacts, addresses, phones, and emails
- **User Management**: Role-based access control with permissions management
- **Subdomain Management**: Create and manage isolated tenant environments

### Integrations
- **Telegram Integration**:
  - QR code login authentication
  - Secure session management
  - Channel creation and management
  - Comprehensive error monitoring with BugSnag

- **Twilio SMS Integration**:
  - Send SMS messages to customers
  - Automated survey questions
  - Incoming/outgoing message tracking
  - Webhook support for message status updates

- **Google Maps API**: Address validation and geocoding

### Security & Features
- **Auto-logout on Inactivity**: Configurable session timeout with warning
- **Logo Upload**: Company branding support
- **Activity Logging**: Track all important user actions
- **Media Management**: Spatie Media Library integration
- **Error Monitoring**: BugSnag integration for production error tracking

## Technology Stack

- **Backend**: Laravel 12
- **Frontend**: Livewire Volt, Livewire Flux
- **Styling**: Tailwind CSS 4
- **Database**: SQLite (development) / MySQL/PostgreSQL (production)
- **Multi-Tenancy**: Stancl Tenancy
- **Permissions**: Spatie Laravel Permission
- **Testing**: PHPUnit with 526+ tests

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQLite (development) or MySQL/PostgreSQL (production)
- Redis (optional, for caching and queues)

### PHP Extensions
- ext-dom
- ext-libxml
- PDO extension for your database

## Installation

### 1. Clone the Repository
```bash
git clone <your-repository-url>
cd oms
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Environment Variables

Edit `.env` and configure the following:

#### Database
```env
DB_CONNECTION=sqlite
# For MySQL/PostgreSQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=your_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password
```

#### Telegram Integration (Optional)
Get API credentials from https://my.telegram.org/apps
```env
TELEGRAM_API_ID=your_api_id
TELEGRAM_API_HASH=your_api_hash
TELEGRAM_SESSION_DIR=telegram/sessions
TELEGRAM_SESSION_EXPIRES_DAYS=30
```

#### Twilio SMS Integration (Optional)
Get credentials from https://console.twilio.com/
```env
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=your_twilio_phone_number
TWILIO_SMS_COMMON_URL=https://yourdomain.com
```

#### Google Maps (Optional)
Get API key from https://console.cloud.google.com/
```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key
```

#### BugSnag Error Monitoring (Optional)
```env
BUGSNAG_API_KEY=your_bugsnag_api_key
```

### 5. Database Setup
```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# (Optional) Seed with sample data
php artisan db:seed
```

### 6. Build Assets
```bash
npm run build
# or for development
npm run dev
```

## Running the Application

### Development Server
```bash
php artisan serve
```

Visit: http://localhost:8000

### Using Laravel Herd (Recommended for Mac)
If you're using Laravel Herd, the application will be automatically available at:
- http://oms.test

### Running All Services Concurrently
```bash
composer dev
```

This runs: server, queue worker, logs (Pail), and Vite simultaneously.

### Background Queue Worker
```bash
php artisan queue:listen --tries=1
```

## Testing

### Run All Tests
```bash
composer test
# or
./test
```

### Run Specific Test Suite
```bash
./test --testsuite=Unit
./test --testsuite=Feature
```

### Run With Test Names
```bash
./test --testdox
```

### Current Test Coverage
- **526 tests** with **1231 assertions**
- Comprehensive feature and unit test coverage
- 14 skipped tests (documented edge cases)

## Code Quality

### Linting
```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse
```

### JavaScript/Blade Linting
```bash
# Check all
npm run lint

# Fix formatting
npm run format
```

## Multi-Tenancy

### Subdomain Structure
- **Central Domain**: `oms.test` (or your configured domain)
- **Tenant Subdomains**: `{subdomain}.oms.test`

### Creating a Tenant
1. Register a new user
2. System automatically creates a subdomain
3. User is redirected to their tenant environment

### Automatic Subdomain Redirection
- Single subdomain: Auto-redirect to that subdomain
- Multiple subdomains: Show dashboard with subdomain selector
- Zero subdomains: Redirect to subdomain creation page

## Key Commands

### Twilio Webhook Management
```bash
# Auto-detect and update ngrok URL
php artisan twilio:update-webhook

# Manually specify URL
php artisan twilio:update-webhook https://xxxx.ngrok-free.app
```

### Clear Caches
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Project Structure

```
app/
├── Console/Commands/      # Artisan commands
├── DTOs/                  # Data Transfer Objects
├── Http/
│   ├── Controllers/       # Traditional controllers
│   ├── Middleware/        # Custom middleware
│   └── Requests/          # Form requests
├── Livewire/             # Livewire components
│   ├── Actions/
│   ├── Integrations/
│   ├── Permissions/
│   ├── Roles/
│   ├── Subdomains/
│   ├── Tenancy/
│   └── Users/
├── Models/               # Eloquent models
├── Repositories/         # Repository pattern
├── Services/             # Business logic services
└── Telegram/             # Telegram integration

resources/
├── css/
├── js/
└── views/
    ├── components/
    ├── layouts/
    └── livewire/

tests/
├── Feature/             # Feature tests
└── Unit/               # Unit tests
```

## Security

### Environment Variables
- Never commit `.env` file
- Keep API keys and secrets secure
- Use `.env.example` as template

### Authentication
- Auto-logout after configurable inactivity period
- Session management across tenant boundaries
- Secure password hashing with bcrypt

### Webhooks
- Twilio webhook signature verification
- CSRF protection for standard routes

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions.

### Quick Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database
- [ ] Set up queue workers
- [ ] Configure scheduled tasks (cron)
- [ ] Set up SSL certificates
- [ ] Configure proper file permissions
- [ ] Set up error monitoring (BugSnag)
- [ ] Configure backup strategy

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Run `composer test` before committing
- Use `composer lint` to check code quality

## Documentation

Comprehensive documentation is available in the [`docs/`](docs/) directory:

### Feature Guides
- [Customer Management Manual](docs/CUSTOMER_MANAGEMENT_MANUAL.md)
- [User Management Guide](docs/USER_MANAGEMENT_GUIDE.md)
- [Subdomain Management Guide](docs/SUBDOMAIN_MANAGEMENT_GUIDE.md)
- [Logo Upload Guide](docs/USER_MANUAL_LOGO_UPLOAD.md)

### Integration Guides
- [SMS User Guide](docs/SMS-USER-GUIDE.md)
- [SMS Developer Guide](docs/SMS-DEVELOPER-GUIDE.md)
- [Telegram QR Login](docs/TELEGRAM_QR_LOGIN_FIX.md)
- [Telegram Session Security](docs/TELEGRAM_SESSION_SECURITY_UPGRADE.md)

### Security & Technical
- [Auto Logout Feature](docs/AUTO_LOGOUT_FEATURE.md)
- [Secure Session Implementation](docs/SECURE_SESSION_IMPLEMENTATION_COMPLETE.md)
- [Service Layer Refactoring](docs/SERVICE_LAYER_REFACTORING.md)
- [Test Improvements](docs/TEST_IMPROVEMENTS.md)

See [docs/README.md](docs/README.md) for a complete documentation index.

## License

This project is licensed under the MIT License.

## Support

For issues and questions:
- Create an issue in the GitHub repository
- Check existing documentation in the [`docs/`](docs/) directory

## Changelog

See commit history for detailed changes.

### Recent Major Features
- BugSnag error monitoring integration
- Auto-logout after inactivity
- Redesigned welcome page
- Customer management system
- SMS service with Twilio
- Telegram integration
- Multi-tenant architecture

---

Built with Laravel, Livewire, and modern web technologies.
