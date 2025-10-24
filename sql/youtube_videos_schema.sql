-- YouTube Videos Slider Schema for QuizYourFaith
-- This schema supports admin-managed YouTube video messages

CREATE TABLE youtube_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    youtube_url VARCHAR(500) NOT NULL,
    youtube_video_id VARCHAR(50) NOT NULL,
    thumbnail_url VARCHAR(500),
    duration VARCHAR(20),
    category VARCHAR(100),
    tags TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    views_count INT DEFAULT 0,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at DATETIME NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_active_videos (is_active, display_order),
    INDEX idx_published (published_at)
);

-- Create video categories table for better organization
CREATE TABLE video_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#007bff',
    icon VARCHAR(50) DEFAULT 'video',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Add category reference to videos table
ALTER TABLE youtube_videos 
ADD COLUMN category_id INT NULL,
ADD FOREIGN KEY (category_id) REFERENCES video_categories(id) ON DELETE SET NULL,
ADD INDEX idx_category (category_id);

-- Insert default video categories
INSERT INTO video_categories (name, slug, description, color, icon) VALUES
('Bible Teachings', 'bible-teachings', 'In-depth Bible study and teachings', '#28a745', 'book'),
('Life Lessons', 'life-lessons', 'Practical life application from Scripture', '#17a2b8', 'heart'),
('Inspirational Messages', 'inspirational', 'Uplifting and motivational messages', '#ffc107', 'star'),
('Prayer & Worship', 'prayer-worship', 'Messages about prayer and worship', '#6f42c1', 'pray'),
('Youth & Family', 'youth-family', 'Messages for young people and families', '#fd7e14', 'users');

-- Create video views tracking table for analytics
CREATE TABLE video_views (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    watched_duration INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES youtube_videos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_video_views (video_id, viewed_at),
    INDEX idx_user_views (user_id, viewed_at)
);

-- Create video likes/dislikes table for engagement
CREATE TABLE video_reactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction ENUM('like', 'dislike') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES youtube_videos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_reaction (video_id, user_id)
);