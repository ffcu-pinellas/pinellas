# Manual Upload Instructions

The following controller files are missing from your production server and need to be uploaded manually.

## Files to Upload

Upload these files to your production server at:
`/home/u664663598/domains/pinellascu.com/public_html/app/Http/Controllers/Backend/`

1. **DocumentTemplateController.php** - Handles document template management
2. **DocumentAnalyticsController.php** - Handles document analytics dashboard



## Upload Methods

### Option 1: Using File Manager in cPanel/Hostinger
1. Log in to your hosting control panel
2. Open File Manager
3. Navigate to: `public_html/app/Http/Controllers/Backend/`
4. Upload the two PHP files from this folder

### Option 2: Using FTP/SFTP
1. Connect to your server via FTP/SFTP
2. Navigate to: `public_html/app/Http/Controllers/Backend/`
3. Upload the two PHP files

### Option 3: Using SSH
```bash
# Connect to your server via SSH
# Navigate to the directory
cd /home/u664663598/domains/pinellascu.com/public_html/app/Http/Controllers/Backend/

# Upload files using scp (from your local machine)
scp DocumentTemplateController.php user@your-server:/home/u664663598/domains/pinellascu.com/public_html/app/Http/Controllers/Backend/
scp DocumentAnalyticsController.php user@your-server:/home/u664663598/domains/pinellascu.com/public_html/app/Http/Controllers/Backend/
```

## After Upload

Once the files are uploaded, the Document Templates and Document Analytics features should be accessible from the sidebar navigation under "Document Management".

## Verification

After uploading, try accessing:
- Document Templates: https://pinellascu.com/admin/document-template
- Document Analytics: https://pinellascu.com/admin/document-analytics

If you still get errors, check that file permissions are correct (644 for PHP files).
