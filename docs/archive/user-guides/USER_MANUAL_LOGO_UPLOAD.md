# Logo Upload User Manual

## Overview

The Logo Upload feature allows users to personalize their profile with a custom logo image. The system supports separate logos for different contexts:

- **System Logo**: Your logo when accessing the main system platform
- **Tenant Logo**: Your logo when accessing a specific tenant/subdomain

Each logo is stored independently, so changing one does not affect the other.

---

## Accessing Logo Settings

1. Navigate to **Settings** from the main menu
2. Click on the **Profile** tab
3. Scroll to the **Logo** section

---

## Understanding Logo Scopes

### System Scope
- **When**: Accessing the system at the main domain (e.g., `http://localhost:8000`)
- **Visibility**: Logo appears in the sidebar under "System platform"
- **Independence**: Changes to this logo do not affect tenant logos

### Tenant Scope
- **When**: Accessing a specific tenant/subdomain (e.g., `http://tenant.oms.test`)
- **Visibility**: Logo appears in the sidebar under "Tenancy platform"
- **Independence**: Changes to this logo do not affect the system logo

---

## How to Upload a Logo

### Step 1: Select Your Image

1. In the **Logo** section, click the **Choose File** button
2. Browse your computer and select an image file
3. The system accepts these formats:
   - JPG/JPEG
   - PNG
   - GIF
   - SVG
4. Maximum file size: **2 MB**

### Step 2: Preview Your Selection

- After selecting a file, a preview will appear below the upload field
- The preview shows how your logo will look
- Review the preview to ensure it's the correct image

### Step 3: Save Your Logo

1. Click the **Save Logo** button
2. Wait for the upload to complete (button will show "Saving..." during upload)
3. A success message will appear: "Logo uploaded successfully!"
4. The page will automatically refresh to display your new logo everywhere

---

## Current Logo Display

The **Current Logo** section shows your active logo for the current scope:

- **If you have a logo**: Your uploaded image is displayed
- **If you don't have a logo**: A default icon is shown
- The current logo updates immediately after a successful upload

---

## File Requirements

### Supported Formats
- **JPG/JPEG**: Standard photo format, good for photos
- **PNG**: Supports transparency, best for logos with transparent backgrounds
- **GIF**: Supports animation, but static images recommended
- **SVG**: Vector format, scales perfectly at any size

### File Size Limit
- Maximum: **2 MB** (2048 KB)
- Recommended: Under 500 KB for faster loading

### Image Dimensions
- Recommended: **Square images** (e.g., 500x500 pixels)
- Display size: Your logo will be displayed at 96x96 pixels
- The system automatically scales your image to fit

---

## Changing Your Logo

To replace an existing logo:

1. Follow the same upload process described above
2. Select a new image file
3. Preview the new image
4. Click **Save Logo**
5. Your old logo will be automatically replaced with the new one

**Note**: The old logo is permanently deleted when you upload a new one.

---

## Best Practices

### Image Quality
- Use high-quality images for better appearance
- Avoid overly compressed or low-resolution images
- Square images work best

### File Formats
- **PNG** is recommended for logos with transparent backgrounds
- **JPG** is recommended for photographic images
- **SVG** is recommended for simple graphics that need to scale

### File Size
- Optimize your images before uploading to reduce file size
- Use online tools to compress images without losing quality
- Smaller files load faster

### Design Considerations
- Ensure your logo is visible on both light and dark backgrounds
- Test in both light mode and dark mode if your system supports it
- Keep designs simple and recognizable at small sizes

---

## Troubleshooting

### "The logo failed to upload"

**Possible causes:**
- File is too large (over 2 MB)
- File format is not supported
- Network connection issue
- Server error

**Solutions:**
1. Check your file size and reduce if necessary
2. Verify your file format is JPG, PNG, GIF, or SVG
3. Try uploading again
4. If problem persists, contact your system administrator

### Logo not displaying after upload

**Solutions:**
1. Refresh the page (the system should do this automatically)
2. Clear your browser cache
3. Check that you're in the correct scope (system vs tenant)
4. Contact your system administrator if issue persists

### Wrong logo appears in different scope

**Understanding the issue:**
- System and tenant logos are separate
- You may need to upload logos in both scopes

**Solution:**
1. Check which scope you're currently in (look at the URL)
2. Navigate to the correct scope
3. Upload the appropriate logo for that scope

### Preview not showing

**Solutions:**
1. Ensure the file is a valid image format
2. Check that the file is not corrupted
3. Try a different image
4. Refresh the page and try again

### Upload button is disabled

**Possible causes:**
- No file has been selected
- The selected file doesn't meet requirements

**Solutions:**
1. Ensure you've clicked "Choose File" and selected an image
2. Verify the file meets format and size requirements
3. Try selecting the file again

---

## Manager-Specific Information

### User Logo Management

As a manager, you should be aware:

- Each user manages their own logo independently
- Logos are stored per user, not per role or permission
- Users can change their logos at any time
- System and tenant logos are stored separately in the database

### Storage Location

- Logos are stored using the Media Library system
- System logos: `system_logo` collection
- Tenant logos: `tenant_logo` collection
- Files are stored in the `storage/app/public/media` directory

### Database Records

Logo metadata is stored in the `media` table with:
- `model_type`: App\Models\User
- `model_id`: User ID
- `collection_name`: Either "system_logo" or "tenant_logo"

---

## Privacy and Security

### Your Logo Data

- Logos are visible to other users in the system
- Your logo appears in the sidebar when you're logged in
- Logos are stored securely on the server
- Only you can change or delete your logo

### What Not to Upload

**Do not upload:**
- Copyrighted images without permission
- Offensive or inappropriate content
- Personal identification documents
- Files containing sensitive information

---

## Frequently Asked Questions

**Q: Can I have different logos for different tenants?**
A: Currently, all tenants use the same tenant logo. Each user has one system logo and one tenant logo.

**Q: What happens to my old logo when I upload a new one?**
A: The old logo is automatically deleted and replaced with the new one.

**Q: Can I remove my logo without uploading a new one?**
A: Currently, you must upload a new logo to replace the existing one. There is no "remove logo" option.

**Q: Why does my logo look different in the sidebar?**
A: The sidebar displays your logo at a smaller size (96x96 pixels). Your original image is scaled down automatically.

**Q: Can I upload animated GIFs?**
A: Yes, but static images are recommended for better performance and professional appearance.

**Q: How often can I change my logo?**
A: You can change your logo as often as you like. There are no restrictions on upload frequency.

**Q: Will my logo be visible to all users?**
A: Your logo is only visible to you in your own interface. Other users see their own logos.

---

## Support

If you encounter issues not covered in this manual:

1. Check the troubleshooting section above
2. Verify you're following the correct upload procedure
3. Ensure your file meets all requirements
4. Contact your system administrator for assistance

---

## Summary

The Logo Upload feature provides a simple way to personalize your profile:

1. Choose an image file (JPG, PNG, GIF, or SVG, max 2 MB)
2. Preview your selection
3. Click "Save Logo"
4. Your logo appears in the sidebar and profile settings

Remember that system and tenant logos are independent, so you may want to upload logos in both scopes for a consistent experience across your system.
