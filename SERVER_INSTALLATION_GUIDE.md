# 🚀 QuizYourFaith - Server Installation Guide

## ✅ **Ready for Production Deployment**

Your QuizYourFaith software is fully prepared for server installation with all features complete:
- ✅ YouTube Video Slider System
- ✅ Feature Toggle Management
- ✅ Clean Repository (no sensitive files)
- ✅ Complete Admin Interface
- ✅ Database Schemas Ready

## 📋 **Pre-Installation Checklist**

### **Server Requirements**
- **PHP**: 7.4 or higher
- **MySQL**: 5.7+ or MariaDB 10.2+
- **Web Server**: Apache/Nginx with SSL support
- **Node.js**: 14+ (for WebSocket server)
- **Storage**: 500MB+ free space
- **Memory**: 1GB+ RAM recommended

### **Required PHP Extensions**
```bash
# Check if these are installed
php -m | grep -E "(pdo_mysql|openssl|curl|json|mbstring)"
```

### **File Permissions**
```bash
# After upload, set these permissions
chmod 755 -R app/ storage/ websocket/
chmod 644 app/config/*.php
chmod 600 .env
```

## 🔧 **Step-by-Step Installation**

### **Step 1: Upload Files**
```bash
# Download from GitHub
git clone https://github.com/integritydan/QuizYourFaith.git
# OR upload ZIP file via FTP/cPanel
```

### **Step 2: Database Setup**
```bash
# Create database
mysql -u root -p
CREATE DATABASE qyf_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit

# Run all SQL schemas in order
mysql -u your_user -p qyf_db < sql/qyf_v1.sql
mysql -u your_user -p qyf_db < sql/multiplayer_schema.sql
mysql -u your_user -p qyf_db < sql/settings_schema.sql
mysql -u your_user -p qyf_db < sql/update_system_schema.sql
mysql -u your_user -p qyf_db < sql/youtube_videos_schema.sql
mysql -u your_user -p qyf_db < sql/features_schema.sql
```

### **Step 3: Environment Configuration**
```bash
# Copy environment file
cp .env.example .env

# Edit .env file with your details
nano .env
```

**Required .env settings:**
```env
# Database
DB_HOST=localhost
DB_NAME=qyf_db
DB_USER=your_database_user
DB_PASS=your_database_password

# Security
JWT_SECRET=your-secret-key-min-32-chars-here
SETTINGS_ENCRYPTION_KEY=your-encryption-key-32-chars-here

# WebSocket
WS_PORT=3001
FRONTEND_URL=https://yourdomain.com
```

### **Step 4: Web Server Configuration**

#### **Apache (.htaccess)**
The `.htaccess` files are already included. Just ensure mod_rewrite is enabled:
```bash
sudo a2enmod rewrite
```

#### **Nginx Configuration**
Add to your server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

### **Step 5: Installation Wizard**
1. **Access Installer**: `https://yourdomain.com/install`
2. **System Check**: Verify all requirements are met
3. **Database Connection**: Test database connectivity
4. **Admin Account**: Create your administrator account
5. **Complete**: Remove install directory after completion

## 🎯 **Post-Installation Setup**

### **1. Feature Configuration**
```bash
# Access admin panel
https://yourdomain.com/admin

# Navigate to Feature Management
# Enable desired features:
# - youtube_videos (for video slider)
# - video_reactions (for like/dislike)
# - video_sharing (for social sharing)
# - multiplayer (for real-time games)
```

### **2. Video System Setup**
```bash
# Go to Video Management
# Add YouTube video URLs for Bible messages
# Organize into categories
# Set display order for homepage slider
```

### **3. WebSocket Server (Optional)**
```bash
# Navigate to websocket directory
cd websocket/

# Install dependencies
npm install

# Start WebSocket server
node server.js

# For production, use PM2
npm install -g pm2
pm2 start server.js --name "quiz-websocket"
pm2 save
pm2 startup
```

## 🔒 **Security Configuration**

### **File Permissions**
```bash
# Secure sensitive files
chmod 600 .env
chmod 644 app/config/*.php
chmod 755 -R app/ storage/

# Protect directories
# Add to .htaccess in root:
<FilesMatch "\.(env|json|lock|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### **SSL Certificate**
```bash
# Install Certbot for Let's Encrypt
sudo apt install certbot python3-certbot-nginx

# Generate SSL certificate
sudo certbot --nginx -d yourdomain.com
```

## 🐛 **Troubleshooting**

### **Common Issues & Solutions**

#### **Database Connection Error**
```bash
# Check MySQL service
sudo systemctl status mysql

# Verify credentials
mysql -u your_user -p your_database
```

#### **Permission Errors**
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/quizyourfaith/

# Fix permissions
find . -type d -exec chmod 755 {} \;
find . -name "*.php" -exec chmod 644 {} \;
chmod 777 storage/logs/ storage/cache/
```

#### **WebSocket Connection Issues**
```bash
# Check if port is open
sudo netstat -tulpn | grep :3001

# Allow port in firewall
sudo ufw allow 3001
```

#### **Feature Toggle Not Working**
```bash
# Run feature schema again
mysql -u your_user -p qyf_db < sql/features_schema.sql

# Check if features table exists
mysql -u your_user -p qyf_db -e "SELECT * FROM features LIMIT 1"
```

## 📊 **Verification Steps**

### **1. Basic Functionality**
- [ ] Homepage loads without errors
- [ ] Admin login works
- [ ] Feature management accessible
- [ ] Video slider displays (if enabled)

### **2. Feature Toggles**
- [ ] Can enable/disable features in admin panel
- [ ] Navigation updates based on feature status
- [ ] Video section respects toggle settings

### **3. Video System**
- [ ] YouTube videos play correctly
- [ ] Admin can add/edit/delete videos
- [ ] Video reactions work (if enabled)
- [ ] Social sharing functions (if enabled)

### **4. Database Integration**
- [ ] All tables created successfully
- [ ] Feature toggles persist after restart
- [ ] Video data saves correctly

## 🚀 **Performance Optimization**

### **Caching**
```bash
# Enable OPcache in php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
```

### **Database Optimization**
```sql
-- Add these indexes for better performance
ALTER TABLE features ADD INDEX idx_enabled_name (is_enabled, name);
ALTER TABLE youtube_videos ADD INDEX idx_active_order (is_active, display_order);
ALTER TABLE video_views ADD INDEX idx_video_date (video_id, viewed_at);
```

## 📞 **Support**

If you encounter issues during installation:

1. **Check Logs**: Look in `storage/logs/` for error details
2. **Verify Requirements**: Ensure all PHP extensions are installed
3. **Test Database**: Confirm MySQL connectivity
4. **Review Permissions**: Check file/directory permissions
5. **Contact Support**: Check GitHub issues or documentation

---

## ✅ **Installation Complete!**

Your QuizYourFaith platform is now ready for use with:
- 🎬 YouTube video messages slider
- ⚙️ Complete feature toggle control
- 🔒 Secure activation-free installation
- 📱 Mobile-responsive design
- 🎮 Multiplayer quiz functionality

**Access your admin panel at**: `https://yourdomain.com/admin`

Enjoy your fully functional Bible quiz platform! 🙏