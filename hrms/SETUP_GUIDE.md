# HRMS Setup Guide

This guide provides detailed instructions for setting up the HRMS backend application on your local development environment or production server.

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0+ (for production) or SQLite (for development)
- Git

### PHP Extensions Required

The following PHP extensions must be enabled:

- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- PDO_MySQL (for MySQL) or PDO_SQLite (for SQLite)
- Tokenizer
- XML

## Installation Steps

### Step 1: Clone the Repository

```bash
git clone https://github.com/01fe23bcs183/nimisha-sign.git
cd nimisha-sign/hrms
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Environment Configuration

Copy the example environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Database Configuration

#### Option A: MySQL (Recommended for Production)

Edit the `.env` file with your MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrms
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Create the database:

```bash
mysql -u root -p -e "CREATE DATABASE hrms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### Option B: SQLite (For Development/Testing)

Edit the `.env` file:

```env
DB_CONNECTION=sqlite
```

Create the SQLite database file:

```bash
touch database/database.sqlite
```

### Step 5: Run Migrations

Run the database migrations to create all required tables:

```bash
php artisan migrate
```

This will create 51 tables including users, staff_members, office_locations, divisions, job_titles, leaves, attendances, pay_slips, and more.

### Step 6: Seed the Database

Seed the database with default data including roles, permissions, and sample data:

```bash
php artisan db:seed
```

This will create:
- Default roles (administrator, manager, hr_officer, staff_member)
- 40+ permissions for various operations
- Default users for each role
- Sample office locations, divisions, and job titles
- Leave types (Annual, Sick, Casual, Maternity, etc.)
- Tax brackets for payroll calculations
- Allowance, deduction, and loan options
- Sample holidays
- Document types and letter templates

### Step 7: Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

## Production Deployment

### Additional Steps for Production

1. Set the environment to production:

```env
APP_ENV=production
APP_DEBUG=false
```

2. Configure caching:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. Set up a proper web server (Nginx or Apache) with PHP-FPM.

4. Configure SSL/TLS for HTTPS.

5. Set up proper file permissions:

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Nginx Configuration Example

```nginx
server {
    listen 80;
    server_name hrms.yourdomain.com;
    root /var/www/hrms/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Default Credentials

After seeding, the following users are available:

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@hrms.com | password |
| Manager | manager@hrms.com | password |
| HR Officer | hr@hrms.com | password |
| Staff Member | staff@hrms.com | password |

**Important:** Change these passwords immediately in a production environment.

## Testing the Installation

### Verify API is Working

Test the login endpoint:

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "admin@hrms.com", "password": "password"}'
```

You should receive a JSON response with a token.

### Run Automated Tests

```bash
php artisan test
```

## Troubleshooting

### Common Issues

**Issue: "Could not find driver" error**

Solution: Install the appropriate PHP database extension:
```bash
# For MySQL
sudo apt-get install php8.2-mysql

# For SQLite
sudo apt-get install php8.2-sqlite3
```

**Issue: Permission denied errors**

Solution: Fix storage permissions:
```bash
chmod -R 775 storage bootstrap/cache
```

**Issue: Class not found errors**

Solution: Regenerate autoload files:
```bash
composer dump-autoload
```

**Issue: Migration errors**

Solution: Clear cache and retry:
```bash
php artisan cache:clear
php artisan config:clear
php artisan migrate:fresh --seed
```

## Environment Variables Reference

| Variable | Description | Default |
|----------|-------------|---------|
| APP_NAME | Application name | HRMS |
| APP_ENV | Environment (local/production) | local |
| APP_DEBUG | Enable debug mode | true |
| APP_URL | Application URL | http://localhost |
| APP_TIMEZONE | Application timezone | UTC |
| DB_CONNECTION | Database driver | mysql |
| DB_HOST | Database host | 127.0.0.1 |
| DB_PORT | Database port | 3306 |
| DB_DATABASE | Database name | hrms |
| DB_USERNAME | Database username | root |
| DB_PASSWORD | Database password | - |

## Next Steps

After completing the setup:

1. Review the [FRONTEND_GUIDE.md](FRONTEND_GUIDE.md) for API documentation
2. Configure email settings for password reset functionality
3. Set up scheduled tasks for automated reports
4. Configure file storage for staff documents
