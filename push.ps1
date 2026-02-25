param (
    [string]$message = "Minor updates"
)

Write-Host "Starting Easy Push..." -ForegroundColor Cyan

# Check if there are changes
$status = git status --porcelain
if (-not $status) {
    Write-Host "No changes to push." -ForegroundColor Green
    exit
}

# Sync assets from public to root (Hostinger production fix)
Write-Host "Syncing assets to root..." -ForegroundColor Yellow
if (Test-Path "public\assets") {
    Copy-Item -Path "public\assets\*" -Destination "assets\" -Recurse -Force -ErrorAction SilentlyContinue
}

Write-Host "Adding changes..."
git add .
git reset -- fcm_config/ 2>$null

Write-Host "Committing with message: $message"
git commit -m "$message" --allow-empty

Write-Host "Pushing to GitHub..."
git push origin main

Write-Host "Done! Your changes are now being deployed by Hostinger." -ForegroundColor Green
