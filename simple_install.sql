-- Simple Database Setup for Webuzo
-- Just run this SQL file in your Webuzo database manager

-- Create tables with minimal complexity
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bible_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    question_count INT DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bible_book_id INT,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bible_book_id) REFERENCES bible_books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    bible_book_id INT,
    score INT DEFAULT 0,
    total_questions INT DEFAULT 0,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (bible_book_id) REFERENCES bible_books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample Bible books
INSERT INTO bible_books (name, category, difficulty, question_count, description) VALUES
('Genesis', 'Pentateuch', 'beginner', 25, 'The beginning of all things and God\'s covenant with Abraham'),
('Exodus', 'Pentateuch', 'intermediate', 30, 'The liberation of Israel from Egypt and the giving of the Law'),
('Psalms', 'Wisdom Literature', 'advanced', 50, 'Songs and prayers of praise, lament, and worship'),
('Proverbs', 'Wisdom Literature', 'beginner', 35, 'Wise sayings and practical wisdom for daily living'),
('Matthew', 'Gospels', 'beginner', 28, 'The life and teachings of Jesus Christ, the Messiah'),
('Acts', 'History', 'intermediate', 28, 'The birth and growth of the early Christian church'),
('Romans', 'Pauline Epistles', 'advanced', 16, 'The gospel of righteousness by faith in Christ'),
('Revelation', 'Prophecy', 'advanced', 22, 'The revelation of Jesus Christ and the end times');

-- Insert sample questions
INSERT INTO questions (bible_book_id, question, answer, difficulty) VALUES
(1, 'Who was the first man created by God?', 'Adam', 'beginner'),
(1, 'Who built the ark?', 'Noah', 'beginner'),
(1, 'Who was Abraham\'s wife?', 'Sarah', 'beginner'),
(2, 'Who led the Israelites out of Egypt?', 'Moses', 'intermediate'),
(2, 'What mountain did Moses receive the Ten Commandments on?', 'Mount Sinai', 'intermediate'),
(3, 'Who wrote most of the Psalms?', 'David', 'advanced'),
(3, 'What is the shortest Psalm?', 'Psalm 117', 'advanced');

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password) VALUES
('admin', 'admin@yourdomain.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Simple settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert basic settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Quiz Your Faith'),
('site_description', 'Master the Scriptures through interactive biblical gaming'),
('enable_videos', '1'),
('enable_multiplayer', '1'),
('enable_donations', '0'),
('enable_activation', '0');