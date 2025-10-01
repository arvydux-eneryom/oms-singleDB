# My Account Link - User Guide

## Overview

The "My Account" link is a navigation feature that appears in the sidebar for super administrators who are working within a tenant environment. It provides quick access back to the main system dashboard.

## What is the "My Account" Link?

The "My Account" link is a special navigation item that allows system super administrators to quickly switch from a tenant's dashboard back to the main system dashboard where they can manage all tenants, users, roles, and permissions.

## When Does the Link Appear?

The "My Account" link is **only visible** when **both** of the following conditions are met:

1. **You are in a tenant context** - You are currently viewing a tenant's dashboard (subdomain)
2. **You have the super-admin-for-system role** - Your account has system-level super administrator privileges

### Visibility Examples

✅ **Link IS visible when:**
- You are logged in as a system super administrator
- You are currently on a tenant's subdomain (e.g., `tenant1.localhost:8000/dashboard`)

❌ **Link IS NOT visible when:**
- You are on the main system dashboard (not in a tenant context)
- You are a tenant administrator without system-level privileges
- You are a regular user with any other role (admin-for-tenant, manager, site-manager, foreman)

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

### Role Requirements
- Must have the `super-admin-for-system` role
- The `super-admin-for-tenant` role will **not** show the link
- Regular tenant roles (admin, manager, foreman) will **not** show the link

### URL Configuration
The link dynamically uses your application's configured system URL from `config('app.url')`, ensuring it always points to the correct system dashboard regardless of which tenant subdomain you're currently viewing.

## Troubleshooting

### "Why don't I see the My Account link?"

Check the following:

1. **Are you in a tenant context?**
   - Check your URL - it should be on a subdomain (e.g., `tenant1.localhost`)
   - If you're on the main domain, the link won't appear

2. **Do you have the correct role?**
   - Verify you have `super-admin-for-system` role (not `super-admin-for-tenant`)
   - Contact your system administrator if you need this role

3. **Are you logged in?**
   - Ensure you're properly authenticated
   - Try logging out and logging back in

## Security Notes

- The link is only visible to users with system-level super administrator privileges
- This prevents regular tenant users from accessing system-level administration functions
- The link respects all authentication and authorization rules
- Session and authentication context is maintained when opening in a new tab
