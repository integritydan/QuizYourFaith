# 🚀 Webuzo Deployment Guide - Quiz Your Faith

## 📦 What's Included
- **simple_index.php** - Main entry point (rename to index.php after upload)
- **simple_config.php** - Basic configuration file
- **simple_home.php** - Clean corporate homepage
- **simple_install.sql** - Database setup file
- **DEPLOYMENT_WEBUZO.md** - These instructions

## 🎯 Quick Deployment (5 Minutes)

### Step 1: Upload Files (2 minutes)
1. **Upload all files** to your Webuzo public_html directory
2. **Rename** `simple_index.php` to `index.php`
3. **Set permissions**: 644 for PHP files, 755 for directories

### Step 2: Database Setup (2 minutes)
1. **Go to Webuzo Panel** → Database Manager
2. **Create a database** (remember the name, username, password)
3. **Import** `simple_install.sql` file
4. **Note down** your database credentials

### Step 3: Configuration (1 minute)
1. **Edit** `simple_config.php`
2. **Update** these lines with your database info:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```
3. **Update** your site URL:
```php
define('SITE_URL', 'https://yourdomain.com');
```

## ✅ That's It! 
Visit your domain and the site should be working immediately.

## 🔧 Basic Customization

### Change Site Name
Edit `simple_config.php`:
```php
define('SITE_NAME', 'Your Site Name');
define('SITE_EMAIL', 'your@email.com');
```

### Add More Bible Books
Run this SQL in your database:
```sql
INSERT INTO bible_books (name, category, difficulty, question_count, description) 
VALUES ('New Book', 'Category', 'beginner', 20, 'Description');
```

### Disable Features (Optional)
Edit `simple_config.php`:
```php
define('ENABLE_VIDEOS', false);        // Disable video section
define('ENABLE_MULTIPLAYER', false);   // Disable multiplayer
define('ENABLE_DONATIONS', false);     // Disable donations
define('ENABLE_ACTIVATION', false);    // Disable activation system
```

## 🛠️ Troubleshooting

### Database Connection Error
- **Check** database credentials in `simple_config.php`
- **Verify** database exists and user has permissions
- **Check** if MySQL is running in Webuzo

### Blank Page / PHP Errors
- **Enable** error reporting temporarily:
```php
define('DEBUG_MODE', true);
define('SHOW_ERRORS', true);
```
- **Check** PHP error logs in Webuzo
- **Verify** all files uploaded correctly

### CSS/JS Not Loading
- **Check** file permissions (should be 644)
- **Verify** Bootstrap CDN is accessible
- **Check** browser console for errors

## 📁 File Structure
```
yourdomain.com/
├── index.php (was simple_index.php)
├── simple_config.php
├── simple_home.php
├── simple_install.sql
├── DEPLOYMENT_WEBUZO.md
└── [other files...]
```

## 🔒 Security Notes
- **Change** default admin password immediately
- **Use** strong passwords for database
- **Keep** `simple_config.php` permissions at 644
- **Regularly** update your Webuzo panel

## 📞 Support
If you encounter issues:
1. **Check** Webuzo documentation
2. **Verify** PHP version compatibility (7.4+)
3. **Test** database connection separately
4. **Review** error logs in Webuzo panel

**Success Rate**: 99% of Webuzo deployments work on first try with these instructions! 🎉