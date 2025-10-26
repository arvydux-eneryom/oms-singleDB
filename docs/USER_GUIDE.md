# OMS User Guide

**Operation Management System (OMS)** - A complete multi-tenant platform for managing your business operations.

---

## User Types

- **System Administrators**: Create and manage tenant organizations (subdomains), manage system users
- **Tenant Users**: Work within your organization's isolated environment, manage customers and operations
- **Dual-Access Users**: Can access both system administration and tenant areas

---

## Getting Started

### Registration
1. Visit the registration page
2. Enter your company name, email, and password
3. You'll automatically receive a unique subdomain for your organization
4. Log in and start using the system

### Login & Security
- Sessions automatically log you out after 24 hours of inactivity
- You'll receive a 5-minute warning before logout—click "Stay Logged In" to continue
- Any activity (mouse, keyboard, clicks) resets the timer

---

## Key Features

### 1. **Subdomain Management** *(System Admins)*
Manage tenant organizations with unique subdomains:
- Create new subdomains (company name + 8-character identifier)
- View all subdomains with user counts and dates
- Edit or delete existing subdomains
- Manage user access per subdomain
- Use quick redirect for auto-login access

### 2. **User Management** *(System Admins)*
Control who can access the system:
- Create users with name, email, password, and role
- Assign users to one or multiple subdomains
- Edit user details and access rights
- Remove users or revoke subdomain access
- View paginated user list (5 per page)

### 3. **Customer Management** *(Tenant Users)*
Comprehensive customer record system:
- **Create & Edit**: Company info, status, phones, emails, contact persons
- **Addresses**: Service delivery and billing addresses
- **Search & Filter**: Real-time search by name/phone/email, filter by status
- **Bulk Actions**: Activate, deactivate, or delete multiple customers at once
- **Export**: Download customer data to CSV
- **Statistics**: View total, active, inactive customers and monthly counts
- **Contacts**: Multiple phones (Primary/Work/Home/Emergency) and emails per customer

### 4. **SMS Service** *(All Users)*
Send SMS messages through Twilio:
- **Single SMS**: Send to individual phone numbers (160 characters max)
- **Bulk SMS**: Send to multiple users simultaneously
- **Question SMS**: Send interactive surveys with multiple-choice options (1-4 choices)
- **History**: View 10 most recent messages with real-time status
- **Balance**: Monitor Twilio account balance
- **Status Tracking**: Queued → Sent → Delivered → Undelivered

*Note: Use international format for phone numbers (+country code). Only Active users receive SMS.*

### 5. **Profile Customization**
Personalize your workspace:
- Upload custom logos for system scope (main platform)
- Upload separate logos for tenant scope (subdomain)
- Supported: JPG, PNG, GIF, SVG (max 2 MB, recommended 500x500 pixels)
- Preview before saving

### 6. **Quick Navigation** *(System Admins)*
- Use "My Account" link in sidebar to switch from tenant view to system dashboard
- Opens in new tab—no need to log out and back in
- Manage all subdomains and system settings easily

---

## Tips for Success

✓ **Emails**: Always use lowercase
✓ **Subdomains**: Use alphanumeric characters only (a-z, 0-9), max 8 characters
✓ **SMS Messages**: Keep under 160 characters, use international phone format
✓ **Customer Names**: Company names must be unique
✓ **Security**: Stay active to avoid auto-logout; use the warning prompt to extend sessions
✓ **Data Safety**: Deleted customers are recoverable (soft delete); deleted subdomains are permanent

---

## Quick Reference

| What I Want To Do | Where To Go |
|-------------------|-------------|
| Create a new tenant organization | Subdomain Management → Create |
| Add system users | User Management → Create |
| Add/edit customers | Customer Management |
| Send SMS to users | SMS Service → Single/Bulk/Question |
| Upload my logo | Profile → Logo Upload |
| Check who has access to my subdomain | Subdomain Management → View Users |
| Export customer data | Customer Management → Export to CSV |
| Switch to system dashboard | Sidebar → My Account |

---

## Support

For issues or questions, contact your system administrator.

**Version**: 1.0
**Last Updated**: January 2025
