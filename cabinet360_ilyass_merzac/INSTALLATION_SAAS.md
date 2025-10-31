# Cabinet360 SaaS - Multi-Tenant Installation Guide

## 📋 Overview

This guide will help you install and deploy the Cabinet360 SaaS multi-tenant lawyer management system.

---

## 🚀 Quick Installation Steps

### 1. System Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache or Nginx
- **PHP Extensions**:
  - PDO
  - PDO_MySQL
  - mbstring
  - json
  - session

---

### 2. Database Setup

#### Step A: Create Database

```sql
CREATE DATABASE cabinet360_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Step B: Import Schema

Import the multi-tenant database schema:

```bash
mysql -u your_username -p cabinet360_saas < database_multitenant.sql
```

**OR** if using phpMyAdmin:
1. Open phpMyAdmin
2. Select `cabinet360_saas` database
3. Click "Import" tab
4. Choose `database_multitenant.sql`
5. Click "Go"

---

### 3. Configuration

#### Update `config/config.php`

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'cabinet360_saas');

// Application Settings
define('APP_NAME', 'Cabinet360');
define('APP_URL', 'http://yourdomain.com'); // Change this to your domain
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
```

#### Set Permissions

```bash
chmod 755 config/
chmod 644 config/config.php
chmod 755 uploads/
```

---

### 4. Default Access Credentials

#### 🔴 Admin Panel Access

**URL**: `http://yourdomain.com/admin/login.php`

- **Username**: `admin`
- **Password**: `AdminCabinet360!`

⚠️ **IMPORTANT**: Change the admin password immediately after first login!

#### 👨‍⚓ Test Lawyer Account

**URL**: `http://yourdomain.com/login_lawyer.php`

- **Email**: `lawyer@test.com`
- **Password**: `Lawyer123!`

---

### 5. Testing the Installation

1. **Test Lawyer Login**:
   - Go to `http://yourdomain.com/login_lawyer.php`
   - Login with test credentials
   - Check dashboard displays correctly

2. **Test Admin Panel**:
   - Go to `http://yourdomain.com/admin/login.php`
   - Login with admin credentials
   - Verify lawyer list shows test account

3. **Test Signup**:
   - Go to `http://yourdomain.com/signup.php`
   - Create a new lawyer account
   - Verify account creation and login

---

## 🌐 Deployment Options

### Option 1: Shared Hosting (InfinityFree, Hostinger, etc.)

1. **Upload Files**:
   - Use FTP/SFTP or File Manager
   - Upload all files to `public_html` or `www` directory

2. **Create Database**:
   - Use cPanel or hosting control panel
   - Create MySQL database
   - Import `database_multitenant.sql`

3. **Update Config**:
   - Edit `config/config.php` with database credentials
   - Update `APP_URL` to your domain

4. **Set Permissions**:
   - `uploads/` folder: 755 or 777
   - `config/` folder: 755

### Option 2: VPS/Dedicated Server

#### Apache Setup

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/cabinet360
    
    <Directory /var/www/cabinet360>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/cabinet360_error.log
    CustomLog ${APACHE_LOG_DIR}/cabinet360_access.log combined
</VirtualHost>
```

#### Nginx Setup

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/cabinet360;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Option 3: Local Development (XAMPP/WAMP)

1. Copy files to `C:\xampp\htdocs\Cabinet360\`
2. Start Apache and MySQL in XAMPP
3. Create database via phpMyAdmin
4. Import `database_multitenant.sql`
5. Update `config/config.php`:
   ```php
   define('APP_URL', 'http://localhost/Cabinet360');
   ```
6. Access: `http://localhost/Cabinet360/login_lawyer.php`

---

## 📱 PWA (Mobile App) Setup

The system is already PWA-ready with:
- ✅ Service Worker (`service-worker.js`)
- ✅ Manifest file (`manifest.json`)
- ✅ Icons generated

### To install as mobile app:

**Android (Chrome)**:
1. Open site in Chrome
2. Tap menu → "Add to Home Screen"
3. Icon will appear on home screen

**iOS (Safari)**:
1. Open site in Safari
2. Tap Share button
3. Select "Add to Home Screen"

---

## 🔒 Security Checklist

### After Installation:

- [ ] Change default admin password
- [ ] Delete or disable test lawyer account
- [ ] Update `session.cookie_secure` to `1` if using HTTPS
- [ ] Enable HTTPS with SSL certificate
- [ ] Set proper file permissions
- [ ] Review and update database credentials
- [ ] Configure email for password resets (optional)

### Recommended Security Settings:

```php
// In config/config.php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Only if HTTPS
```

---

## 🛠️ Troubleshooting

### Issue: "Database connection error"
**Solution**: Check database credentials in `config/config.php`

### Issue: "Page not found"
**Solution**: 
- Check `APP_URL` is correct in `config/config.php`
- Verify `.htaccess` file exists (for Apache)

### Issue: "Cannot upload files"
**Solution**: 
- Check `uploads/` folder exists
- Set permissions: `chmod 755 uploads/`

### Issue: "Session errors"
**Solution**: 
- Verify PHP session is enabled
- Check server has write permissions for session files

### Issue: "CSS/JS not loading"
**Solution**: 
- Check `APP_URL` path is correct
- Verify file paths in header.php

---

## 📊 Database Structure

The multi-tenant database includes:

- **lawyers**: Main tenant table (one per lawyer/cabinet)
- **admin_users**: System administrators
- **clients**: Client data (filtered by lawyer_id)
- **cases**: Case management (filtered by lawyer_id)
- **appointments**: Appointments (filtered by lawyer_id)
- **payments**: Payment tracking (filtered by lawyer_id)
- **subscription_plans**: Available subscription plans
- **password_resets**: Password reset tokens
- **activity_logs**: Audit trail for admin

---

## 🎯 Post-Installation Tasks

### 1. Customize Branding
- Update logo in `assets/icons/`
- Modify colors in `assets/css/style.css`
- Change app name in `config/config.php`

### 2. Configure Email (Optional)
Update password reset emails by integrating PHPMailer or similar.

### 3. Set Up Backup
```bash
# Daily database backup
mysqldump -u username -p cabinet360_saas > backup_$(date +%Y%m%d).sql
```

### 4. Monitor Activity
- Check admin panel → Activity Logs
- Review lawyer registrations
- Monitor subscription status

---

## 📞 Support

For issues or questions:
- **Email**: support@cabinet360.com
- **Documentation**: See other `.md` files in project root
- **Admin Panel**: Monitor activity logs for debugging

---

## 🎉 You're All Set!

Your Cabinet360 SaaS multi-tenant system is now ready to use!

- Lawyers can signup at: `/signup.php`
- Lawyers login at: `/login_lawyer.php`
- Admin panel at: `/admin/login.php`

Happy Managing! ⚖️



