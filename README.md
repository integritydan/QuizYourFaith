# 🎯 QuizYourFaith - Multiplayer Bible Quiz Platform

**Developer: Daniel Onnoriode**

[![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)](https://github.com/integritydan/QuizYourFaith)
[![PHP Version](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## 🌟 Overview

QuizYourFaith is a comprehensive multiplayer Bible quiz platform featuring real-time gaming, social interactions, tournament management, and secure payment processing. Built with modern web technologies and enterprise-grade security.

## ✨ Key Features

### 👥 Multiplayer Gaming
- **Real-time Matches**: Play against friends or random opponents
- **Live Chat**: Interactive messaging during matches
- **Tournament System**: Compete in organized tournaments
- **Friend System**: Add friends and invite to matches
- **Leaderboards**: Track rankings and achievements

### 🔐 User Management (3-Tier System)
- **Super Admin**: Full system control and configuration
- **Admin**: Game moderation and community management
- **User**: Regular gameplay with social features

### 💳 Payment Integration
- **Multiple Gateways**: Paystack, PayPal, Stripe, Flutterwave
- **Secure Processing**: Encrypted payment handling
- **Donation System**: Support the platform financially
- **Tournament Entry Fees**: Monetize competitions

### 🔗 Social Authentication
- **Google OAuth**: Secure Google login integration
- **Facebook Login**: Facebook authentication support
- **Traditional Login**: Email/password registration

### ⚙️ Advanced Settings
- **Comprehensive Admin Panel**: Complete system configuration
- **Encrypted Settings**: Secure storage of sensitive data
- **API Key Management**: Secure API credential handling
- **Email Configuration**: Multiple email service providers

### 🛡️ Security Features
- **AES-256 Encryption**: All sensitive data encrypted
- **JWT Authentication**: Secure token-based authentication
- **Rate Limiting**: Protection against abuse
- **CSRF Protection**: Form security measures
- **Chat Moderation**: Content filtering and admin controls

## 🚀 Shared Server Installation Guide

### Prerequisites
- PHP 7.4+ with extensions: `pdo_mysql`, `openssl`, `curl`, `json`, `mbstring`
- MySQL 5.7+ or MariaDB 10.2+
- Node.js 14+ (for WebSocket server)
- SSL Certificate (recommended for production)

### Step 1: Upload Files to Shared Server
1. **Download from GitHub**: https://github.com/integritydan/QuizYourFaith
2. **Upload via FTP/cPanel**: Extract and upload all files to your web directory
3. **Set Permissions**:
   ```bash
   chmod 755 -R app/ storage/ websocket/
   chmod 644 app/config/*.php
   ```

### Step 2: Database Setup
1. **Create Database** in your hosting control panel:
   ```sql
   CREATE DATABASE qyf_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Run SQL Schemas** via phpMyAdmin or command line:
   ```bash
   mysql -u your_user -p your_database < sql/qyf_v1.sql
   mysql -u your_user -p your_database < sql/multiplayer_schema.sql
   mysql -u your_user -p your_database < sql/settings_schema.sql
   mysql -u your_user -p your_database < sql/update_system_schema.sql
   ```

### Step 3: Environment Configuration
1. **Create `.env` file** in the root directory:
   ```env
   # Database
   DB_HOST=localhost
   DB_NAME=your_database_name
   DB_USER=your_database_user
   DB_PASS=your_database_password
   
   # Security
   JWT_SECRET=your-secret-key-min-32-chars-here
   SETTINGS_ENCRYPTION_KEY=your-encryption-key-32-chars-here
   
   # WebSocket
   WS_PORT=3001
   FRONTEND_URL=https://yourdomain.com
   ```

2. **Secure the `.env` file**:
   ```bash
   chmod 600 .env
   ```

### Step 4: Web Server Configuration
1. **Point domain** to the `public/` directory
2. **Configure SSL certificate** for HTTPS
3. **Set up WebSocket proxy** (if needed):

For Apache (.htaccess in public/):
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

For Nginx:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Step 5: Run Installation Wizard
1. **Access installer**: `https://yourdomain.com/install`
2. **Follow 3-step process**:
   - System requirements check
   - Database connection setup
   - Admin account creation

### Step 6: Post-Installation Setup
1. **Access admin panel**: `https://yourdomain.com/admin`
2. **Upload activation codes** through secure admin interface
3. **Configure payment gateways** in settings
4. **Set up email providers** for notifications
5. **Test multiplayer functionality**

## 🔧 Webuzo Server Specific Instructions

### For Webuzo Control Panel:
1. **Create Application** in Webuzo App Manager
2. **Upload ZIP file** through file manager
3. **Create MySQL Database** in Database section
4. **Run installer** via web interface
5. **Configure cron jobs** for automated tasks:
   ```bash
   # Add to cron jobs in Webuzo
   */5 * * * * php /path/to/cron/leaderboard.php
   ```

## 🛡️ Security Configuration for Shared Hosting

### File Permissions (Critical)
```bash
# Directories
find . -type d -exec chmod 755 {} \;

# PHP files
find . -name "*.php" -exec chmod 644 {} \;

# Writable directories
chmod 777 storage/logs/
chmod 777 storage/cache/
```

### .htaccess Security Rules
Create `.htaccess` in root directory:
```apache
# Deny access to sensitive files
<FilesMatch "\.(env|json|lock|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect sensitive directories
RedirectMatch 404 /\.git
RedirectMatch 404 /config/
RedirectMatch 404 /storage/
```

## 📋 Activation Code Management

### For Shared Server:
1. **Access admin panel** at `/admin/activation`
2. **Upload codes** through secure interface
3. **Codes are stored encrypted** in database
4. **Never store activation files** in web-accessible directories

### Security Notes:
- Keep activation codes private and secure
- Regular rotation recommended
- Monitor usage through admin dashboard
- Backup codes regularly

## 🔧 System Update Process

### Automatic Updates (Recommended)
1. Navigate to **System Update** in Super Admin panel
2. Upload new version ZIP file
3. System automatically:
   - Creates backup of current version
   - Preserves all user data and progress
   - Maintains database content
   - Runs any necessary migrations
   - Clears caches and optimizes

### Manual Backup (Before Updates)
```bash
# Create backup via admin panel
# Or use: php admin/backup/create.php
```

## 📞 Support & Troubleshooting

### Common Issues:
1. **Database Connection Errors**:
   - Verify credentials in `.env` file
   - Check database exists and is accessible
   - Ensure proper user permissions

2. **WebSocket Connection Issues**:
   - Check if port 3001 is available
   - Verify WebSocket server is running
   - Check firewall settings

3. **Permission Errors**:
   - Ensure proper file permissions (755 for dirs, 644 for files)
   - Check .htaccess configuration
   - Verify PHP version compatibility

### Getting Help:
- Check application logs in `storage/logs/`
- Enable debug mode temporarily for troubleshooting
- Review WebSocket server console output
- Contact your hosting provider for server-specific issues

---

## 🎉 Ready for Production!

**Your QuizYourFaith platform is fully configured and secured for shared server installation!**

**Developer: Daniel Onnoriode**  
**Status: ✅ READY FOR SHARED SERVER DEPLOYMENT** 🚀

Follow the steps above to install on your shared hosting or Webuzo server. The platform includes enterprise-grade security and is optimized for shared hosting environments.