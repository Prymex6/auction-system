@echo off
REM ============================================
REM PIGEON AUCTION - SETUP SCRIPT (Windows)
REM ============================================

echo 🐦 Setting up Pigeon Auction Platform...

REM 1. Copy .env file
echo 📋 Copying .env.example to .env...
copy .env.example .env

REM 2. Install dependencies
echo 📦 Installing PHP dependencies...
composer install

REM 3. Generate APP_KEY
echo 🔑 Generating APP_KEY...
php artisan key:generate

REM 4. Create database
echo 📊 Creating database...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS pigeon_auction CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul || echo ⚠️  MySQL not found

REM 5. Run migrations
echo 🚀 Running migrations...
php artisan migrate --force

REM 6. Run seeders
echo 🌱 Seeding database...
php artisan db:seed

REM 7. Build frontend (if exists)
if exist package.json (
    echo 🎨 Installing npm dependencies...
    call npm install
    echo 🔨 Building frontend...
    call npm run build
)

echo.
echo ✅ Setup complete!
echo.
echo 🚀 To start the development server, run:
echo    php artisan serve
echo.
echo 📚 Then visit: http://localhost:8000
echo.
echo 🔐 Test credentials:
echo    Email: seller@example.com
echo    Email: buyer@example.com
echo    Email: admin@example.com
echo    Password: password (check factory)
echo.
pause
