-- Update System Schema
-- This schema supports the automatic update system with data preservation

-- System updates tracking
CREATE TABLE system_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(50) NOT NULL,
    backup_path TEXT,
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    applied_by INT,
    status ENUM('success', 'failed', 'rolled_back') DEFAULT 'success',
    notes TEXT,
    FOREIGN KEY (applied_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_applied_at (applied_at),
    INDEX idx_version (version)
);

-- Migrations tracking
CREATE TABLE migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    executed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_executed_at (executed_at)
);

-- Backup tracking
CREATE TABLE backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_path TEXT NOT NULL,
    backup_type ENUM('full', 'database', 'files') DEFAULT 'full',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    size_bytes BIGINT,
    checksum VARCHAR(64),
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_created_at (created_at),
    INDEX idx_active (is_active)
);

-- Update configuration settings
INSERT INTO settings_encrypted (category_id, key_name, value, data_type, description, is_required, display_order) VALUES
(6, 'auto_backup_before_update', 'true', 'boolean', 'Automatically create backup before system update', FALSE, 1),
(6, 'update_notification_email', '', 'string', 'Email address for update notifications', FALSE, 2),
(6, 'max_backup_retention_days', '30', 'integer', 'Maximum days to retain backups', FALSE, 3),
(6, 'update_check_frequency', 'daily', 'string', 'How often to check for updates (daily, weekly, monthly)', FALSE, 4),
(6, 'enable_auto_updates', 'false', 'boolean', 'Enable automatic security updates', FALSE, 5);

-- Insert initial system update record
INSERT INTO system_updates (version, applied_at, applied_by, status, notes) VALUES 
('1.0.0', NOW(), 1, 'success', 'Initial system installation');

-- Create backup directory structure
-- This would be created by the application, but included here for reference
-- storage/backups/ - Main backup directory
-- storage/updates/ - Update packages directory  
-- storage/temp/ - Temporary files during updates
-- storage/archives/ - Archived old versions