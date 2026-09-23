#!/bin/bash

# ============================================
# PIGEON AUCTION - SETUP SCRIPT
# ============================================

echo "🐦 Setting up Pigeon Auction Platform..."

# 1. Copy .env file
echo "📋 Copying .env.example to .env..."
cp .env.example .env

# 2. Install dependencies
echo "📦 Installing PHP dependencies..."
composer install

# 3. Generate APP_KEY
echo "🔑 Generating APP_KEY..."
php artisan key:generate

# 4. Create database
echo "📊 Creating database..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS pigeon_auction CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || echo "⚠️  MySQL not found or database already exists"

# 5. Run migrations
echo "🚀 Running migrations..."
php artisan migrate --force

# 6. Run seeders
echo "🌱 Seeding database..."
php artisan db:seed

# 7. Build frontend (if exists)
if [ -f "package.json" ]; then
    echo "🎨 Installing npm dependencies..."
    npm install
    echo "🔨 Building frontend..."
    npm run build
fi

echo ""
echo "✅ Setup complete!"
echo ""
echo "🚀 To start the development server, run:"
echo "   php artisan serve"
echo ""
echo "📚 Then visit: http://localhost:8000"
echo ""
echo "🔐 Test credentials:"
echo "   Email: seller@example.com"
echo "   Email: buyer@example.com"
echo "   Email: admin@example.com"
echo "   Password: password (check factory)"
echo ""
