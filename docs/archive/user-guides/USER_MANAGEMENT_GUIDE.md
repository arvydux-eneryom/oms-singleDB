# User Management Guide

## Overview

The User Management feature allows system administrators to create, edit, and manage users within the Operation Management System (OMS). Each user can be assigned to one or more tenant organizations (subdomains), given specific roles and permissions, and have their access controlled at a granular level.

**Who is this for?**
- System Administrators who need to manage user accounts
- Managers who oversee user access and permissions
- HR personnel responsible for onboarding/offboarding employees

## Prerequisites

- You must be logged in as a system administrator
- You must have `is_system` permission enabled on your account
- For assigning users to subdomains, the subdomain must already exist

## Managing Users

### Accessing User Management

1. Log in to your OMS system account
2. Navigate to **Users** from the main menu
3. You'll see a list of all users associated with your system

### Viewing User List

The user list displays:
- **Name** - The user's full name
- **Email** - The user's email address (used for login)
- **Access to domains** - List of subdomains the user can access
- **Roles** - Assigned roles and permissions
- **Actions** - Edit and Delete buttons

**Note:** You can only see users that belong to your system. Users from other systems are automatically filtered out for security.

### Pagination

The user list shows 5 users per page. Use the pagination controls at the bottom to navigate through multiple pages when you have more than 5 users.

## Creating a New User

### Step 1: Navigate to Create Page

1. From the Users list, click the **"Create User"** button
2. You'll be taken to the user creation form

### Step 2: Fill in Required Information

#### User Name
- **Required:** Yes
- **Maximum Length:** 255 characters
- **Description:** The user's full name as it will appear in the system
- **Example:** ✅ `John Smith`, `Maria Garcia`
- **Note:** Names must be unique within your system

#### Email
- **Required:** Yes
- **Maximum Length:** 255 characters
- **Format:** Valid email address
- **Description:** Used for login and system notifications
- **Example:** ✅ `john.smith@company.com`, `maria@example.com`
- **Important:**
  - Email must be in lowercase
  - Email must be unique across the entire system
  - ❌ Invalid: `JOHN@COMPANY.COM` (uppercase)
  - ✅ Valid: `john@company.com` (lowercase)

#### Password
- **Required:** Yes
- **Requirements:**
  - Minimum 8 characters
  - Must meet Laravel's default password rules
  - Combination of letters, numbers recommended
- **Security Note:** Passwords are automatically hashed and encrypted before storage
- **Example:** ✅ `SecurePass123!`, `MyPassword2024`

#### Role
- **Required:** No (but recommended)
- **Description:** Determines what the user can access and do in the system
- **Available Roles:** Admin, Manager, User (roles defined by your system)
- **Example:** Select `Admin` for full system access, `User` for limited access

#### Subdomain Assignment
- **Required:** No
- **Description:** Assigns the user to a specific tenant organization
- **Options:** Dropdown shows all available subdomains for your system
- **Note:** You can assign additional subdomains after user creation

### Step 3: Save the User

1. Review all entered information for accuracy
2. Click the **"Save"** button
3. The user will be created and you'll be redirected to the Users list

## What Happens After User Creation

When you create a new user:

1. **User Account Created** - A new user record is saved with `is_tenant` flag set to true
2. **Password Hashed** - The password is securely encrypted
3. **Role Assigned** - The selected role is linked to the user account
4. **Subdomain Access** (if selected) - User is granted access to the chosen subdomain
5. **Registration Event** - System triggers registration event for any automated processes
6. **System ID Set** - User inherits your system_id for proper isolation

**Success Message:** You'll see "User successfully created." confirmation

## Editing an Existing User

### Step 1: Access Edit Page

1. From the Users list, locate the user you want to edit
2. Click the **"Edit"** button next to the user's name
3. You'll see the edit form with current information pre-filled

### Step 2: Update User Information

You can modify:

#### Name
- Update the user's display name
- Must remain unique (except for current user)

#### Email
- Update the user's email address
- Must remain unique (except for current user)
- Must be in lowercase
- Cannot update if it conflicts with another user

#### Role
- Change the user's role
- **Important:** Changing roles immediately updates permissions
- Old roles are automatically removed before assigning new role

#### Subdomain Assignment

**Viewing Assigned Subdomains:**
- See a table of all subdomains currently assigned to the user
- Each row shows the domain name and a "Revoke access" button

**Adding New Subdomains:**
- Use the dropdown to select from available unassigned subdomains
- Click "Save" to grant access

**Removing Subdomain Access:**
1. Find the subdomain in the "Assigned domains" table
2. Click **"Revoke access"** button
3. Confirm the action
4. Access is immediately removed

**Success Messages:**
- "User successfully updated." - User details saved
- "Domain assigned successfully." - Subdomain access granted
- "Domain unassigned successfully." - Subdomain access revoked

### Step 3: Save Changes

1. Review all modifications
2. Click the **"Save"** button
3. Changes are applied immediately

### Multiple Subdomain Management

Users can be assigned to multiple subdomains:
- Each subdomain assignment is independent
- Revoking one subdomain doesn't affect others
- User can have different roles in different tenants (future feature)

## Deleting a User

### When to Delete a User

Delete users when:
- Employee leaves the organization
- Account is no longer needed
- Cleaning up test/demo accounts

**Warning:** This action permanently removes the user and cannot be undone.

### Deletion Process

1. From the Users list, locate the user to delete
2. Click the **"Delete"** button
3. Confirm the deletion when prompted
4. The user is permanently removed

### What Happens During Deletion

The system performs the following actions:

1. **Detach All Tenants** - Removes user access from all subdomains
2. **Remove Roles** - Deletes all role assignments
3. **Delete User Record** - Permanently removes the user account

**Success Message:** "User successfully deleted."

**Important:**
- User data associated with completed work may remain in the system
- Cannot delete your own account while logged in
- Cannot undo deletion

## Common Issues and Solutions

### Issue: "Email must be lowercase"

**Error:** Validation fails when creating/editing user with uppercase email

**Solution:**
- Convert email to lowercase before submission
- ✅ Correct: `john@example.com`
- ❌ Incorrect: `John@Example.COM`

### Issue: "The email has already been taken"

**Error:** Email address already exists in the system

**Solution:**
1. Use a different email address
2. If the email should be available, check if:
   - User already exists (use Edit instead of Create)
   - Old account needs to be deleted first
   - Email belongs to another system (contact system administrator)

### Issue: "The name has already been taken"

**Error:** User name already exists in your system

**Solution:**
1. Use a different name format (e.g., add middle initial)
2. Add department or role to differentiate (e.g., "John Smith - Sales")
3. Check if user already exists

### Issue: User Cannot See Subdomain

**Problem:** User account created but cannot access assigned subdomain

**Troubleshooting:**
1. Verify subdomain is assigned in the Edit User page
2. Check "Access to domains" column in Users list
3. Ensure user has appropriate role permissions
4. Verify subdomain exists and is active
5. Have user log out and log back in

### Issue: Cannot Assign Subdomain

**Problem:** Subdomain doesn't appear in dropdown when creating/editing user

**Possible Causes:**
1. Subdomain already assigned to this user (check Edit page)
2. Subdomain belongs to different system_id
3. No unassigned subdomains available

**Solution:**
- Create new subdomain if needed
- Unassign from another user if appropriate
- Verify you're in the correct system context

### Issue: Password Not Meeting Requirements

**Error:** "The password field must be at least 8 characters"

**Solution:**
- Use minimum 8 characters
- Include mix of letters, numbers, and symbols
- Avoid common/weak passwords

## Best Practices

### User Creation

✅ **Do:**
- Use professional email addresses
- Create strong, unique passwords
- Assign appropriate roles based on job function
- Document why users have specific access
- Verify email addresses before creating accounts

❌ **Don't:**
- Use generic emails (info@, admin@)
- Share user accounts between people
- Grant Admin role unless necessary
- Create users without assigning subdomains
- Use personal email addresses for business users

### Email Management

- Always use lowercase for consistency
- Use company domain emails when possible
- Verify email deliverability before user creation
- Keep email addresses professional

### Role Assignment

- Follow principle of least privilege
- Assign minimum necessary role
- Review roles quarterly
- Document special access requirements
- Remove roles when job duties change

### Subdomain Access

- Only assign subdomains users actually need
- Review access regularly (monthly/quarterly)
- Remove access promptly when no longer needed
- Document access requirements
- Audit unusual access patterns

### Security Practices

- Create unique accounts for each person
- Never share login credentials
- Delete accounts promptly when users leave
- Review user list regularly for inactive accounts
- Use strong passwords
- Revoke access before termination/resignation

## User Lifecycle Management

### Onboarding Process

1. **Create User Account**
   - Gather: full name, email, required role
   - Create user with initial password
   - Assign to appropriate subdomain(s)

2. **Notify User**
   - Send credentials securely
   - Provide login instructions
   - Share relevant documentation

3. **Verify Access**
   - Have user test login
   - Confirm subdomain access
   - Validate role permissions

### During Employment

1. **Regular Reviews**
   - Quarterly access reviews
   - Role validation
   - Subdomain assignments audit

2. **Changes**
   - Job role changes → Update user role
   - Department transfer → Adjust subdomain access
   - Promotion → Review permission needs

### Offboarding Process

1. **Disable Access**
   - Remove subdomain assignments
   - Change role to minimal access (if keeping account)
   - OR delete account entirely

2. **Document**
   - Record deletion date
   - Note data ownership transfer
   - Archive important information

## Technical Details (For Administrators)

### Database Structure

**User Record Fields:**
- `name` - varchar(255), unique within system
- `email` - varchar(255), unique globally, lowercase
- `password` - hashed using bcrypt
- `is_system` - boolean, false for tenant users
- `is_tenant` - boolean, true for regular users
- `system_id` - integer, links to parent system

### Relationships

**User → Tenants (Many-to-Many)**
- Table: `tenant_user`
- Fields: `tenant_id`, `user_id`
- Allows multiple subdomain assignments

**User → Roles (Many-to-Many via Spatie)**
- Table: `model_has_roles`
- Fields: `role_id`, `model_id`, `model_type`
- Managed by Spatie Laravel Permission package

### System Isolation

All user queries filter by `system_id` to ensure:
- Users only see their system's users
- Cannot access other systems' data
- Maintains security boundaries

### Validation Rules

**Name:**
```php
'required', 'string', 'max:255', 'unique:users'
```

**Email:**
```php
'required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'
```

**Password (Create only):**
```php
'required', 'string', Rules\Password::defaults()
```

### Events Triggered

- `Illuminate\Auth\Events\Registered` - On user creation
- Used for email verification, welcome emails, etc.

### Pagination

- Default: 5 users per page
- Uses Tailwind CSS pagination theme
- Configurable in component

## Next Steps

After creating and managing users:

1. **Assign to Projects/Tasks** - Use created users in your OMS workflows
2. **Set Up Permissions** - Fine-tune role permissions in Role Management
3. **Monitor Activity** - Review user login and activity logs
4. **Create More Subdomains** - Set up additional tenant environments as needed
5. **Bulk Operations** - Consider creating multiple users if onboarding team

## Related Documentation

- [Subdomain Management Guide](SUBDOMAIN_MANAGEMENT_GUIDE.md)
- [User Registration Guide](USER_REGISTRATION_GUIDE.md)
- [My Account Link Guide](MY_ACCOUNT_LINK_GUIDE.md)
- Role & Permission Management (if available)

## Need Help?

If you encounter issues not covered in this guide:

1. **Check Error Messages** - They often indicate the specific problem
2. **Review Prerequisites** - Ensure you have necessary permissions
3. **Contact System Administrator** - For access or permission issues
4. **Technical Support** - For system errors or bugs
5. **Documentation** - Check other guides for related features

---

**Document Version:** 1.0
**Last Updated:** October 2024
**Applies to:** OMS User Management Module
