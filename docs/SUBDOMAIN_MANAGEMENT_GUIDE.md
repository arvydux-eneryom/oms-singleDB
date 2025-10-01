# Subdomain Management Guide

## Overview

The Subdomain Management feature allows system administrators to create and manage separate tenant environments within the Operation Management System (OMS). Each subdomain represents a different company or organization with its own isolated space, users, and data.

**Who is this for?**
- System Administrators who need to set up new client organizations
- Managers who oversee multiple tenant accounts

## Prerequisites

- You must be logged in as a system administrator
- You must have `is_system` permission enabled on your account

## Managing Subdomains

### Accessing Subdomain Management

1. Log in to your OMS system account
2. Navigate to **Subdomains** from the main menu
3. You'll see a list of all subdomains associated with your system

### Viewing Subdomain List

The subdomain list shows:
- **Company Name** - The organization's display name
- **Subdomain** - The unique subdomain identifier (e.g., `acme` in `acme.yourdomain.com`)
- **Full Domain** - The complete URL for accessing the tenant
- **User Count** - Number of users assigned to this tenant
- **Created Date** - When the subdomain was created

**Note:** You can only see subdomains that belong to your system. Subdomains from other systems are automatically filtered out.

## Creating a New Subdomain

### Step 1: Navigate to Create Page

1. From the Subdomain list, click **"Create New Subdomain"** or **"Add Subdomain"** button
2. You'll be taken to the subdomain creation form

### Step 2: Fill in Required Information

#### Company Name
- **Required:** Yes
- **Maximum Length:** 255 characters
- **Description:** The full name of the organization/company
- **Example:** ✅ `Acme Corporation`, `Smith & Associates`

#### Subdomain
- **Required:** Yes
- **Format:** Alphanumeric characters only (a-z, A-Z, 0-9)
- **Maximum Length:** 8 characters
- **Must be unique:** Cannot already exist in the system
- **Description:** The unique identifier that will appear in the URL

**Valid Examples:**
- ✅ `acme`
- ✅ `smith123`
- ✅ `ABC456`

**Invalid Examples:**
- ❌ `acme-corp` (contains hyphen)
- ❌ `acme_co` (contains underscore)
- ❌ `acme corp` (contains space)
- ❌ `acme@123` (contains special character)
- ❌ `verylong123` (exceeds 8 characters)

### Step 3: Submit Creation

1. Review the information you entered
2. Click **"Create Subdomain"** or **"Save"** button
3. Wait for confirmation message

### What Happens After Creation

When you create a subdomain, the system automatically:

1. ✅ Creates a new tenant record for the organization
2. ✅ Generates a unique domain (e.g., `acme.yourdomain.com`)
3. ✅ Associates the domain with your system ID
4. ✅ Links your user account to the new tenant
5. ✅ Marks your account as a tenant user (`is_tenant = true`)
6. ✅ Redirects you back to the subdomain list

**Success Message:** "Subdomain successfully created."

## Editing an Existing Subdomain

### Step 1: Access Edit Page

1. From the Subdomain list, locate the subdomain you want to edit
2. Click the **"Edit"** button or pencil icon
3. The edit form will load with current information

### Step 2: Update Information

You can modify:
- **Company Name** - Update the organization's display name
- **Subdomain** - Change the subdomain identifier

**Important:** Due to system validation, you must change the subdomain text when updating. You cannot save without modifying the subdomain value.

### Step 3: Manage Users

On the edit page, you can also:
- View all users assigned to this tenant
- **Revoke User Access** - Click "Remove" or "Revoke" to unassign a user from the tenant

### Step 4: Save Changes

Two save options:
1. **"Save"** - Saves changes and stays on the edit page
2. **"Save & Close"** - Saves changes and returns to the subdomain list

### What Happens After Update

When you update a subdomain:

1. ✅ Domain name is updated (e.g., from `oldname.yourdomain.com` to `newname.yourdomain.com`)
2. ✅ Tenant name is synchronized with the new company name
3. ✅ All existing user relationships are maintained
4. ✅ The subdomain remains accessible at the new URL

**Success Message:** "Subdomain successfully updated."

## Deleting a Subdomain

### ⚠️ Warning: Deletion is Permanent

Deleting a subdomain will:
- Remove the subdomain and domain records
- Delete the associated tenant
- Remove all user-tenant relationships
- This action **cannot be undone**

### Step 1: Locate Subdomain

1. From the Subdomain list, find the subdomain to delete
2. Review the subdomain information carefully

### Step 2: Confirm Deletion

1. Click the **"Delete"** button or trash icon
2. Confirm the deletion when prompted
3. Wait for the system to process

### What Happens After Deletion

The system will:

1. ✅ Detach all users from the tenant
2. ✅ Clean up the `tenant_user` relationship table
3. ✅ Delete the tenant record
4. ✅ Delete the domain record
5. ✅ Show confirmation message

**Success Message:** "Subdomain and all it's users successfully deleted."

**Note:** The actual user accounts are not deleted, only their association with this tenant.

## Subdomain Redirect Feature

### Quick Access to Tenants

The Redirect feature provides a quick way to access tenant environments:

1. Navigate to **Subdomains → Redirect**
2. The system automatically redirects you to the most recently created subdomain
3. You'll be logged in with a secure auto-login token

**How it works:**
- System retrieves all your subdomains
- Orders them by creation date (newest first)
- Redirects to the first subdomain
- Generates a temporary signed URL valid for 10 minutes

## Common Issues and Solutions

### Error: "The subdomain has already been taken"

**Error:** When creating or editing, you see: "The subdomain has already been taken."

**Cause:** Another tenant is already using this subdomain identifier.

**Solution:**
1. Choose a different subdomain name
2. Add numbers or variations (e.g., `acme2`, `acme123`)
3. Check with your team to ensure uniqueness

### Error: "The subdomain format is invalid"

**Error:** Validation fails with: "The subdomain format is invalid."

**Cause:** The subdomain contains invalid characters.

**Solution:**
1. Remove all special characters (-, _, @, etc.)
2. Remove spaces
3. Use only letters (a-z, A-Z) and numbers (0-9)
4. Ensure length is 8 characters or less

### Error: "The subdomain field is required"

**Error:** "The subdomain field is required."

**Cause:** You left the subdomain field empty.

**Solution:**
Fill in both Company Name and Subdomain fields before submitting.

### Cannot Save Edit Without Changing Subdomain

**Issue:** When editing, you must change the subdomain even if you only want to update the company name.

**Cause:** Current validation requires the subdomain to be different from existing value.

**Workaround:**
1. Change the subdomain to a new value
2. Or create a support ticket if you need to keep the same subdomain

## Best Practices

### Subdomain Naming

✅ **Do:**
- Use short, memorable names
- Include company/client identifier
- Use lowercase for consistency
- Keep it professional and brand-appropriate
- Example: `acme`, `smith`, `techco`

❌ **Don't:**
- Use offensive or inappropriate terms
- Make it overly complex
- Use confusing abbreviations
- Example: `a1b2c3d4` (hard to remember)

### Security Considerations

🔒 **Important Security Notes:**

1. **User Access Control**
   - Regularly review user assignments
   - Remove users who no longer need access
   - Each tenant has isolated data

2. **Subdomain Visibility**
   - Subdomains are system-scoped
   - You only see subdomains for your system
   - Other system administrators cannot see your subdomains

3. **Auto-Login Tokens**
   - Redirect tokens expire after 10 minutes
   - Tokens are cryptographically signed
   - Each token is single-use and user-specific

### Planning Before Creation

Before creating a subdomain:

1. ✅ Confirm the company/organization name
2. ✅ Choose an appropriate subdomain identifier
3. ✅ Verify the subdomain isn't already in use
4. ✅ Document the subdomain for your records
5. ✅ Plan which users will need access

## Pagination and Search

### Working with Large Lists

- The system displays 10 subdomains per page
- Use pagination controls at the bottom to navigate
- Subdomains are ordered by creation date (newest first)
- If you have more than 10 subdomains, use the page numbers to browse

## Next Steps

After creating a subdomain:

1. **Set Up Initial User** - Your account is automatically linked
2. **Add More Users** - Invite team members to the tenant
3. **Configure Tenant Settings** - Set up organization-specific preferences
4. **Test Access** - Verify the subdomain URL works correctly
5. **Inform Stakeholders** - Share the subdomain URL with relevant users

## Need Help?

### Support Resources

- **Technical Issues:** Contact your system administrator
- **Feature Requests:** Submit via the feedback system
- **Bug Reports:** Report at the issue tracking URL
- **Documentation:** Check `/docs` folder for additional guides

### Quick Reference

| Action | Permission Required | Can Undo? |
|--------|-------------------|-----------|
| View Subdomains | System Admin | N/A |
| Create Subdomain | System Admin | Via Delete |
| Edit Subdomain | System Admin | Yes |
| Delete Subdomain | System Admin | ❌ No |
| Manage Users | System Admin | Yes |

## Technical Details (For Administrators)

### Database Structure

**Tables Involved:**
- `tenants` - Stores tenant records with JSON data column
- `domains` - Stores domain and subdomain information
- `users` - User accounts
- `tenant_user` - Many-to-many relationship pivot table

### Validation Rules

**Company Name:**
```
- Required: Yes
- Type: String
- Max Length: 255 characters
```

**Subdomain:**
```
- Required: Yes
- Pattern: ^[a-zA-Z0-9]+$ (alphanumeric only)
- Max Length: 8 characters
- Unique: Yes (across all domains)
```

### System Behavior

**Multi-Tenancy:**
- Each tenant has isolated data via `tenant_id`
- System uses `system_id` for additional scoping
- Domains have foreign key constraints to tenants

**Cascade Deletion:**
- Deleting a domain cascades to tenant if configured
- User relationships are manually cleaned up
- `tenant_user` pivot records are explicitly deleted

### Configuration

**Central Domain:**
The base domain is configured in `config/tenancy.central_domains`:
```php
'central_domains' => [
    'yourdomain.com' // Your central domain
]
```

Full subdomain format: `{subdomain}.{central_domain}`

### Auto-Login Implementation

**Redirect Feature:**
- Uses Laravel's `temporarySignedRoute()`
- Route name: `auto-login`
- Expiration: 10 minutes
- Parameters: `user` (ID), `subdomain` (text)
- Signature prevents tampering

---

**Last Updated:** 2025-10-06
**Version:** 1.0
**Tested With:** OMS Single-DB v1.0
