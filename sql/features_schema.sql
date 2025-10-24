-- Feature Toggle System Schema for QuizYourFaith
-- This schema supports dynamic feature management

CREATE TABLE features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(150) NOT NULL,
    description TEXT,
    is_enabled BOOLEAN DEFAULT FALSE,
    category VARCHAR(50) DEFAULT 'general',
    requires_permission VARCHAR(50) NULL,
    dependencies TEXT NULL, -- JSON array of feature names
    config_data TEXT NULL, -- JSON configuration data
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    enabled_at DATETIME NULL,
    disabled_at DATETIME NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_enabled (is_enabled),
    INDEX idx_category (category),
    INDEX idx_name (name)
);

-- Feature categories for better organization
CREATE TABLE feature_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#6c757d',
    icon VARCHAR(50) DEFAULT 'cog',
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Feature audit log for tracking changes
CREATE TABLE feature_audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    feature_id INT NOT NULL,
    action ENUM('enabled', 'disabled', 'created', 'updated', 'deleted') NOT NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (feature_id) REFERENCES features(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_feature_audit (feature_id, created_at),
    INDEX idx_user_audit (user_id, created_at)
);

-- Insert default feature categories
INSERT INTO feature_categories (name, display_name, description, color, icon, sort_order) VALUES
('core', 'Core Features', 'Essential platform features', '#dc3545', 'star', 1),
('content', 'Content Management', 'Features related to content creation and management', '#007bff', 'edit', 2),
('social', 'Social Features', 'User interaction and social features', '#28a745', 'users', 3),
('monetization', 'Monetization', 'Payment and donation related features', '#ffc107', 'dollar-sign', 4),
('advanced', 'Advanced Features', 'Optional advanced functionality', '#6f42c1', 'cogs', 5);

-- Insert default features
INSERT INTO features (name, display_name, description, is_enabled, category, requires_permission) VALUES
-- Core Features (always enabled)
('user_registration', 'User Registration', 'Allow new users to register', TRUE, 'core', NULL),
('user_login', 'User Login', 'Allow users to login', TRUE, 'core', NULL),
('quiz_system', 'Quiz System', 'Enable quiz functionality', TRUE, 'core', NULL),
('leaderboard', 'Leaderboard', 'Show user rankings and statistics', TRUE, 'core', NULL),

-- Content Features
('youtube_videos', 'YouTube Video Messages', 'Display YouTube video messages slider', TRUE, 'content', 'admin'),
('video_categories', 'Video Categories', 'Enable video categorization', TRUE, 'content', 'admin'),
('video_reactions', 'Video Reactions', 'Allow users to like/dislike videos', TRUE, 'content', NULL),
('video_sharing', 'Video Sharing', 'Enable social media sharing for videos', TRUE, 'content', NULL),

-- Social Features
('multiplayer', 'Multiplayer Mode', 'Enable real-time multiplayer quizzes', TRUE, 'social', NULL),
('friend_system', 'Friend System', 'Allow users to add friends', TRUE, 'social', NULL),
('chat_system', 'Chat System', 'Enable in-game chat functionality', TRUE, 'social', NULL),

-- Monetization Features
('donations', 'Donation System', 'Enable donation functionality', TRUE, 'monetization', 'admin'),
('payment_gateways', 'Payment Gateways', 'Enable payment processing', TRUE, 'monetization', 'admin'),

-- Advanced Features
('user_achievements', 'User Achievements', 'Enable achievement system', FALSE, 'advanced', NULL),
('advanced_analytics', 'Advanced Analytics', 'Show detailed analytics and reports', FALSE, 'advanced', 'super_admin'),
('api_access', 'API Access', 'Enable API endpoints for external integration', FALSE, 'advanced', 'super_admin');

-- Create a view for active features
CREATE VIEW active_features AS
SELECT f.*, fc.display_name as category_display_name, fc.color as category_color, fc.icon as category_icon
FROM features f
JOIN feature_categories fc ON f.category = fc.name
WHERE f.is_enabled = TRUE AND fc.is_active = TRUE;