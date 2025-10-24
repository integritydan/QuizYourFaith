# QuizYourFaith - Complete Setup Guide with Settings & Integrations

## 🎯 Overview

This comprehensive guide covers the complete setup of QuizYourFaith with:
- **3 User Levels**: Super Admin, Admin, User
- **Multiplayer Features**: Real-time gaming, friends, tournaments
- **OAuth Integration**: Google, Facebook, Twitter, GitHub login
- **Payment Gateways**: Paystack, PayPal, Stripe, Flutterwave
- **Email Services**: SMTP, SendGrid, Mailgun, Amazon SES
- **Security Features**: Encryption, 2FA, rate limiting
- **Settings Management**: Comprehensive admin dashboard

## 📋 Prerequisites

- PHP 7.4+ with extensions: `pdo_mysql`, `openssl`, `curl`, `json`, `mbstring`
- MySQL 5.7+ or MariaDB 10.2+
- Node.js 14+ (for WebSocket server)
- SSL Certificate (recommended for production)
- Domain name (for OAuth callbacks)

## 🚀 Quick Start

### 1. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE qyf_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run all schemas
mysql -u root -p qyf_db < sql/qyf_v1.sql
mysql -u root -p qyf_db < sql/multiplayer_schema.sql
mysql -u root -p qyf_db < sql/settings_schema.sql
mysql -u root -p qyf_db < sql/payment_keys.sql
mysql -u root -p qyf_db < sql/logo_setting.sql

# Create super admin user
mysql -u root -p qyf_db -e "INSERT INTO users (name, email, password, role, created_at) VALUES ('Super Admin', 'admin@quizyourfaith.com', '$(php -r "echo password_hash('admin123', PASSWORD_DEFAULT);")', 'super_admin', NOW());"
```

### 2. Environment Configuration

Create `.env` file in root directory:

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_HOST=localhost
DB_NAME=qyf_db
DB_USER=your_db_user
DB_PASS=your_db_password

# Security Keys
JWT_SECRET=your-super-secret-jwt-key-min-32-chars
SETTINGS_ENCRYPTION_KEY=your-encryption-key-for-settings-32-chars

# WebSocket
WS_PORT=3001
FRONTEND_URL=https://yourdomain.com

# Email (Configure after setup)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password

# OAuth (Configure after setup)
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret

# Payment Gateways (Configure after setup)
PAYSTACK_PUBLIC_KEY=your-paystack-public-key
PAYSTACK_SECRET_KEY=your-paystack-secret-key
PAYSTACK_WEBHOOK_SECRET=your-paystack-webhook-secret

# Social Media (Configure after setup)
FACEBOOK_APP_ID=your-facebook-app-id
FACEBOOK_APP_SECRET=your-facebook-app-secret
TWITTER_API_KEY=your-twitter-api-key
TWITTER_API_SECRET=your-twitter-api-secret
```

### 3. Install Dependencies

```bash
# PHP dependencies
composer require firebase/php-jwt phpmailer/phpmailer

# WebSocket server
cd websocket
npm install

# Return to root
cd ..
```

### 4. File Permissions

```bash
chmod 755 -R app/
chmod 644 app/config/*.php
chmod 755 -R storage/
chmod 755 -R websocket/
```

## 🔧 Settings Configuration

### Access Settings Dashboard
1. Login as Super Admin
2. Navigate to: `/admin/settings`
3. Configure each section as described below

---

## 🔐 Authentication Settings

### Google OAuth Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create new project or select existing
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URI: `https://yourdomain.com/auth/oauth/google/callback`
6. Copy Client ID and Secret to settings

### Facebook OAuth Setup
1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create new app
3. Add Facebook Login product
4. Configure OAuth redirect URI: `https://yourdomain.com/auth/oauth/facebook/callback`
5. Copy App ID and Secret to settings

### Security Settings
- **Max Login Attempts**: 5 (recommended)
- **Lockout Duration**: 15 minutes
- **Session Timeout**: 60 minutes
- **Enable 2FA**: Recommended for admin accounts
- **Force SSL**: Enable in production

---

## 💳 Payment Gateway Configuration

### Paystack Setup (Recommended for Africa)
1. Create account at [Paystack](https://paystack.com)
2. Go to Settings → API Keys & Webhooks
3. Copy Public Key and Secret Key
4. Set webhook URL: `https://yourdomain.com/webhooks/paystack`
5. Configure webhook secret

### PayPal Setup
1. Create business account at [PayPal](https://paypal.com)
2. Go to Developer Dashboard
3. Create REST API app
4. Copy Client ID and Secret
5. Set webhook URL: `https://yourdomain.com/webhooks/paypal`

### Stripe Setup
1. Create account at [Stripe](https://stripe.com)
2. Go to Developers → API Keys
3. Copy Publishable Key and Secret Key
4. Set webhook endpoint: `https://yourdomain.com/webhooks/stripe`
5. Configure webhook signing secret

### Flutterwave Setup
1. Create account at [Flutterwave](https://flutterwave.com)
2. Go to Settings → API
3. Copy Public Key and Secret Key
4. Set webhook URL: `https://yourdomain.com/webhooks/flutterwave`

---

## 📧 Email Service Configuration

### SMTP Setup (Recommended)
1. Use Gmail App Password or SMTP service
2. Configure in Email Settings tab:
   - Host: `smtp.gmail.com`
   - Port: `587`
   - Encryption: `TLS`
   - Username: Your email
   - Password: App password

### SendGrid Setup
1. Create account at [SendGrid](https://sendgrid.com)
2. Create API key with full access
3. Configure in settings with API key

### Mailgun Setup
1. Create account at [Mailgun](https://mailgun.com)
2. Add your domain
3. Get API key from settings
4. Configure in email settings

---

## 🎮 Multiplayer Configuration

### WebSocket Server Setup
```bash
# Start WebSocket server
cd websocket
npm start

# Or use PM2 for production
npm install -g pm2
pm2 start server.js --name "quiz-websocket"
pm2 startup
pm2 save
```

### Match Settings
- **Max Players**: 8 (recommended)
- **Match Timeout**: 30 minutes
- **Chat Enabled**: Yes
- **Anti-cheat**: Enabled

### Tournament Settings
- **Max Participants**: 16-64 (based on server capacity)
- **Entry Fee**: Configurable per tournament
- **Prize Pool**: Automatic calculation

---

## 🔒 Security Configuration

### API Security
- **Rate Limiting**: 100 requests/minute
- **JWT Expiration**: 1 hour
- **API Key Rotation**: Monthly recommended

### Backup Settings
- **Frequency**: Weekly recommended
- **Retention**: 30 days
- **Storage**: Local or cloud (S3, Google Drive)
- **Encryption**: Always enabled

### Password Requirements
- Minimum length: 8 characters
- Require uppercase, lowercase, numbers, special characters
- Password history: 5 previous passwords

---

## 📊 Analytics & Monitoring

### System Health
- Monitor WebSocket connections
- Track API response times
- Monitor database performance
- Set up error logging

### User Analytics
- Registration trends
- Match participation rates
- Payment conversion rates
- User retention metrics

---

## 🚨 Troubleshooting

### Common Issues

**1. OAuth Login Fails**
- Check redirect URIs match exactly
- Verify client credentials are correct
- Ensure HTTPS in production
- Check OAuth provider status

**2. Payment Gateway Errors**
- Verify API keys are correct
- Check webhook URLs are accessible
- Ensure currency support
- Review transaction logs

**3. WebSocket Connection Issues**
- Check firewall settings
- Verify WebSocket port is open
- Check SSL certificate validity
- Monitor server resources

**4. Email Not Sending**
- Verify SMTP credentials
- Check spam folder
- Test with different provider
- Review email logs

### Debug Mode
Enable debug mode in `.env`:
```env
APP_DEBUG=true
```

Check logs in:
- `storage/logs/` (application logs)
- WebSocket console output
- Browser developer tools

---

## 🔄 Maintenance

### Regular Tasks
1. **Daily**: Monitor system health, review error logs
2. **Weekly**: Check backup integrity, review security logs
3. **Monthly**: Rotate API keys, update dependencies
4. **Quarterly**: Security audit, performance review

### Updates
```bash
# Update dependencies
composer update
cd websocket && npm update

# Clear caches
rm -rf storage/cache/*
```

### Database Maintenance
```sql
-- Optimize tables
OPTIMIZE TABLE users, matches, friends;

-- Clean old logs
DELETE FROM logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Archive old matches
UPDATE matches SET status = 'archived' WHERE end_time < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## 📞 Support & Resources

### Getting Help
1. Check this guide first
2. Review application logs
3. Check browser console for errors
4. Test with minimal configuration

### Useful Links
- [PHP Documentation](https://php.net)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Socket.io Documentation](https://socket.io/docs/)
- [JWT Documentation](https://jwt.io/)

### Security Resources
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://phpsecurity.readthedocs.io/)
- [Web Application Security](https://cheatsheetseries.owasp.org/)

---

## ✅ Post-Setup Checklist

- [ ] All settings configured and tested
- [ ] OAuth providers working
- [ ] Payment gateways tested
- [ ] Email service functional
- [ ] WebSocket server running
- [ ] Security settings applied
- [ ] Backup system configured
- [ ] Monitoring setup complete
- [ ] SSL certificate installed
- [ ] Error logging enabled
- [ ] Performance optimized
- [ ] Documentation updated

## 🎉 Congratulations!

Your QuizYourFaith platform is now fully configured with:
- ✅ 3-tier user management system
- ✅ Real-time multiplayer gaming
- ✅ Secure payment processing
- ✅ Social OAuth integration
- ✅ Comprehensive admin controls
- ✅ Advanced security features
- ✅ Professional settings management

The system is ready for production deployment with enterprise-grade features and security.