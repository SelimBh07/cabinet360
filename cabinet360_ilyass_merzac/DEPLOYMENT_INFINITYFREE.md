# Cabinet360 PWA - Deployment Guide for InfinityFree

## 📱 PWA Features Included

✅ **Progressive Web App Ready**
- Fully responsive mobile and tablet design
- Offline functionality with Service Worker
- "Add to Home Screen" capability
- Fast loading with caching strategy
- Optimized for touch devices

✅ **Mobile Optimizations**
- Responsive layouts for all screen sizes
- Touch-friendly buttons (44px minimum)
- Optimized forms for mobile keyboards
- Adaptive tables for small screens
- Safe area support for notched devices

---

## 🚀 Deployment Steps for InfinityFree

### Step 1: Sign Up and Create Account
1. Go to [InfinityFree.net](https://infinityfree.net)
2. Create a free account
3. Create a new hosting account
4. Note your:
   - Domain/Subdomain (e.g., `yoursite.infinityfreeapp.com`)
   - FTP hostname
   - FTP username
   - FTP password

### Step 2: Create MySQL Database
1. In your InfinityFree control panel (cPanel), go to **MySQL Databases**
2. Create a new database (e.g., `epiz_xxxxx_cabinet360`)
3. Create a database user
4. Add user to database with ALL PRIVILEGES
5. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `sql###.infinityfree.com`)

### Step 3: Configure the Application

1. Open `config/config.php` in a text editor
2. Update the database settings:

```php
// Database Configuration
define('DB_HOST', 'sql###.infinityfree.com');  // Your DB host
define('DB_USER', 'epiz_xxxxx_dbuser');        // Your DB username
define('DB_PASS', 'your_db_password');         // Your DB password
define('DB_NAME', 'epiz_xxxxx_cabinet360');    // Your DB name

// Application Settings
define('APP_NAME', 'Cabinet360');
define('APP_URL', 'https://yoursite.infinityfreeapp.com');  // Your domain
```

3. If using HTTPS (recommended), update:
```php
ini_set('session.cookie_secure', 1); // Set to 1 for HTTPS
```

### Step 4: Upload Files via FTP

**Option A: Using FileZilla (Recommended)**
1. Download [FileZilla Client](https://filezilla-project.org/)
2. Connect using your FTP credentials
3. Navigate to `/htdocs/` folder on the server
4. Upload ALL Cabinet360 files to `/htdocs/`
5. Ensure proper file structure:
   ```
   /htdocs/
   ├── actions/
   ├── assets/
   │   ├── css/
   │   ├── icons/
   │   └── js/
   ├── config/
   ├── includes/
   ├── pages/
   ├── uploads/
   ├── index.php
   ├── login.php
   ├── manifest.json
   ├── service-worker.js
   └── ...
   ```

**Option B: Using File Manager**
1. In cPanel, go to **File Manager**
2. Upload a ZIP file of Cabinet360
3. Extract in `/htdocs/` directory

### Step 5: Import Database

1. In cPanel, open **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Choose `database.sql` file
5. Click **Go** to import

### Step 6: Set Permissions

Set folder permissions via FTP or File Manager:
- `/uploads/` → **755** or **777** (needs write access)
- All PHP files → **644**
- All folders → **755**

### Step 7: Generate PWA Icons

**Important:** You must generate the PWA icons for the app to work properly.

**Option 1: Use HTML Generator (Easiest)**
1. Open `https://yoursite.infinityfreeapp.com/create_icons.html` in your browser
2. Click "Download Icons" button
3. Upload `icon-192x192.png` and `icon-512x512.png` to `/htdocs/assets/icons/`
4. Delete `create_icons.html` from server

**Option 2: Upload Your Own Icons**
- Create or design two PNG files (192x192 and 512x512 pixels)
- Upload to `/htdocs/assets/icons/`

**Option 3: Use Python Script (If Python available)**
```bash
pip install Pillow
python generate_icons.py
```

### Step 8: Test Your PWA

1. Visit `https://yoursite.infinityfreeapp.com`
2. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`

3. **Test PWA Features:**
   - Open on mobile browser (Chrome/Safari)
   - Check for "Install App" or "Add to Home Screen" prompt
   - Install the PWA
   - Test offline functionality
   - Verify responsive design

---

## 🔧 InfinityFree Specific Notes

### .htaccess Configuration (Optional)

Create a `.htaccess` file in `/htdocs/` for better performance:

```apache
# Force HTTPS (if SSL enabled)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Enable Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
</IfModule>
```

### Common Issues & Solutions

**Issue 1: Service Worker Not Registering**
- Solution: Ensure HTTPS is enabled (PWAs require HTTPS)
- InfinityFree provides free SSL certificates

**Issue 2: Database Connection Error**
- Solution: Double-check DB credentials in `config/config.php`
- Verify database host (usually `sql###.infinityfree.com`)

**Issue 3: Upload Folder Permission Denied**
- Solution: Set `/uploads/` folder permissions to 755 or 777

**Issue 4: Icons Not Loading**
- Solution: Make sure icon files exist in `/assets/icons/`
- Generate icons using `create_icons.html`

**Issue 5: 404 Errors on Pages**
- Solution: Ensure all files uploaded to correct location
- Check APP_URL in config.php matches your domain

---

## 📱 Mobile Installation Guide

### For Android (Chrome)
1. Open the site in Chrome
2. Tap the menu (⋮) → "Install app" or "Add to Home Screen"
3. Confirm installation
4. App icon appears on home screen

### For iOS (Safari)
1. Open the site in Safari
2. Tap the Share button (□↑)
3. Scroll down and tap "Add to Home Screen"
4. Name the app and tap "Add"
5. App icon appears on home screen

---

## 🎨 Customization

### Change Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary-black: #111;
    --gold: #D4AF37;  /* Change this for different accent color */
    --gold-hover: #c49a2c;
}
```

### Change App Name
Edit `manifest.json`:
```json
{
  "name": "Your Custom Name",
  "short_name": "CustomName"
}
```

---

## 🔒 Security Recommendations

1. **Change Default Login:**
   - Go to database → `users` table
   - Update admin password (use password_hash in PHP)

2. **Enable HTTPS:**
   - In InfinityFree cPanel, enable free SSL certificate

3. **Regular Backups:**
   - Backup database regularly via phpMyAdmin
   - Download file backups via FTP

4. **Update Configuration:**
   - Remove or rename `create_icons.html` and `generate_icons.py` after use
   - Delete `INSTALLATION.txt` if it contains sensitive info

---

## 📊 Performance Optimization

Your PWA is already optimized with:
- ✅ Service Worker caching
- ✅ Lazy loading images
- ✅ Minified external libraries (CDN)
- ✅ Optimized CSS for mobile
- ✅ Touch-optimized interface

---

## 🆘 Support

If you encounter issues:

1. Check browser console (F12) for errors
2. Verify all files uploaded correctly
3. Check database connection
4. Ensure icons are generated
5. Verify InfinityFree account is active

---

## 📝 Final Checklist

Before going live:
- [ ] Database imported successfully
- [ ] config.php updated with correct credentials
- [ ] All files uploaded to /htdocs/
- [ ] Icons generated and uploaded
- [ ] Login works (test with admin/admin123)
- [ ] PWA installs on mobile device
- [ ] Offline mode tested
- [ ] All pages responsive on mobile
- [ ] Default password changed
- [ ] Temporary files removed (create_icons.html, etc.)

---

## 🎉 Your PWA is Ready!

Your Cabinet360 app is now:
- ✅ Accessible from any device
- ✅ Installable as a mobile app
- ✅ Works offline
- ✅ Fully responsive
- ✅ Free to host on InfinityFree

**Congratulations!** 🎊

