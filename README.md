# 🎯 QuizYourFaith - Multiplayer Bible Quiz Platform

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

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+ with extensions: `pdo_mysql`, `openssl`, `curl`, `json`, `mbstring`
- MySQL 5.7+ or MariaDB 10.2+
- Node.js 14+ (for WebSocket server)
- SSL Certificate (recommended for production)

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/integritydan/QuizYourFaith.git
cd QuizYourFaith
```

2. **Install dependencies**
```bash
# PHP dependencies
composer install

# WebSocket server dependencies
cd websocket && npm install && cd ..
```

3. **Database setup**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE qyf_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run all schemas
mysql -u root -p qyf_db < sql/qyf_v1.sql
mysql -u root -p qyf_db < sql/multiplayer_schema.sql
mysql -u root -p qyf_db < sql/settings_schema.sql
mysql -u root -p qyf_db < sql/update_system_schema.sql
```

4. **Environment configuration**
```bash
# Copy and configure environment file
cp .env.example .env
# Edit .env with your configuration
```

5. **File permissions**
```bash
chmod 755 -R app/ storage/ websocket/
chmod 644 app/config/*.php
```

6. **Start services**
```bash
# Start WebSocket server
cd websocket && npm start

# Configure web server to point to public/ directory
```

## 📋 Configuration Guide

### Environment Variables
Create a `.env` file with:
```env
# Database
DB_HOST=localhost
DB_NAME=qyf_db
DB_USER=your_user
DB_PASS=your_password

# Security
JWT_SECRET=your-secret-key-min-32-chars
SETTINGS_ENCRYPTION_KEY=your-encryption-key-32-chars

# WebSocket
WS_PORT=3001
FRONTEND_URL=https://yourdomain.com
```

### Super Admin Setup
1. Access `/admin/update` after installation
2. Upload your update package (ZIP file)
3. System automatically preserves all user data
4. Configure settings through admin dashboard

## 🔧 System Update Process

### Automatic Updates
1. Navigate to **System Update** in Super Admin panel
2. Upload new version ZIP file
3. System automatically:
   - Creates backup of current version
   - Preserves all user data and progress
   - Maintains database content
   - Runs any necessary migrations
   - Clears caches and optimizes

### Manual Update (if needed)
```bash
# Backup current system
php admin/backup/create.php

# Upload new files
# Run migrations
php admin/migrate/run.php

# Clear caches
php admin/cache/clear.php
```

## 🛡️ Security Best Practices

### Activation Codes Protection
- **Private Repository**: Keep activation codes in private repository
- **Environment Variables**: Store sensitive keys in `.env` (not in code)
- **Access Control**: Only repository owner can view activation files
- **Regular Rotation**: Update activation codes periodically

### Recommended GitHub Settings
1. **Make repository private** if containing activation codes
2. **Enable GitHub Security Features**:
   - Dependabot alerts
   - Code scanning
   - Secret scanning
3. **Branch Protection**: Require reviews for main branch
4. **Access Management**: Limit collaborator access

## 📊 Monitoring & Maintenance

### System Health
- Monitor WebSocket connections
- Track database performance
- Review error logs regularly
- Check backup integrity

### Chat Management
- Messages automatically cleared on user logout
- Admin moderation tools available
- Rate limiting prevents spam
- Profanity filtering enabled

### Update Safety
- Automatic backups before updates
- Rollback capability if update fails
- User data and progress preserved
- Zero-downtime updates possible

## 🎯 Multiplayer Features

### Real-time Gaming
- WebSocket-powered instant communication
- Live score updates
- Real-time chat with moderation
- Tournament brackets and scheduling

### Social Features
- Friend system with invitations
- Private match creation
- Team-based competitions
- Achievement and badge system

### Monetization
- Tournament entry fees
- Donation system
- Premium features (future)
- Advertisement integration ready

## 📞 Support & Documentation

### Getting Help
- Check [COMPLETE_SETUP_GUIDE.md](COMPLETE_SETUP_GUIDE.md) for detailed setup
- Review application logs in `storage/logs/`
- Check WebSocket server console output
- Enable debug mode for troubleshooting

### Contributing
1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

### License
This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

## 🔗 Links

- **Live Demo**: [Your Demo URL]
- **Documentation**: [Wiki/Documentation URL]
- **Issue Tracker**: [GitHub Issues]
- **Security Policy**: [SECURITY.md]

---

**⭐ Star this repository if you find it helpful!**

**💡 For support or questions, please open an issue on GitHub.**