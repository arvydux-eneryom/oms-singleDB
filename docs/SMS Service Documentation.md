# SMS Service Documentation

Complete documentation for the SMS service feature of the OMS application.

---

## 📚 Documentation Guides

### For Users & Managers
**[SMS User Guide](./SMS-USER-GUIDE.md)**

A comprehensive, non-technical guide covering:
- How to send single SMS messages
- How to send bulk SMS to multiple recipients
- How to send interactive question surveys
- Understanding SMS delivery status
- Viewing SMS history and tracking messages
- Character limits and best practices
- Troubleshooting common issues
- FAQ for end users

**Target Audience:** End users, managers, administrators, and anyone who will be using the SMS interface.

---

### For Developers
**[SMS Developer Guide](./SMS-DEVELOPER-GUIDE.md)**

A comprehensive technical guide covering:
- Architecture overview and design patterns
- Technology stack and dependencies
- Directory structure and file organization
- Core components (Services, Repositories, DTOs, Models)
- Database schema with relationships
- Detailed workflow diagrams
- API integration and webhook handling
- Queue system configuration
- Testing strategy and examples
- Deployment and configuration
- Security best practices
- Performance optimization
- Troubleshooting technical issues

**Target Audience:** Developers, DevOps engineers, technical leads, and anyone maintaining or extending the codebase.

---

## 🚀 Quick Start

### For Users

1. Navigate to **Integrations** → **SMS Manager**
2. Choose your action:
   - **Send Single SMS**: Enter phone number and message → Click "Send SMS"
   - **Send Bulk SMS**: Select users → Enter message → Click "Send SMS to Selected"
   - **Send Question**: Enter phone number → Click "Send Question"
3. Monitor delivery in SMS History panel

### For Developers

1. **Setup Environment:**
   ```bash
   cp .env.example .env
   # Add your Twilio credentials
   ```

2. **Configure Twilio:**
   ```env
   TWILIO_ACCOUNT_SID=ACxxxxxxxx
   TWILIO_AUTH_TOKEN=your_token
   TWILIO_PHONE_NUMBER=+447426914907
   ```

3. **Run Migrations:**
   ```bash
   php artisan migrate
   ```

4. **Setup Webhooks (Development):**
   ```bash
   ngrok http 80
   php artisan twilio:update-webhook
   ```

5. **Run Tests:**
   ```bash
   php artisan test --filter=Sms
   ```

---

## 📋 Features

### Core Features
- ✉️ **Single SMS**: Send individual messages to specific recipients
- 📢 **Bulk SMS**: Send messages to multiple recipients simultaneously
- 📋 **Question SMS**: Interactive surveys with multiple-choice options
- 📊 **SMS History**: Track sent messages and delivery status
- 💰 **Balance Monitoring**: Real-time Twilio account balance
- ✅ **Status Tracking**: Monitor message lifecycle (Queued → Sent → Delivered)

### Technical Features
- 🔐 **Webhook Security**: Signature verification for Twilio webhooks
- 🔄 **Queue Support**: Async processing for bulk operations
- 📈 **Rate Limiting**: Prevent abuse (10 SMS/minute per user)
- 🎯 **Smart Validation**: Auto-trim whitespace, validate phone formats
- 🔍 **Duplicate Prevention**: Users can't answer same question instance twice
- 📝 **Comprehensive Logging**: Track all operations for debugging
- 🧪 **Test Coverage**: Unit tests for core functionality

---

## 🏗️ Architecture

### Layered Architecture

```
┌─────────────────────────────────────────────────────────┐
│  Presentation Layer (Livewire, Controllers, Commands)   │
└───────────────────────┬─────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────┐
│  Service Layer (Business Logic, Twilio Integration)     │
└───────────────────────┬─────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────┐
│  Repository Layer (Data Access Abstraction)             │
└───────────────────────┬─────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────┐
│  Data Layer (MySQL Database)                            │
└─────────────────────────────────────────────────────────┘
```

### Design Patterns
- **Repository Pattern**: Clean data access
- **Service Layer Pattern**: Business logic separation
- **DTO Pattern**: Type-safe data transfer
- **Observer Pattern**: Livewire real-time updates
- **Queue Pattern**: Async job processing

---

## 🗂️ File Structure

```
oms/
├── app/
│   ├── DTOs/                    # Data Transfer Objects
│   ├── Http/
│   │   ├── Controllers/         # Webhook handlers
│   │   └── Middleware/          # Signature verification
│   ├── Jobs/                    # Queue jobs
│   ├── Livewire/                # UI components
│   ├── Models/                  # Database models
│   ├── Repositories/            # Data access layer
│   └── Services/                # Business logic
├── config/
│   └── services.php             # Twilio configuration
├── database/
│   └── migrations/              # Database schema
├── docs/                        # 📍 You are here
│   ├── README.md
│   ├── SMS-USER-GUIDE.md
│   └── SMS-DEVELOPER-GUIDE.md
├── resources/
│   └── views/livewire/          # UI templates
├── routes/
│   └── web.php                  # Route definitions
└── tests/
    └── Unit/Services/           # Service tests
```

---

## 🔧 Technology Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 11.x, PHP 8.3+ |
| **Frontend** | Livewire 3.x, Tailwind CSS |
| **Database** | MySQL 8.0+ |
| **Queue** | Sync (Dev) / Database (Prod) |
| **SMS Provider** | Twilio REST API |
| **Testing** | PHPUnit |
| **Development** | ngrok (Webhook tunneling) |

---

## 📊 Database Schema

### Core Tables

1. **sms_messages**: All SMS (incoming + outgoing)
2. **sms_questions**: Survey questions with options
3. **sent_sms_questions**: Track which questions were sent
4. **sms_responses**: User responses to questions

### Relationships

```
sms_questions (1) ──< (N) sent_sms_questions
                              │
                              │ (1)
                              │
                              ▼
                         (N) sms_responses
```

---

## 🚦 Status Flow

Every SMS goes through this lifecycle:

```
QUEUED → SENT → DELIVERED
```

- **Queued**: Accepted by Twilio, waiting to send
- **Sent**: Sent to carrier, en route to recipient
- **Delivered**: Successfully delivered to recipient
- **Undelivered**: Failed to deliver (error state)

---

## 🔐 Security

### Implemented Security Measures

1. ✅ **Webhook Signature Verification**: Validates all Twilio webhooks
2. ✅ **CSRF Protection**: Exempts webhook endpoints only
3. ✅ **Rate Limiting**: 10 SMS per minute per user
4. ✅ **Input Validation**: Phone format, message length
5. ✅ **SQL Injection Prevention**: Using Eloquent ORM
6. ✅ **Authorization**: Users see only their own SMS
7. ✅ **Environment Variables**: Secrets not in code

---

## 🧪 Testing

### Test Coverage

```bash
# Run all SMS tests
php artisan test --filter=Sms

# Run with coverage
XDEBUG_MODE=coverage php artisan test --coverage
```

### Test Files
- `SmsManagerServiceTest.php`: Business logic tests
- `TwilioSmsServiceTest.php`: Twilio integration tests

### Test Examples
- ✅ Sending single SMS
- ✅ Sending bulk SMS
- ✅ Question validation
- ✅ Answer processing
- ✅ Duplicate prevention
- ✅ Error handling

---

## 📈 Performance

### Optimization Strategies

1. **Database Indexing**: Optimized queries on sms_sid, user_id, created_at
2. **Eager Loading**: Prevent N+1 query problems
3. **Caching**: Account balance cached for 5 minutes
4. **Queue Processing**: Async bulk SMS operations
5. **Rate Limiting**: Prevent API abuse
6. **Lazy Loading**: Livewire pagination for history

---

## 🔍 Troubleshooting

### Quick Fixes

| Issue | Solution |
|-------|----------|
| Invalid webhook signature | Trust proxy headers in `bootstrap/app.php` |
| Bulk SMS not sending | Check `QUEUE_CONNECTION` in `.env` |
| Character counter stuck | Ensure `wire:model.live` in view |
| Status not updating | Verify webhook URLs in Twilio console |
| Duplicate answers blocked | Check `sent_sms_question_id` in logic |

See full guides for detailed troubleshooting.

---

## 📞 Support

### Getting Help

1. **Check Documentation**: User Guide or Developer Guide
2. **Review Logs**: `storage/logs/laravel.log`
3. **Check Twilio Console**: https://console.twilio.com
4. **Contact Support**: Reach out to system administrator

### Common Resources

- **Twilio Docs**: https://www.twilio.com/docs/sms
- **Laravel Docs**: https://laravel.com/docs
- **Livewire Docs**: https://livewire.laravel.com/docs

---

## 📝 Version History

### Version 1.0 (October 2025)

**Initial Release**
- Complete SMS sending functionality
- Question-based surveys
- Webhook integration
- Comprehensive documentation

---

## 🤝 Contributing

### For Developers

1. **Read Developer Guide**: Understand architecture first
2. **Follow Patterns**: Use existing patterns (Repository, Service, DTO)
3. **Write Tests**: Add tests for new features
4. **Update Docs**: Keep documentation current
5. **Code Style**: Run `./vendor/bin/pint` before committing

### Code Style

```bash
# Format code
./vendor/bin/pint

# Run tests
php artisan test

# Check coverage
XDEBUG_MODE=coverage php artisan test --coverage
```

---

## 📄 License

This documentation is part of the OMS project.

---

## 👥 Authors

- **SMS Service Implementation**: October 2025
- **Documentation**: October 2025

---

**Last Updated:** October 15, 2025
**Documentation Version:** 1.0
