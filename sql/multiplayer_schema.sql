-- Update users table to support 3 roles
ALTER TABLE users 
MODIFY COLUMN role ENUM('super_admin', 'admin', 'user') DEFAULT 'user';

-- Add multiplayer-related fields to users table
ALTER TABLE users 
ADD COLUMN online_status ENUM('online', 'offline', 'away', 'playing') DEFAULT 'offline',
ADD COLUMN last_seen_at DATETIME NULL,
ADD COLUMN max_friends INT DEFAULT 50,
ADD COLUMN is_banned BOOLEAN DEFAULT FALSE,
ADD COLUMN banned_until DATETIME NULL,
ADD COLUMN ban_reason TEXT NULL;

-- Create friends table
CREATE TABLE friends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'blocked') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_friendship (user_id, friend_id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_friend_status (friend_id, status)
);

-- Create matches table
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mode ENUM('solo', 'multiplayer', 'tournament') DEFAULT 'multiplayer',
    match_type ENUM('quick', 'private', 'tournament') DEFAULT 'quick',
    created_by INT NOT NULL,
    title VARCHAR(255),
    max_players INT DEFAULT 4,
    current_players INT DEFAULT 0,
    status ENUM('waiting', 'active', 'completed', 'cancelled') DEFAULT 'waiting',
    start_time DATETIME NULL,
    end_time DATETIME NULL,
    quiz_id INT NULL,
    settings JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_created_by (created_by)
);

-- Create match_players table
CREATE TABLE match_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    user_id INT NOT NULL,
    score INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    total_answers INT DEFAULT 0,
    result ENUM('win', 'lose', 'draw', 'incomplete') DEFAULT 'incomplete',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    finished_at DATETIME NULL,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_match_player (match_id, user_id),
    INDEX idx_user_matches (user_id),
    INDEX idx_match_results (match_id, result)
);

-- Create invitations table
CREATE TABLE invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    match_id INT NULL,
    invitation_type ENUM('friend', 'match') DEFAULT 'friend',
    status ENUM('pending', 'accepted', 'declined', 'expired') DEFAULT 'pending',
    message TEXT,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    INDEX idx_receiver_status (receiver_id, status),
    INDEX idx_sender_status (sender_id, status)
);

-- Create chat_messages table for multiplayer chat
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'system', 'emoji') DEFAULT 'text',
    is_moderated BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_match_messages (match_id, created_at)
);

-- Create tournaments table
CREATE TABLE tournaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    max_participants INT DEFAULT 16,
    current_participants INT DEFAULT 0,
    status ENUM('upcoming', 'active', 'completed', 'cancelled') DEFAULT 'upcoming',
    start_time DATETIME,
    end_time DATETIME,
    prize_pool DECIMAL(10,2) DEFAULT 0,
    entry_fee DECIMAL(10,2) DEFAULT 0,
    rules JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_start_time (start_time)
);

-- Create tournament_participants table
CREATE TABLE tournament_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('registered', 'active', 'eliminated', 'winner') DEFAULT 'registered',
    position INT NULL,
    score INT DEFAULT 0,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tournament_participant (tournament_id, user_id),
    INDEX idx_user_tournaments (user_id)
);

-- Create match_reports table for reporting abusive players
CREATE TABLE match_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    reported_user_id INT NOT NULL,
    match_id INT NOT NULL,
    report_type ENUM('cheating', 'harassment', 'inappropriate_behavior', 'other') DEFAULT 'other',
    description TEXT NOT NULL,
    evidence JSON,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    reviewed_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_reported_user (reported_user_id)
);

-- Create multiplayer_achievements table
CREATE TABLE multiplayer_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    badge_icon VARCHAR(255),
    metric ENUM('matches_won', 'tournaments_won', 'friends_invited', 'matches_played') NOT NULL,
    threshold INT NOT NULL,
    rarity ENUM('common', 'rare', 'epic', 'legendary') DEFAULT 'common'
);

-- Create user_multiplayer_achievements table
CREATE TABLE user_multiplayer_achievements (
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, achievement_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES multiplayer_achievements(id) ON DELETE CASCADE
);

-- Insert default multiplayer achievements
INSERT INTO multiplayer_achievements (name, description, badge_icon, metric, threshold, rarity) VALUES
('First Victory', 'Win your first multiplayer match', 'first_victory.png', 'matches_won', 1, 'common'),
('Social Butterfly', 'Invite 10 friends to play', 'social_butterfly.png', 'friends_invited', 10, 'rare'),
('Tournament Champion', 'Win your first tournament', 'champion.png', 'tournaments_won', 1, 'epic'),
('Match Master', 'Win 50 multiplayer matches', 'match_master.png', 'matches_won', 50, 'legendary');

-- Insert default settings for multiplayer
INSERT INTO settings (`key`, `value`) VALUES 
('max_friends_per_user', '50'),
('max_match_players', '8'),
('match_timeout_minutes', '30'),
('chat_enabled', 'true'),
('tournament_entry_fee', '0'),
('anti_cheat_enabled', 'true');