# DriveEase Vehicle Rental System

DriveEase is a PHP and MySQL vehicle rental web application for browsing vehicles, booking rentals, paying through eSewa, and managing users, vehicles, bookings, and contact messages from an admin panel.

## Features

- User signup, email verification, login, logout, and OTP-based password reset
- Role-based access for users and administrators
- Vehicle fleet browsing with category, type, transmission, and price filtering
- Booking flow with server-side date validation, overlap checks, dynamic pricing, and payment session handling
- eSewa ePay v2 integration for online payments
- User dashboard for confirmed paid bookings
- Admin panel for managing vehicles, users, bookings, and contact messages
- Vehicle image upload support for admin vehicle management
- Contact form storage
- Floating chatbot assistant backed by the configured Groq API key
- CSRF protection, prepared SQL statements, password hashing, and basic rate limiting

## Tech Stack

- PHP 8+
- MySQL or MariaDB
- Composer
- PHPMailer
- HTML, CSS, and vanilla JavaScript
- XAMPP-compatible local setup

## Project Structure

```text
VRS-php/
  config/          App configuration, environment loading, database connection
  controllers/     Authentication, booking, home, and vehicle controllers
  helpers/         Mail, upload, and eSewa helpers
  models/          Database models
  public/          CSS, JavaScript, and image assets
  uploads/         Uploaded vehicle images
  view/layout/     Shared headers and footers
  database.sql     Database schema and default admin seed
```

## Setup

1. Place the project in your XAMPP web root:

```text
C:\xampp\htdocs\VRS-php
```

2. Install Composer dependencies:

```bash
composer install
```

3. Create the database:

Open phpMyAdmin or MySQL CLI and import:

```text
database.sql
```

The schema creates a `vrs` database by default.

4. Configure `.env`:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_FROM_EMAIL=your-email@gmail.com
SMTP_FROM_NAME=DriveEase

APP_URL=http://localhost/VRS-php
APP_NAME=DriveEase

GROQ_API_KEY=your-groq-api-key

ESEWA_PRODUCT_CODE=EPAYTEST
ESEWA_SECRET_KEY=8gBm/:&EnhH.1/q
ESEWA_PAYMENT_URL=https://rc-epay.esewa.com.np/api/epay/main/v2/form
ESEWA_STATUS_URL=https://rc.esewa.com.np/api/epay/transaction/status/
```

For production eSewa payments, replace the sandbox values with credentials and URLs provided by eSewa.

5. Start Apache and MySQL from XAMPP.

6. Open the app:

```text
http://localhost/VRS-php
```

## Default Admin

The database seed creates a pre-verified admin account:

```text
Email: admin@admin.com
Password: Admin123
```

Change this password after the first login in any real deployment.

## Payment Notes

The app uses eSewa ePay v2. In local development, it submits payments to the eSewa UAT endpoint when sandbox credentials are used. A `502 Bad Gateway` page from `rc-epay.esewa.com.np` usually indicates an eSewa sandbox-side outage or gateway issue, not a PHP error in this project.

## Useful Checks

Run PHP syntax checks:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -d display_errors=1 -l $_.FullName }
```

Search for unused references or non-ASCII symbols:

```bash
rg "contact\.js|search\.css|reset\.css|reset_password\.css"
rg -n "[^\x00-\x7F]" -P .
```

## Security Notes

- Do not commit real SMTP passwords, Groq keys, or production eSewa secrets.
- Use Gmail app passwords instead of normal account passwords.
- Keep `vendor/` generated through Composer.
- Ensure `uploads/` only accepts expected image types.
- Use HTTPS and secure session cookies in production.

