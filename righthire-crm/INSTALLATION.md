# Right Hire CRM Installation Guide

This guide provides detailed instructions for installing and configuring the Right Hire CRM system.

## Server Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- mod_rewrite enabled
- PHP Extensions:
  - PDO
  - PDO_MySQL
  - mbstring
  - json
  - fileinfo
  - gd

## Step 1: Database Setup

1. Create a new MySQL database:
   ```sql
   CREATE DATABASE righthire_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Create a database user (or use an existing one):
   ```sql
   CREATE USER 'righthire_user'@'localhost' IDENTIFIED BY 'your_secure_password';
   GRANT ALL PRIVILEGES ON righthire_crm.* TO 'righthire_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Import the database schema:
   ```bash
   mysql -u righthire_user -p righthire_crm < database/schema.sql
   ```

4. (Optional) Import sample data:
   ```bash
   mysql -u righthire_user -p righthire_crm < database/sample_data.sql
   ```

## Step 2: Application Setup

1. Upload all files to your web server's document root or a subdirectory.

2. Configure the database connection:
   - Copy `config/config.sample.php` to `config/config.php`
   - Edit `config/config.php` and update the database settings:
     ```php
     // Database configuration
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'righthire_crm');
     define('DB_USER', 'righthire_user');
     define('DB_PASS', 'your_secure_password');
     ```

3. Configure the application URL:
   - In `config/config.php`, set the base URL:
     ```php
     // Application URL
     define('BASE_URL', 'https://yourdomain.com/righthire-crm/');
     ```

4. Set the application environment:
   - For production:
     ```php
     define('ENVIRONMENT', 'production');
     ```
   - For development:
     ```php
     define('ENVIRONMENT', 'development');
     ```

## Step 3: Directory Permissions

Set the correct permissions for writable directories:

```bash
chmod 755 -R uploads/
chmod 755 -R exports/
```

Ensure the web server user has write access to these directories.

## Step 4: Web Server Configuration

### Apache

Ensure the `.htaccess` file is properly configured:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /righthire-crm/
    
    # Redirect to index.php if not a file or directory
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
```

Make sure `mod_rewrite` is enabled:

```bash
a2enmod rewrite
service apache2 restart
```

### Nginx

Add this to your server block:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/righthire-crm;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?url=$uri&$args;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

## Step 5: Security Configuration

1. Enable HTTPS:
   - Install an SSL certificate
   - Configure your web server to use HTTPS
   - Update the BASE_URL in `config/config.php` to use https://

2. Set secure file permissions:
   - Files: 644
   - Directories: 755
   - Configuration files: 600

3. Protect sensitive directories:
   - Add to `.htaccess` or Nginx configuration:
     ```
     # Deny access to config directory
     <FilesMatch "^\.">
         Order allow,deny
         Deny from all
     </FilesMatch>
     ```

## Step 6: Initial Login

After installation, you can log in with the default administrator account:

- Email: sales@getrighthire.com
- Password: Sales@112233

**IMPORTANT**: Change the default password immediately after your first login!

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `config/config.php`
   - Check if MySQL server is running
   - Ensure the database user has proper permissions

2. **404 Page Not Found**
   - Check if mod_rewrite is enabled
   - Verify .htaccess configuration
   - Ensure BASE_URL is set correctly

3. **Permission Denied**
   - Check file/directory permissions
   - Ensure web server user has write access to uploads/ and exports/

4. **Blank Page**
   - Check PHP error logs
   - Enable error reporting in development environment
   - Verify PHP version compatibility

### Enabling Error Reporting

For troubleshooting, you can enable detailed error reporting in `config/config.php`:

```php
// Error reporting
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
```

## Support

For installation support, please contact:

- Email: sales@zixisoft.com
- Website: https://zixisoft.com

