# 🚀 Cabinet360 SaaS - Quick Start Guide

## ⚡ 5-Minute Setup

### Step 1: Import Database (2 minutes)
```sql
CREATE DATABASE cabinet360_saas;
```
Then import `database_multitenant.sql` via phpMyAdmin or command line.

---

### Step 2: Update Config (1 minute)
Edit `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');     // ← Change this
define('DB_PASS', 'your_password');     // ← Change this
define('DB_NAME', 'cabinet360_saas');
define('APP_URL', 'http://localhost/Cabinet360'); // ← Change to your URL
```

---

### Step 3: Test Access (2 minutes)

#### Test Admin Login
**URL**: `/admin/login.php`
- Username: `admin`
- Password: `AdminCabinet360!`

#### Test Lawyer Login
**URL**: `/login_lawyer.php`
- Email: `lawyer@test.com`
- Password: `Lawyer123!`

#### Test Signup
**URL**: `/signup.php`
- Create a new lawyer account

---

## 🎯 What Changed from Single-User to SaaS?

### Old Way (Single User)
```
Login → Dashboard → Manage all data
```

### New Way (Multi-Tenant SaaS)
```
Signup → Login → Your Own Dashboard → Your Data Only
Admin Panel → Manage All Lawyers
```

---

## 📁 New Files & Structure

### Authentication Files (NEW)
- `signup.php` - Lawyer registration
- `login_lawyer.php` - Lawyer login
- `forgot_password.php` - Password reset request
- `reset_password.php` - Password reset form

### Admin Panel (NEW - `/admin/`)
- `login.php` - Admin login
- `index.php` - Admin dashboard
- `lawyers.php` - Manage all lawyers
- `lawyer_detail.php` - View lawyer details
- `subscriptions.php` - View plans
- `activity_logs.php` - Audit trail
- `logout.php` - Admin logout

### Lawyer Features (NEW)
- `pages/subscription.php` - Manage subscription
- `pages/settings.php` - Updated for lawyer profile

### Database (NEW)
- `database_multitenant.sql` - Multi-tenant schema with:
  - `lawyers` table (main tenant table)
  - `subscription_plans` table
  - `password_resets` table
  - `activity_logs` table
  - All existing tables with `lawyer_id` foreign key

### Config Files (UPDATED)
- `config/auth.php` - Now checks `lawyer_id`
- `config/admin_auth.php` - Admin authentication (NEW)
- `config/multitenant_helper.php` - Helper functions (NEW)

---

## 🔑 Default Credentials

| Role | URL | Username/Email | Password |
|------|-----|----------------|----------|
| **Admin** | `/admin/login.php` | `admin` | `AdminCabinet360!` |
| **Test Lawyer** | `/login_lawyer.php` | `lawyer@test.com` | `Lawyer123!` |

⚠️ **Change these immediately in production!**

---

## 📊 Subscription Plans (Pre-configured)

| Plan | Price | Clients | Cases | Storage |
|------|-------|---------|-------|---------|
| **Free** | 0 MAD | 10 | 5 | 50 MB |
| **Pro** | 299 MAD | 100 | 50 | 500 MB |
| **Premium** | 599 MAD | Unlimited | Unlimited | 2 GB |

---

## 🔍 Quick Testing Checklist

- [ ] Database imported successfully
- [ ] Config updated with correct credentials
- [ ] Admin panel accessible and working
- [ ] Can view lawyers list in admin
- [ ] Lawyer login works with test account
- [ ] New lawyer signup works
- [ ] Lawyer sees only their own data
- [ ] Subscription page displays correctly
- [ ] Settings page allows profile updates
- [ ] Password reset generates tokens

---

## 🐛 Common Issues & Fixes

### "Database connection error"
```php
// Check config/config.php has correct:
define('DB_USER', 'correct_username');
define('DB_PASS', 'correct_password');
```

### "Redirect loop on login"
```php
// Check APP_URL in config/config.php matches your actual URL
define('APP_URL', 'http://yourdomain.com'); // No trailing slash
```

### "Cannot see uploaded files"
```bash
chmod 755 uploads/
```

### "Session errors"
```bash
# Ensure PHP session is working
<?php session_start(); ?>
```

---

## 📱 Mobile Access (PWA)

Your app is already PWA-ready!

**Android**: Open in Chrome → Menu → "Add to Home Screen"
**iOS**: Open in Safari → Share → "Add to Home Screen"

---

## 🎨 Customization Quick Tips

### Change Logo
Replace files in `assets/icons/`:
- `icon-192x192.png`
- `icon-512x512.png`

### Change Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary-color: #D4AF37; /* Gold */
    --dark-bg: #1a1a1a;
}
```

### Change App Name
Edit `config/config.php`:
```php
define('APP_NAME', 'Your Cabinet Name');
```

---

## 📞 Need Help?

1. **Check** `INSTALLATION_SAAS.md` for detailed setup
2. **Read** `SAAS_FEATURES.md` for features overview
3. **Review** admin activity logs for debugging
4. **Contact** support@cabinet360.com

---

## ✅ You're Ready!

Your multi-tenant SaaS is now live! 🎉

**Lawyer Signup**: `yourdomain.com/signup.php`
**Admin Panel**: `yourdomain.com/admin/login.php`

---

*Built for scale. Ready for growth. Cabinet360 SaaS.* ⚖️



