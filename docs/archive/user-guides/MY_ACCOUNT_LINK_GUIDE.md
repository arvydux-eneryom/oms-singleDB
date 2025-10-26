# My Account Link - User Guide

## Overview

The "My Account" link is a navigation feature that appears in the sidebar for super administrators who are working within a tenant environment. It provides quick access back to the main system dashboard.

## What is the "My Account" Link?

The "My Account" link is a special navigation item that allows system super administrators to quickly switch from a tenant's dashboard back to the main system dashboard where they can manage all tenants, users, roles, and permissions.

## When Does the Link Appear?

The "My Account" link is **only visible** when **both** of the following conditions are met:

1. **You are in a tenant context** - You are currently viewing a tenant's dashboard (subdomain)
2. **You are a system user** - Your account has the `is_system` flag set to true

### Visibility Examples

✅ **Link IS visible when:**
- You are logged in as a system user (user with `is_system = true`)
- You are currently on a tenant's subdomain (e.g., `tenant1.localhost:8000/dashboard`)

❌ **Link IS NOT visible when:**
- You are on the main system dashboard (not in a tenant context)
- You are a tenant-only user (user with `is_system = false`)
- You are not authenticated

## How to Use the "My Account" Link

1. **Locate the link** - Look in the sidebar above the user menu dropdown
2. **Click the link** - The link will open in a new browser tab (target="_blank")
3. **Access system dashboard** - You'll be taken to the main system dashboard where you can:
   - Manage all subdomains
   - Manage all users
   - Configure roles and permissions
   - Access system-wide settings

## Link Behavior

- **Icon:** Book with open text icon (📖)
- **Target:** Opens in a new browser tab
- **URL:** Points to the system dashboard (`http://localhost:8000/dashboard` or your configured system URL)
- **Purpose:** Quick navigation from tenant context back to system administration

## Use Cases

### Scenario 1: Managing Multiple Tenants
You're reviewing a tenant's setup on their subdomain and need to quickly switch back to the system dashboard to check another tenant's configuration. Simply click "My Account" to open the system dashboard in a new tab without losing your current tenant view.

### Scenario 2: System Administration Tasks
While working in a tenant environment, you realize you need to create a new user or modify permissions at the system level. The "My Account" link provides immediate access to the system administration area.

### Scenario 3: Cross-Context Work
You're troubleshooting an issue for a tenant and need to compare their settings with system-level configurations. Keep both tabs open using the "My Account" link for easy comparison.

## Technical Details

### User Requirements
- Must be a system user (`is_system` column set to `true` in the users table)
- Tenant-only users (`is_system = false`) will **not** see the link, regardless of their roles
- The visibility check uses the `isSystem()` method on the User model, which directly checks the `is_system` database column
- This approach is more reliable than role-based checking in multi-tenant contexts

### URL Configuration
The link dynamically uses your application's configured system URL from `config('app.url')`, ensuring it always points to the correct system dashboard regardless of which tenant subdomain you're currently viewing.

## Troubleshooting

### "Why don't I see the My Account link?"

Check the following:

1. **Are you in a tenant context?**
   - Check your URL - it should be on a subdomain (e.g., `tenant1.localhost`)
   - If you're on the main domain, the link won't appear

2. **Are you a system user?**
   - Verify your user account has `is_system = true` in the database
   - You can check this by asking your system administrator
   - Contact your system administrator if you need system user privileges

3. **Are you logged in?**
   - Ensure you're properly authenticated
   - Try logging out and logging back in
   - Clear your browser cache and Laravel's cache if needed

## Security Notes

- The link is only visible to system users (users with `is_system = true`)
- This prevents tenant-only users from accessing system-level administration functions
- The visibility check uses a direct database column check, not role-based permissions
- This approach is more reliable in multi-tenant contexts where role checking can be complex
- The link respects all authentication and authorization rules
- Session and authentication context is maintained when opening in a new tab
