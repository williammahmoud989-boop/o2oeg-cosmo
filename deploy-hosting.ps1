# Fast PowerShell Deployment Script for Hostinger
# Usage: ./deploy-hosting.ps1

Set-Location $PSScriptRoot

$DOMAIN = "o2oeg.com"
$IP = "82.29.188.80"
$PORT = "65002"
$USER = "u658989443"
$REMOTE_PATH = "domains/o2oeg.com/public_html"
$ZIP_FILE = "cosmo_fast.zip"

Write-Host "Starting FAST deployment for $DOMAIN..."

# 1. Build Assets Locally
if (-not (Test-Path "node_modules")) {
    Write-Host "node_modules missing. Running npm install..."
    npm install
}
Write-Host "Building assets locally..."
npm run build

# We exclude vendor and other large/temporary folders to speed up the process.
# The server will run composer install to get the dependencies.
Get-ChildItem -Path . -Exclude "node_modules", "vendor", ".git", "tests", "storage", ".env", ".env.example", $ZIP_FILE | Compress-Archive -DestinationPath $ZIP_FILE -Force

Write-Host "Package created: $ZIP_FILE"

# 3. Upload via SCP
Write-Host "Uploading to Hostinger..."
$SCP_DEST = $USER + "@" + $IP + ":" + $REMOTE_PATH + "/"
# Using -o StrictHostKeyChecking=no to bypass yes/no prompt
scp -o StrictHostKeyChecking=no -P $PORT $ZIP_FILE $SCP_DEST

# 4. Remote Execution via SSH (Including Composer Install)
Write-Host "Running remote setup and installing dependencies on server..."

$REMOTE_CMDS = @(
    "cd $REMOTE_PATH",
    "unzip -o $ZIP_FILE",
    "rm $ZIP_FILE",
    "cp .env.production .env",
    "composer install --no-dev --optimize-autoloader", # Install dependencies on server
    "php artisan migrate --force",
    "php artisan config:cache",
    "php artisan storage:link"
)

$FINAL_CMD = $REMOTE_CMDS -join "; "
$SSH_DEST = $USER + "@" + $IP
# Using -o StrictHostKeyChecking=no to bypass yes/no prompt
ssh -o StrictHostKeyChecking=no -p $PORT $SSH_DEST $FINAL_CMD

# 5. Cleanup
if (Test-Path $ZIP_FILE) { Remove-Item $ZIP_FILE }

Write-Host "FAST Deployment Complete! Visit https://$DOMAIN"
