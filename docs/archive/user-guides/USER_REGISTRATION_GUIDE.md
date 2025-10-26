# User Registration Guide

## Overview

This guide explains how to register a new account in the OMS. The registration process creates a company account with full administrative access to your own tenant environment.

## Registration Process

### Step 1: Access Registration Page

Navigate to `/register` on the application URL.

### Step 2: Fill in Registration Form

Complete the registration form with the following information:

#### Company Name (Required)
- **Field:** Company name
- **Purpose:** This will be used as your company identifier and tenant name
- **Requirements:**
  - Maximum 255 characters
  - Cannot be empty
- **Example:** "Acme Corporation"

#### Email Address (Required)
- **Field:** Email address (this will be your super admin account)
- **Purpose:** Your login credential and primary contact email
- **Requirements:**
  - Must be a valid email format
  - Must be lowercase (e.g., `user@example.com`, not `USER@EXAMPLE.COM`)
  - Maximum 255 characters
  - Must be unique in the system
  - Cannot be empty
- **Example:** "admin@acme.com"

#### Password (Required)
- **Field:** Password
- **Purpose:** Secure your account access
- **Requirements:**
  - Must meet minimum password complexity rules
  - Typically requires minimum 8 characters
  - Must match the password confirmation field
  - Cannot be empty
- **Security Tip:** Use a strong password with a mix of letters, numbers, and symbols

#### Confirm Password (Required)
- **Field:** Confirm password
- **Purpose:** Verify you typed your password correctly
- **Requirements:**
  - Must exactly match the password field

### Step 3: Submit Registration

Click the **"Create account"** button to submit your registration.

## What Happens After Registration?

When you successfully register, the system automatically:

1. **Creates Your User Account**
   - Sets you up as a system administrator
   - Assigns you the "super-admin-for-tenant" role
   - Generates a unique system ID

2. **Creates Your Company**
   - Registers your company in the system
   - Links it to your user account

3. **Sets Up Your Tenant Environment**
   - Creates an isolated tenant space for your company
   - Generates a unique random subdomain (2-8 characters)
   - Example: `ab12cd.yourdomain.com` or `xyz789.yourdomain.com`

4. **Logs You In**
   - Automatically authenticates you
   - Redirects you to your unique subdomain
   - Takes you to your dashboard

## Your Subdomain

After registration, you'll access your account through a unique subdomain:

- **Format:** `[random-subdomain].yourdomain.com`
- **Example:** `a5bx2.localhost` or `qw7y.localhost`
- **Length:** 2-8 characters
- **Uniqueness:** Guaranteed to be unique across the system

**Bookmark this URL** for easy access to your tenant environment!

## Common Issues and Solutions

### Email Already Exists

**Error:** "The email has already been taken"

**Solution:** This email is already registered. Either:
- Use a different email address
- If you forgot your password, use the password reset feature
- Contact support if you believe this is an error

### Email Must Be Lowercase

**Error:** "The email must be lowercase"

**Solution:** Convert your email to all lowercase letters:
- ❌ Wrong: `Admin@Example.COM`
- ✅ Correct: `admin@example.com`

### Password Too Weak

**Error:** "The password must be at least X characters" or similar

**Solution:** Create a stronger password that meets the requirements:
- Use at least 8 characters (or system requirement)
- Include uppercase and lowercase letters
- Include numbers
- Include special characters

### Company Name Too Long

**Error:** "The name must not be greater than 255 characters"

**Solution:** Shorten your company name to 255 characters or less.

## Security Best Practices

1. **Strong Passwords**
   - Use unique passwords for each system
   - Never share your password
   - Change your password regularly

2. **Email Security**
   - Use a business email address
   - Ensure you have access to the email account
   - Keep your email account secure

3. **Account Protection**
   - Log out when finished
   - Don't save passwords on shared computers
   - Report suspicious activity immediately

## Next Steps After Registration

Once registered, you can:

1. **Access Your Dashboard**
   - View your company overview
   - Access all administrative features

2. **Customize Your Settings**
   - Update your profile
   - Configure company settings
   - Set up additional users

3. **Start Using the System**
   - As a super-admin, you have full access to all features
   - You can create additional users and assign roles
   - You can manage your company's data and operations

## Need Help?

If you encounter any issues during registration:

- Check this guide for common solutions
- Contact your system administrator
- Reach out to technical support
- Review the error message carefully for specific guidance

## Technical Details (For Administrators)

### Database Transactions
All registration operations are wrapped in a database transaction. If any step fails, all changes are automatically rolled back to maintain data integrity.

### Subdomain Generation
- Random alphanumeric string (2-8 characters)
- Collision detection with up to 10 retry attempts
- Falls back to timestamp-based generation if needed
- Checked against existing domains to ensure uniqueness

### User Roles
- New registrations receive the "super-admin-for-tenant" role
- This provides full administrative access within your tenant
- Additional users can be created with different permission levels

---

**Document Version:** 1.0
**Last Updated:** 2025-10-01
**For:** OMS Multi-Tenant System
