# Fix Controller Not Found Errors

The controllers exist but Laravel's autoloader needs to be regenerated to recognize them.

## Solution: Clear Laravel Cache via Deploy Endpoint

Since you don't have access to `php artisan`, use the deploy endpoint to clear the cache:

1. **Clear application cache:**
   Visit: `https://pinellascu.com/deploy/clear-cache`

2. **Clear config cache:**
   Visit: `https://pinellascu.com/deploy/clear-config`

3. **Clear route cache:**
   Visit: `https://pinellascu.com/deploy/clear-route`

4. **Clear view cache:**
   Visit: `https://pinellascu.com/deploy/clear-view`

## Alternative: Manual File Upload Verification

If the above doesn't work, verify the files are in the correct location:

**Correct path:**
`/home/u664663598/domains/pinellascu.com/public_html/app/Http/Controllers/Backend/`

**Files should be:**
- `DocumentTemplateController.php`
- `DocumentAnalyticsController.php`

## Alternative: SSH Access (if available)

If you have SSH access, run:
```bash
cd /home/u664663598/domains/pinellascu.com/public_html
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

## After Clearing Cache

After clearing the cache, try accessing:
- Document Templates: https://pinellascu.com/admin/document-template
- Document Analytics: https://pinellascu.com/admin/document-analytics
