-- Settings Management System Schema
-- This extends the existing multiplayer schema with comprehensive settings

-- Settings categories for better organization
CREATE TABLE setting_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    display_order INT DEFAULT 0,
    icon VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default setting categories
INSERT INTO setting_categories (name, description, display_order, icon) VALUES
('general', 'General application settings', 1, 'fas fa-cog'),
('authentication', 'OAuth and authentication settings', 2, 'fas fa-key'),
('payment', 'Payment gateway configuration', 3, 'fas fa-credit-card'),
('email', 'Email service configuration', 4, 'fas fa-envelope'),
('social', 'Social media integration', 5, 'fas fa-share-alt'),
('security', 'Security and backup settings', 6, 'fas fa-shield-alt'),
('multiplayer', 'Multiplayer and game settings', 7, 'fas fa-gamepad'),
('api', 'API keys and external services', 8, 'fas fa-plug');

-- Settings table with encryption support
CREATE TABLE settings_encrypted (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    key_name VARCHAR(255) NOT NULL UNIQUE,
    value TEXT,
    value_encrypted TEXT,
    is_encrypted BOOLEAN DEFAULT FALSE,
    data_type ENUM('string', 'integer', 'boolean', 'json', 'array') DEFAULT 'string',
    description TEXT,
    validation_rules JSON,
    options JSON,
    is_sensitive BOOLEAN DEFAULT FALSE,
    is_required BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES setting_categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_key (key_name)
);

-- Settings history for audit trail
CREATE TABLE settings_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_id INT NOT NULL,
    user_id INT,
    old_value TEXT,
    new_value TEXT,
    change_type ENUM('created', 'updated', 'deleted') DEFAULT 'updated',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (setting_id) REFERENCES settings_encrypted(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_setting (setting_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
);

-- OAuth providers configuration
CREATE TABLE oauth_providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100),
    client_id VARCHAR(255),
    client_secret VARCHAR(255),
    client_secret_encrypted TEXT,
    redirect_uri TEXT,
    scopes TEXT,
    additional_params JSON,
    is_enabled BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default OAuth providers
INSERT INTO oauth_providers (name, display_name, is_enabled, display_order) VALUES
('google', 'Google', FALSE, 1),
('facebook', 'Facebook', FALSE, 2),
('twitter', 'Twitter', FALSE, 3),
('github', 'GitHub', FALSE, 4),
('microsoft', 'Microsoft', FALSE, 5);

-- Payment gateways configuration
CREATE TABLE payment_gateways (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100),
    gateway_type ENUM('paystack', 'paypal', 'stripe', 'flutterwave', 'razorpay') NOT NULL,
    public_key VARCHAR(255),
    secret_key VARCHAR(255),
    secret_key_encrypted TEXT,
    merchant_id VARCHAR(255),
    webhook_secret VARCHAR(255),
    webhook_secret_encrypted TEXT,
    test_mode BOOLEAN DEFAULT TRUE,
    is_enabled BOOLEAN DEFAULT FALSE,
    additional_config JSON,
    display_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default payment gateways
INSERT INTO payment_gateways (name, display_name, gateway_type, display_order) VALUES
('paystack', 'Paystack', 'paystack', 1),
('paypal', 'PayPal', 'paypal', 2),
('stripe', 'Stripe', 'stripe', 3),
('flutterwave', 'Flutterwave', 'flutterwave', 4);

-- Email service providers
CREATE TABLE email_providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100),
    provider_type ENUM('smtp', 'sendgrid', 'mailgun', 'amazon_ses') NOT NULL,
    host VARCHAR(255),
    port INT,
    username VARCHAR(255),
    password VARCHAR(255),
    password_encrypted TEXT,
    encryption ENUM('none', 'tls', 'ssl') DEFAULT 'tls',
    api_key VARCHAR(255),
    api_key_encrypted TEXT,
    from_address VARCHAR(255),
    from_name VARCHAR(255),
    test_mode BOOLEAN DEFAULT TRUE,
    is_enabled BOOLEAN DEFAULT FALSE,
    additional_config JSON,
    display_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default email providers
INSERT INTO email_providers (name, display_name, provider_type, display_order) VALUES
('smtp', 'SMTP', 'smtp', 1),
('sendgrid', 'SendGrid', 'sendgrid', 2),
('mailgun', 'Mailgun', 'mailgun', 3),
('amazon_ses', 'Amazon SES', 'amazon_ses', 4);

-- Social media platforms
CREATE TABLE social_platforms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100),
    platform_type ENUM('facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'discord') NOT NULL,
    page_url VARCHAR(255),
    api_key VARCHAR(255),
    api_key_encrypted TEXT,
    api_secret VARCHAR(255),
    api_secret_encrypted TEXT,
    access_token VARCHAR(500),
    access_token_encrypted TEXT,
    is_enabled BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default social platforms
INSERT INTO social_platforms (name, display_name, platform_type, display_order) VALUES
('facebook_page', 'Facebook', 'facebook', 1),
('twitter_account', 'Twitter', 'twitter', 2),
('instagram_account', 'Instagram', 'instagram', 3),
('discord_server', 'Discord', 'discord', 4);

-- API keys management
CREATE TABLE api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    key_value VARCHAR(255),
    key_value_encrypted TEXT,
    service_name VARCHAR(100),
    description TEXT,
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    expires_at DATETIME,
    last_used_at DATETIME,
    usage_count INT DEFAULT 0,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_service (service_name),
    INDEX idx_active (is_active),
    INDEX idx_expires (expires_at)
);

-- Backup configuration
CREATE TABLE backup_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_type ENUM('database', 'files', 'full') NOT NULL,
    frequency ENUM('daily', 'weekly', 'monthly') DEFAULT 'weekly',
    retention_days INT DEFAULT 30,
    storage_provider ENUM('local', 's3', 'google_drive', 'dropbox') DEFAULT 'local',
    storage_config JSON,
    encryption_key VARCHAR(255),
    encryption_key_encrypted TEXT,
    notification_email VARCHAR(255),
    is_enabled BOOLEAN DEFAULT FALSE,
    last_backup_at DATETIME,
    next_backup_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Security settings
CREATE TABLE security_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default security settings
INSERT INTO security_settings (setting_key, value, description) VALUES
('max_login_attempts', '5', 'Maximum failed login attempts before temporary lockout'),
('lockout_duration_minutes', '15', 'Duration of temporary lockout in minutes'),
('password_min_length', '8', 'Minimum password length requirement'),
('password_require_uppercase', 'true', 'Require uppercase letters in passwords'),
('password_require_lowercase', 'true', 'Require lowercase letters in passwords'),
('password_require_numbers', 'true', 'Require numbers in passwords'),
('password_require_special', 'true', 'Require special characters in passwords'),
('session_timeout_minutes', '60', 'Session timeout in minutes'),
('enable_2fa', 'false', 'Enable two-factor authentication'),
('api_rate_limit', '100', 'API rate limit per minute'),
('enable_captcha', 'false', 'Enable CAPTCHA on login'),
('force_ssl', 'true', 'Force SSL/HTTPS connections');

-- Insert default settings
INSERT INTO settings_encrypted (category_id, key_name, value, data_type, description, is_required, display_order) VALUES
(1, 'site_name', 'QuizYourFaith', 'string', 'Website name', TRUE, 1),
(1, 'site_description', 'Test your Bible knowledge with friends!', 'string', 'Website description', FALSE, 2),
(1, 'site_logo', '/assets/img/logo.png', 'string', 'Website logo URL', FALSE, 3),
(1, 'maintenance_mode', 'false', 'boolean', 'Enable maintenance mode', FALSE, 4),
(1, 'timezone', 'UTC', 'string', 'Application timezone', TRUE, 5),

(2, 'google_oauth_enabled', 'false', 'boolean', 'Enable Google OAuth login', FALSE, 1),
(2, 'facebook_oauth_enabled', 'false', 'boolean', 'Enable Facebook OAuth login', FALSE, 2),
(2, 'registration_enabled', 'true', 'boolean', 'Allow new user registration', TRUE, 3),
(2, 'email_verification_required', 'false', 'boolean', 'Require email verification', FALSE, 4),

(3, 'default_payment_gateway', 'paystack', 'string', 'Default payment gateway', TRUE, 1),
(3, 'currency', 'USD', 'string', 'Default currency', TRUE, 2),
(3, 'currency_symbol', '$', 'string', 'Currency symbol', TRUE, 3),

(4, 'default_email_provider', 'smtp', 'string', 'Default email provider', TRUE, 1),
(4, 'admin_email', 'admin@quizyourfaith.com', 'string', 'Admin email address', TRUE, 2),

(5, 'enable_social_login', 'true', 'boolean', 'Enable social login buttons', FALSE, 1),
(5, 'enable_social_sharing', 'true', 'boolean', 'Enable social sharing', FALSE, 2),

(6, 'enable_backups', 'true', 'boolean', 'Enable automatic backups', FALSE, 1),
(6, 'backup_frequency', 'weekly', 'string', 'Backup frequency', FALSE, 2),

(7, 'max_match_players', '8', 'integer', 'Maximum players per match', TRUE, 1),
(7, 'match_timeout_minutes', '30', 'integer', 'Match timeout in minutes', TRUE, 2),
(7, 'chat_enabled', 'true', 'boolean', 'Enable chat in matches', TRUE, 3),

(8, 'enable_api', 'true', 'boolean', 'Enable API access', FALSE, 1),
(8, 'api_rate_limit', '100', 'integer', 'API rate limit per minute', FALSE, 2);