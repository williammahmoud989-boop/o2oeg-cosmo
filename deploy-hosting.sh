#!/bin/bash

# O2OEG Cosmo Deployment Script for Hostinger Managed Hosting
# Usage: ./deploy-hosting.sh

# --- Configuration ---
DOMAIN="o2oeg.com"
IP="82.29.188.80"
PORT="65002"
USER="u658989443"
REMOTE_PATH="domains/$DOMAIN/public_html" # Adjust based on Hostinger structure
TEMP_ZIP="cosmo_deploy.zip"

echo "🚀 Starting deployment for $DOMAIN..."

# 1. Local Build & Preparation
echo "📦 Building assets and preparing local files..."
npm run build
composer install --no-dev --optimize-autoloader

# 2. Creating Deployment Zip
echo "🤐 Creating deployment package..."
zip -r $TEMP_ZIP . -x "node_modules/*" "tests/*" ".git/*" "storage/*.log" ".env" ".env.example" "vendor/*" # We'll run composer install on server if supported, or include vendor

# 3. Uploading to Server
echo "📤 Uploading package to $IP:$PORT..."
scp -P $PORT $TEMP_ZIP $USER@$IP:$REMOTE_PATH/

# 4. Remote Execution
echo "🛠️ Executing remote commands..."
ssh -p $PORT $USER@$IP << EOF
    cd $REMOTE_PATH
    unzip -o $TEMP_ZIP
    rm $TEMP_ZIP
    
    # Finalize Laravel steps
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan storage:link
    
    echo "✅ Remote commands completed."
EOF

# 5. Cleanup
rm $TEMP_ZIP
echo "✨ Deployment finished! Visit https://$DOMAIN"
