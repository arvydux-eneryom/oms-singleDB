Mak# Customer Management Manual

**Version:** 1.0
**Last Updated:** October 24, 2025

---

## Overview

The Customer Management system allows you to create, view, edit, and delete customer records. Each customer can have multiple phone numbers, email addresses, contacts, service addresses, and billing addresses.

---

## Accessing Customers

Navigate to the **Customers** section from the main menu. You'll see a list of all customers with their company name, address, and status.

---

## Creating a Customer

1. Click the **"New Customer"** button
2. Fill in the required fields in the **General** tab:
   - Company Name (required)
   - Address (required)
   - Country (required)
   - City (required)
   - Postcode (optional)
   - At least one phone number (required)
   - At least one email address (required)
   - Status: Active or Inactive

3. Optionally add information in other tabs:
   - **Contacts**: Add contact persons with name, phone, email, and position
   - **Service Addresses**: Add service delivery locations
   - **Billing Addresses**: Add invoicing addresses

4. Click **"Save Customer"**

The system will validate your data and show errors if any required fields are missing. Phone numbers are automatically formatted, and email addresses are converted to lowercase.

---

## Viewing Customer Details

Click the **View** icon (eye) next to any customer in the list. You'll see all customer information organized in tabs:

- **Overview**: Company info, phones, emails, and quick actions
- **Contacts**: List of contact persons
- **Addresses**: Service and billing locations
- **Statistics**: Customer metrics and activity

---

## Editing a Customer

1. Click the **Edit** icon (pencil) next to a customer, or click **"Edit Customer"** from the detail view
2. Make your changes in any tab
3. Click **"Update Customer"** to save

You can modify any information including company name, contact details, addresses, and status. The system checks for duplicate company names when saving.

---

## Deleting Customers

Click the **Delete** icon (trash) next to a customer, or click **"Delete Customer"** from the detail view. Confirm the deletion when prompted.

**Important:** Deletions are "soft deletes" - the data isn't permanently removed and can be recovered by administrators if needed.

---

## Customer Status

Each customer has a status:

- **Active**: Currently operational customers (shown by default)
- **Inactive**: Dormant or suspended customers

Change status by editing the customer and toggling the Status field, or use bulk operations to change multiple customers at once.

---

## Searching and Filtering

**Search:** Type in the search box to filter customers by company name, address, phone number, or email. Results update as you type.

**Filters:**
- **Status**: Show All, Active only, or Inactive only
- **Sort By**: Company, Created Date, or Updated Date
- **Sort Order**: Ascending or Descending
- **Per Page**: Show 10, 25, 50, or 100 customers per page

Filters can be combined with search for precise results.

---

## Bulk Operations

1. Select customers using checkboxes
2. Click **"Bulk Actions"** dropdown
3. Choose an action:
   - **Bulk Delete**: Remove multiple customers
   - **Activate Selected**: Set customers to Active
   - **Deactivate Selected**: Set customers to Inactive

The selection count is displayed. Use the header checkbox to select all customers on the current page.

---

## Exporting Data

**Export All:** Click **"Export CSV"** to download all customers

**Export Selected:** Select customers, then choose **"Export Selected"** from Bulk Actions

The exported CSV file can be opened in Excel, Google Sheets, or any spreadsheet application.

---

## Statistics

The statistics bar at the top of the customer list shows:

- **Total**: All customers in the system
- **Active**: Currently active customers
- **Inactive**: Inactive customers
- **This Month**: Customers added this month

---

## Data Validation

When creating or editing customers, the system enforces these rules:

- Company name, address, country, and city are required
- At least one phone number is required
- At least one email address is required
- Company names must be unique within your organization
- Phone numbers are automatically normalized to a standard format
- Email addresses are automatically converted to lowercase

If validation fails, error messages appear and the system switches to the tab containing errors.

---

## Managing Phone Numbers

For each customer, you can add multiple phone numbers:

- Enter the phone in any format (it will be normalized automatically)
- Select a type: Primary, Work, Home, or Emergency
- Enable SMS if the number can receive text messages
- Click **"+ Add Phone"** to add more numbers
- Click the **"X"** button to remove a phone number

---

## Managing Email Addresses

For each customer, you can add multiple email addresses:

- Enter a valid email address
- Select a type: Primary, Work, or Personal
- Mark as Verified if you've confirmed the email works
- Click **"+ Add Email"** to add more addresses
- Click the **"X"** button to remove an email address

---

## Managing Contacts

Add individual contact persons within a customer organization:

1. Go to the **Contacts** tab
2. Click **"+ Add Contact"**
3. Fill in: Full Name, Phone, Email, Position
4. Click **"X"** to remove a contact

Contacts are optional but recommended for easy communication.

---

## Managing Addresses

**Service Addresses:** Locations where services are delivered

**Billing Addresses:** Addresses for invoicing

For each address type:
1. Go to the respective tab
2. Click **"+ Add Service Address"** or **"+ Add Billing Address"**
3. Fill in: Label, Address, Country, City, Postcode
4. Optionally add Latitude and Longitude for mapping
5. Click **"X"** to remove an address

---

## Common Tasks

**Find a customer:** Type their company name, phone, or email in the search box

**Change customer status:** Edit the customer and toggle the Status field

**Delete multiple customers:** Select them with checkboxes, then choose Bulk Delete

**See only active customers:** Set Status filter to "Active"

**Sort alphabetically:** Set Sort By to "Company" and Sort Order to "Ascending"

**Export for reporting:** Click Export CSV or select specific customers and export

---

## Troubleshooting

**"Cannot save - validation error"**
Check that all required fields are filled: company name, address, country, city, at least one phone, and at least one email.

**"Company already exists"**
Another customer has the same company name. Either edit the existing customer or modify the company name to make it unique.

**"Customer not found"**
Check your filters - the customer might be Inactive. Set Status to "All" to see all customers.

**"Export file is empty"**
Your current filters resulted in zero customers. Clear filters or choose "Export All" instead of "Export Selected".

---

## Best Practices

- Complete all required fields when creating customers
- Add multiple contact methods for reliability
- Use the Status field to manage active vs inactive customers
- Regularly review and update customer information
- Use descriptive labels for multiple addresses
- Export customer data periodically for backup

---

## Getting Help

If you encounter issues:

1. Check this manual for solutions
2. Ask your manager or colleagues
3. Contact your system administrator
4. Submit a support ticket with error details

---

*End of Manual*
