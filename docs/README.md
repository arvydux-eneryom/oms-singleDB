# OMS Documentation

This directory contains detailed technical documentation for various features and implementations in the OMS project.

## Feature Guides

### Customer Management
- **[Customer Management Manual](CUSTOMER_MANAGEMENT_MANUAL.md)** - Complete guide to managing customers, contacts, and addresses

### User Management
- **[User Management Guide](USER_MANAGEMENT_GUIDE.md)** - Managing users, roles, and permissions
- **[User Registration Guide](USER_REGISTRATION_GUIDE.md)** - User registration and onboarding process
- **[My Account Link Guide](MY_ACCOUNT_LINK_GUIDE.md)** - User profile and settings management
- **[Logo Upload Manual](USER_MANUAL_LOGO_UPLOAD.md)** - Company logo upload feature

### Subdomain Management
- **[Subdomain Management Guide](SUBDOMAIN_MANAGEMENT_GUIDE.md)** - Creating and managing tenant subdomains

## Integration Documentation

### SMS Integration (Twilio)
- **[SMS User Guide](SMS-USER-GUIDE.md)** - End-user guide for SMS features
- **[SMS Developer Guide](SMS-DEVELOPER-GUIDE.md)** - Technical implementation details for SMS service
- **[SMS Service Documentation](SMS%20Service%20Documentation.md)** - Complete SMS service architecture

### Telegram Integration
- **[Telegram QR Login Fix](TELEGRAM_QR_LOGIN_FIX.md)** - QR code authentication implementation
- **[Telegram Session Security Upgrade](TELEGRAM_SESSION_SECURITY_UPGRADE.md)** - Session management and security enhancements
- **[BugSnag Telegram Monitoring Guide](BUGSNAG_TELEGRAM_MONITORING_GUIDE.md)** - Error monitoring for Telegram integration

## Security Features
- **[Auto Logout Feature](AUTO_LOGOUT_FEATURE.md)** - Automatic logout on inactivity implementation
- **[Secure Session Implementation](SECURE_SESSION_IMPLEMENTATION_COMPLETE.md)** - Session security and management

## Technical Implementation Notes

### Error Monitoring
- **[BugSnag Integration](BUGSNAG_INTEGRATION.md)** - Error tracking and monitoring setup

### Architecture & Refactoring
- **[Service Layer Refactoring](SERVICE_LAYER_REFACTORING.md)** - Service layer architecture and patterns

### Testing
- **[Test Improvements](TEST_IMPROVEMENTS.md)** - Testing strategy and improvements

## Quick Links

- [Main README](../README.md) - Project overview and setup
- [Deployment Guide](../DEPLOYMENT.md) - Production deployment instructions

## Documentation Structure

```
docs/
├── README.md                                    # This file
├── AUTO_LOGOUT_FEATURE.md                       # Security: Auto-logout
├── BUGSNAG_INTEGRATION.md                       # Monitoring: BugSnag setup
├── BUGSNAG_TELEGRAM_MONITORING_GUIDE.md        # Telegram error monitoring
├── CUSTOMER_MANAGEMENT_MANUAL.md               # Feature: Customers
├── MY_ACCOUNT_LINK_GUIDE.md                    # Feature: User profile
├── SECURE_SESSION_IMPLEMENTATION_COMPLETE.md   # Security: Sessions
├── SERVICE_LAYER_REFACTORING.md                # Architecture: Services
├── SMS Service Documentation.md                # Integration: SMS overview
├── SMS-DEVELOPER-GUIDE.md                      # Integration: SMS (dev)
├── SMS-USER-GUIDE.md                           # Integration: SMS (user)
├── SUBDOMAIN_MANAGEMENT_GUIDE.md               # Feature: Subdomains
├── TELEGRAM_QR_LOGIN_FIX.md                    # Integration: Telegram auth
├── TELEGRAM_SESSION_SECURITY_UPGRADE.md        # Integration: Telegram security
├── TEST_IMPROVEMENTS.md                         # Testing: Strategy
├── USER_MANAGEMENT_GUIDE.md                     # Feature: Users
├── USER_MANUAL_LOGO_UPLOAD.md                  # Feature: Logo upload
└── USER_REGISTRATION_GUIDE.md                   # Feature: Registration
```

## Contributing to Documentation

When adding new documentation:
1. Create a descriptive filename in UPPER_CASE with underscores
2. Include a clear title and table of contents
3. Add the document to this index under the appropriate category
4. Follow the existing documentation style and formatting
