<?php namespace App\Models;

class Quiz {
    
    // Basic CRUD operations
    static function all($filters = []) {
        $sql = "SELECT q.*, bb.name as book_name, bb.short_name, bb.testament, bb.category as book_category,
                       c.name as category_name, c.slug as category_slug
                FROM quizzes q
                LEFT JOIN bible_books bb ON q.book_id = bb.id
                LEFT JOIN categories c ON q.category_id = c.id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['book_id'])) {
            $sql .= " AND q.book_id = ?";
            $params[] = $filters['book_id'];
        }
        
        if (isset($filters['testament'])) {
            $sql .= " AND bb.testament = ?";
            $params[] = $filters['testament'];
        }
        
        if (isset($filters['category'])) {
            $sql .= " AND bb.category = ?";
            $params[] = $filters['category'];
        }
        
        if (isset($filters['difficulty'])) {
            $sql .= " AND q.difficulty = ?";
            $params[] = $filters['difficulty'];
        }
        
        if (isset($filters['active_only']) && $filters['active_only']) {
            $sql .= " AND q.is_active = TRUE";
        }
        
        $sql .= " ORDER BY bb.book_order ASC, q.difficulty ASC, q.title ASC";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    static function find($id) {
        $st = db()->prepare("
            SELECT q.*, bb.name as book_name, bb.short_name, bb.testament, bb.category as book_category,
                   bb.chapters, bb.description as book_description, bb.key_verses, bb.theme,
                   c.name as category_name, c.slug as category_slug
            FROM quizzes q
            LEFT JOIN bible_books bb ON q.book_id = bb.id
            LEFT JOIN categories c ON q.category_id = c.id
            WHERE q.id = ? LIMIT 1
        ");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    static function create($data) {
        $sql = "INSERT INTO quizzes (book_id, category_id, title, description, difficulty, duration, time_limit, passing_score, badge_id, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE, NOW())";
        
        $st = db()->prepare($sql);
        $st->execute([
            $data['book_id'], $data['category_id'], $data['title'],
            $data['description'], $data['difficulty'], $data['duration'],
            $data['time_limit'], $data['passing_score'], $data['badge_id']
        ]);
        
        $quizId = db()->lastInsertId();
        
        // Update book quiz count
        if ($data['book_id']) {
            self::updateBookQuizCount($data['book_id']);
        }
        
        return $quizId;
    }
    
    static function update($id, $data) {
        $allowedFields = ['book_id', 'category_id', 'title', 'description', 'difficulty', 'duration', 'time_limit', 'passing_score', 'badge_id', 'is_active'];
        $setParts = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $setParts[] = "{$field} = ?";
                $values[] = $value;
            }
        }
        
        if (empty($setParts)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE quizzes SET " . implode(', ', $setParts) . " WHERE id = ?";
        $st = db()->prepare($sql);
        
        $success = $st->execute($values);
        
        if ($success && isset($data['book_id'])) {
            self::updateBookQuizCount($data['book_id']);
        }
        
        return $success;
    }
    
    static function delete($id) {
        $quiz = self::find($id);
        if (!$quiz) return false;
        
        $st = db()->prepare("DELETE FROM quizzes WHERE id = ?");
        $success = $st->execute([$id]);
        
        if ($success && $quiz->book_id) {
            self::updateBookQuizCount($quiz->book_id);
        }
        
        return $success;
    }
    
    // Bible book integration
    static function getByBook($bookId, $difficulty = null, $activeOnly = true) {
        $sql = "SELECT q.*, bb.name as book_name, bb.short_name, bb.testament
                FROM quizzes q
                JOIN bible_books bb ON q.book_id = bb.id
                WHERE q.book_id = ?";
        
        $params = [$bookId];
        
        if ($difficulty) {
            $sql .= " AND q.difficulty = ?";
            $params[] = $difficulty;
        }
        
        if ($activeOnly) {
            $sql .= " AND q.is_active = TRUE";
        }
        
        $sql .= " ORDER BY q.difficulty ASC, q.title ASC";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    static function getByTestament($testament, $filters = []) {
        $sql = "SELECT q.*, bb.name as book_name, bb.short_name, bb.category as book_category
                FROM quizzes q
                JOIN bible_books bb ON q.book_id = bb.id
                WHERE bb.testament = ? AND q.is_active = TRUE";
        
        $params = [$testament];
        
        if (isset($filters['difficulty'])) {
            $sql .= " AND q.difficulty = ?";
            $params[] = $filters['difficulty'];
        }
        
        if (isset($filters['category'])) {
            $sql .= " AND bb.category = ?";
            $params[] = $filters['category'];
        }
        
        $sql .= " ORDER BY bb.book_order ASC, q.difficulty ASC";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    static function getByCategory($categoryName, $filters = []) {
        $sql = "SELECT q.*, bb.name as book_name, bb.short_name, bb.testament
                FROM quizzes q
                JOIN bible_books bb ON q.book_id = bb.id
                WHERE bb.category = ? AND q.is_active = TRUE";
        
        $params = [$categoryName];
        
        if (isset($filters['difficulty'])) {
            $sql .= " AND q.difficulty = ?";
            $params[] = $filters['difficulty'];
        }
        
        $sql .= " ORDER BY bb.book_order ASC, q.difficulty ASC";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    // Statistics and analytics
    static function getQuizStats($quizId) {
        $st = db()->prepare("
            SELECT
                COUNT(DISTINCT user_id) as unique_players,
                COUNT(*) as total_attempts,
                AVG(score) as avg_score,
                MAX(score) as highest_score,
                MIN(score) as lowest_score,
                COUNT(CASE WHEN passed = TRUE THEN 1 END) as passed_count,
                AVG(completion_time) as avg_completion_time
            FROM bible_quiz_statistics
            WHERE quiz_id = ?
        ");
        $st->execute([$quizId]);
        return $st->fetch();
    }
    
    static function getBookStats($bookId) {
        $st = db()->prepare("
            SELECT
                COUNT(DISTINCT q.id) as total_quizzes,
                COUNT(DISTINCT bqs.user_id) as unique_players,
                COUNT(bqs.id) as total_attempts,
                AVG(bqs.score) as avg_score,
                COUNT(CASE WHEN bqs.passed = TRUE THEN 1 END) as passed_count
            FROM bible_quiz_statistics bqs
            JOIN quizzes q ON bqs.quiz_id = q.id
            WHERE q.book_id = ?
        ");
        $st->execute([$bookId]);
        return $st->fetch();
    }
    
    // User progress tracking
    static function getUserProgress($userId, $filters = []) {
        $sql = "SELECT q.*, bb.name as book_name, bb.short_name, bb.testament, bb.category as book_category,
                       bqs.score, bqs.correct_answers, bqs.total_questions, bqs.completion_time,
                       bqs.attempts, bqs.best_score, bqs.passed, bqs.last_attempt_at
                FROM bible_quiz_statistics bqs
                JOIN quizzes q ON bqs.quiz_id = q.id
                JOIN bible_books bb ON q.book_id = bb.id
                WHERE bqs.user_id = ?";
        
        $params = [$userId];
        
        if (isset($filters['testament'])) {
            $sql .= " AND bb.testament = ?";
            $params[] = $filters['testament'];
        }
        
        if (isset($filters['category'])) {
            $sql .= " AND bb.category = ?";
            $params[] = $filters['category'];
        }
        
        if (isset($filters['passed_only']) && $filters['passed_only']) {
            $sql .= " AND bqs.passed = TRUE";
        }
        
        $sql .= " ORDER BY bqs.last_attempt_at DESC";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    static function getUserBookProgress($userId, $bookId) {
        $st = db()->prepare("
            SELECT q.*, bqs.score, bqs.correct_answers, bqs.total_questions,
                   bqs.completion_time, bqs.attempts, bqs.best_score, bqs.passed,
                   bqs.last_attempt_at
            FROM bible_quiz_statistics bqs
            JOIN quizzes q ON bqs.quiz_id = q.id
            WHERE bqs.user_id = ? AND q.book_id = ?
            ORDER BY bqs.last_attempt_at DESC
        ");
        $st->execute([$userId, $bookId]);
        return $st->fetchAll();
    }
    
    // Leaderboard and rankings
    static function getLeaderboard($bookId = null, $limit = 50) {
        $sql = "SELECT u.id, u.name, u.avatar,
                       SUM(bqs.score) as total_score,
                       COUNT(bqs.id) as quizzes_completed,
                       AVG(bqs.score) as avg_score,
                       COUNT(CASE WHEN bqs.passed = TRUE THEN 1 END) as passed_count";
        
        if ($bookId) {
            $sql .= ", MAX(bqs.score) as best_book_score";
        }
        
        $sql .= " FROM bible_quiz_statistics bqs
                  JOIN users u ON bqs.user_id = u.id";
        
        if ($bookId) {
            $sql .= " JOIN quizzes q ON bqs.quiz_id = q.id WHERE q.book_id = ?";
        }
        
        $sql .= " GROUP BY u.id, u.name, u.avatar
                  ORDER BY total_score DESC, avg_score DESC
                  LIMIT ?";
        
        $params = $bookId ? [$bookId, $limit] : [$limit];
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    // Achievement tracking
    static function checkAchievements($userId, $quizId, $score, $passed) {
        $achievements = [];
        
        // Get quiz and book info
        $quiz = self::find($quizId);
        if (!$quiz || !$quiz->book_id) return $achievements;
        
        $book = \App\Models\BibleBook::find($quiz->book_id);
        if (!$book) return $achievements;
        
        // Check if this is a perfect score
        if ($score >= 95) {
            $achievements[] = 'Perfect Score on ' . $book->name;
        }
        
        // Check if passed
        if ($passed && $score >= $quiz->passing_score) {
            $achievements[] = 'Passed ' . $book->name . ' Quiz';
        }
        
        // Check streak achievements
        $recentQuizzes = db()->prepare("
            SELECT score FROM bible_quiz_statistics
            WHERE user_id = ? AND book_id = ?
            ORDER BY last_attempt_at DESC
            LIMIT 5
        ")->execute([$userId, $book->id])->fetchAll();
        
        if (count($recentQuizzes) >= 5) {
            $allHighScores = true;
            foreach ($recentQuizzes as $quiz) {
                if ($quiz->score < 80) {
                    $allHighScores = false;
                    break;
                }
            }
            if ($allHighScores) {
                $achievements[] = 'Streak: 5 High Scores in ' . $book->name;
            }
        }
        
        return $achievements;
    }
    
    // Helper methods
    static function updateBookQuizCount($bookId) {
        $st = db()->prepare("UPDATE bible_books SET quiz_count = (SELECT COUNT(*) FROM quizzes WHERE book_id = ? AND is_active = TRUE) WHERE id = ?");
        return $st->execute([$bookId, $bookId]);
    }
    
    static function getQuizDifficulties() {
        return ['beginner', 'intermediate', 'advanced', 'expert'];
    }
    
    static function getDifficultyColor($difficulty) {
        $colors = [
            'beginner' => '#28a745',
            'intermediate' => '#ffc107',
            'advanced' => '#fd7e14',
            'expert' => '#dc3545'
        ];
        return $colors[$difficulty] ?? '#6c757d';
    }
    
    static function getDifficultyLabel($difficulty) {
        $labels = [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
            'expert' => 'Expert'
        ];
        return $labels[$difficulty] ?? ucfirst($difficulty);
    }
}
