<?php namespace App\Models;

class BibleBook {
    
    // Basic CRUD operations
    static function all($testament = null, $activeOnly = true) {
        $sql = "SELECT bb.*, bbc.display_name as category_name, bbc.color as category_color, bbc.icon as category_icon 
                FROM bible_books bb 
                LEFT JOIN bible_book_categories bbc ON bb.category = bbc.name 
                WHERE 1=1";
        
        $params = [];
        
        if ($testament) {
            $sql .= " AND bb.testament = ?";
            $params[] = $testament;
        }
        
        if ($activeOnly) {
            $sql .= " AND bb.is_active = TRUE AND bbc.is_active = TRUE";
        }
        
        $sql .= " ORDER BY bb.book_order ASC";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    static function find($id) {
        $st = db()->prepare("
            SELECT bb.*, bbc.display_name as category_name, bbc.color as category_color, bbc.icon as category_icon 
            FROM bible_books bb 
            LEFT JOIN bible_book_categories bbc ON bb.category = bbc.name 
            WHERE bb.id = ? LIMIT 1
        ");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    static function findByName($name) {
        $st = db()->prepare("SELECT * FROM bible_books WHERE name = ? LIMIT 1");
        $st->execute([$name]);
        return $st->fetch();
    }
    
    static function getCategories($activeOnly = true) {
        $sql = "SELECT * FROM bible_book_categories";
        if ($activeOnly) {
            $sql .= " WHERE is_active = TRUE";
        }
        $sql .= " ORDER BY sort_order ASC, display_name ASC";
        
        return db()->query($sql)->fetchAll();
    }
    
    static function findCategory($id) {
        $st = db()->prepare("SELECT * FROM bible_book_categories WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    static function findCategoryByName($name) {
        $st = db()->prepare("SELECT * FROM bible_book_categories WHERE name = ? LIMIT 1");
        $st->execute([$name]);
        return $st->fetch();
    }
    
    // Quiz-related methods
    static function getBooksWithQuizzes($testament = null, $difficulty = null) {
        $sql = "SELECT bb.*, bbc.display_name as category_name, bbc.color as category_color, bbc.icon as category_icon,
                       COUNT(q.id) as quiz_count
                FROM bible_books bb 
                LEFT JOIN bible_book_categories bbc ON bb.category = bbc.name 
                LEFT JOIN quizzes q ON q.book_id = bb.id AND q.is_active = TRUE
                WHERE bb.is_active = TRUE AND bb.quiz_count > 0";
        
        $params = [];
        
        if ($testament) {
            $sql .= " AND bb.testament = ?";
            $params[] = $testament;
        }
        
        if ($difficulty) {
            $sql .= " AND q.difficulty = ?";
            $params[] = $difficulty;
        }
        
        $sql .= " GROUP BY bb.id ORDER BY bb.book_order ASC";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    static function getBooksByCategory($categoryName, $activeOnly = true) {
        $sql = "SELECT bb.*, bbc.display_name as category_name, bbc.color as category_color, bbc.icon as category_icon 
                FROM bible_books bb 
                LEFT JOIN bible_book_categories bbc ON bb.category = bbc.name 
                WHERE bb.category = ?";
        
        $params = [$categoryName];
        
        if ($activeOnly) {
            $sql .= " AND bb.is_active = TRUE AND bbc.is_active = TRUE";
        }
        
        $sql .= " ORDER BY bb.book_order ASC";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    static function getTestamentSummary($testament) {
        $st = db()->prepare("
            SELECT 
                COUNT(*) as total_books,
                SUM(chapters) as total_chapters,
                AVG(chapters) as avg_chapters,
                COUNT(CASE WHEN quiz_count > 0 THEN 1 END) as books_with_quizzes,
                SUM(quiz_count) as total_quizzes
            FROM bible_books 
            WHERE testament = ? AND is_active = TRUE
        ");
        $st->execute([$testament]);
        return $st->fetch();
    }
    
    // User progress tracking
    static function getUserProgress($userId, $bookId = null) {
        if ($bookId) {
            $st = db()->prepare("
                SELECT bqs.*, bb.name as book_name, bb.short_name, bb.testament, bb.category
                FROM bible_quiz_statistics bqs
                JOIN bible_books bb ON bqs.book_id = bb.id
                WHERE bqs.user_id = ? AND bqs.book_id = ?
                ORDER BY bqs.last_attempt_at DESC
                LIMIT 1
            ");
            $st->execute([$userId, $bookId]);
            return $st->fetch();
        } else {
            $st = db()->prepare("
                SELECT 
                    COUNT(DISTINCT bqs.book_id) as books_completed,
                    COUNT(CASE WHEN bqs.passed = TRUE THEN 1 END) as books_passed,
                    AVG(bqs.score) as avg_score,
                    SUM(bqs.attempts) as total_attempts,
                    MAX(bqs.best_score) as best_score,
                    MAX(bqs.completion_time) as longest_quiz,
                    MIN(bqs.completion_time) as shortest_quiz
                FROM bible_quiz_statistics bqs
                WHERE bqs.user_id = ?
            ");
            $st->execute([$userId]);
            return $st->fetch();
        }
    }
    
    static function getUserMasteryLevel($userId) {
        $progress = self::getUserProgress($userId);
        $booksCompleted = $progress->books_completed ?? 0;
        
        if ($booksCompleted >= 50) return 'master';
        if ($booksCompleted >= 35) return 'expert';
        if ($booksCompleted >= 20) return 'advanced';
        if ($booksCompleted >= 10) return 'intermediate';
        return 'beginner';
    }
    
    static function updateUserMastery($userId) {
        $masteryLevel = self::getUserMasteryLevel($userId);
        $booksCompleted = self::getUserProgress($userId)->books_completed ?? 0;
        
        $st = db()->prepare("
            INSERT INTO user_bible_mastery (user_id, total_books_completed, mastery_level, last_quiz_at) 
            VALUES (?, ?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE 
            total_books_completed = ?, mastery_level = ?, last_quiz_at = NOW()
        ");
        return $st->execute([$userId, $booksCompleted, $masteryLevel, $booksCompleted, $masteryLevel]);
    }
    
    // Quiz template management
    static function getQuizTemplates($bookId, $difficulty = null) {
        $sql = "SELECT * FROM bible_book_quiz_templates WHERE book_id = ? AND is_active = TRUE";
        $params = [$bookId];
        
        if ($difficulty) {
            $sql .= " AND difficulty = ?";
            $params[] = $difficulty;
        }
        
        $sql .= " ORDER BY difficulty ASC, question_count ASC";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    static function createQuizTemplate($data) {
        $st = db()->prepare("
            INSERT INTO bible_book_quiz_templates (book_id, quiz_title, description, difficulty, question_count, time_limit, passing_score, badge_icon, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $st->execute([
            $data['book_id'], $data['quiz_title'], $data['description'], 
            $data['difficulty'], $data['question_count'], $data['time_limit'], 
            $data['passing_score'], $data['badge_icon'], $data['is_active']
        ]);
        return db()->lastInsertId();
    }
    
    // Statistics and analytics
    static function getBookStatistics($bookId) {
        $st = db()->prepare("
            SELECT 
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(*) as total_attempts,
                AVG(score) as avg_score,
                MAX(score) as highest_score,
                MIN(score) as lowest_score,
                COUNT(CASE WHEN passed = TRUE THEN 1 END) as passed_count,
                AVG(completion_time) as avg_completion_time,
                MAX(completion_time) as longest_time,
                MIN(completion_time) as shortest_time
            FROM bible_quiz_statistics 
            WHERE book_id = ?
        ");
        $st->execute([$bookId]);
        return $st->fetch();
    }
    
    static function getGlobalStatistics() {
        return db()->query("
            SELECT 
                COUNT(DISTINCT user_id) as total_users,
                COUNT(*) as total_attempts,
                AVG(score) as global_avg_score,
                COUNT(CASE WHEN passed = TRUE THEN 1 END) as total_passed,
                AVG(completion_time) as global_avg_time,
                SUM(attempts) as total_quiz_attempts
            FROM bible_quiz_statistics
        ")->fetch();
    }
    
    // Achievement system
    static function checkAchievements($userId) {
        $userMastery = db()->prepare("SELECT * FROM user_bible_mastery WHERE user_id = ?")->execute([$userId])->fetch();
        if (!$userMastery) return [];
        
        $achievements = [];
        
        // Check testament achievements
        if ($userMastery->ot_books_completed >= 39) {
            $achievements[] = 'Old Testament Scholar';
        }
        if ($userMastery->nt_books_completed >= 27) {
            $achievements[] = 'New Testament Expert';
        }
        
        // Check category achievements
        $categoryStats = db()->prepare("
            SELECT bb.category, COUNT(DISTINCT bqs.book_id) as completed_in_category
            FROM bible_quiz_statistics bqs
            JOIN bible_books bb ON bqs.book_id = bb.id
            WHERE bqs.user_id = ? AND bqs.passed = TRUE
            GROUP BY bb.category
        ")->execute([$userId])->fetchAll();
        
        foreach ($categoryStats as $stat) {
            if ($stat->category === 'pentateuch' && $stat->completed_in_category >= 5) {
                $achievements[] = 'Pentateuch Master';
            }
            if ($stat->category === 'gospels' && $stat->completed_in_category >= 4) {
                $achievements[] = 'Gospel Expert';
            }
            if ($stat->category === 'minor_prophets' && $stat->completed_in_category >= 12) {
                $achievements[] = 'Minor Prophet';
            }
        }
        
        // Check total books achievements
        if ($userMastery->total_books_completed >= 50) {
            $achievements[] = 'Book Legend';
        } elseif ($userMastery->total_books_completed >= 25) {
            $achievements[] = 'Book Champion';
        } elseif ($userMastery->total_books_completed >= 10) {
            $achievements[] = 'Book Master';
        }
        
        return $achievements;
    }
    
    // Search and filtering
    static function searchBooks($query, $testament = null, $category = null) {
        $sql = "SELECT bb.*, bbc.display_name as category_name, bbc.color as category_color, bbc.icon as category_icon 
                FROM bible_books bb 
                LEFT JOIN bible_book_categories bbc ON bb.category = bbc.name 
                WHERE (bb.name LIKE ? OR bb.short_name LIKE ? OR bb.description LIKE ?) AND bb.is_active = TRUE";
        
        $params = ["%{$query}%", "%{$query}%", "%{$query}%"];
        
        if ($testament) {
            $sql .= " AND bb.testament = ?";
            $params[] = $testament;
        }
        
        if ($category) {
            $sql .= " AND bb.category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY bb.book_order ASC LIMIT 20";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
}