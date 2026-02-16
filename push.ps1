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

Write-Host "Adding changes..."
git add .

Write-Host "Committing with message: $message"
git commit -m "$message"

Write-Host "Pushing to GitHub..."
git push origin main

Write-Host "Done! Your changes are now being deployed by Hostinger." -ForegroundColor Green
