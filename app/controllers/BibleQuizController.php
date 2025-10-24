<?php namespace App\Controllers;

use App\Models\{Quiz, Question, Answer, BibleBook, User};

class BibleQuizController {
    
    // Main Bible quiz hub
    function index() {
        if (!\App\Models\Feature::isEnabled('quiz_system')) {
            $_SESSION['error'] = 'Quiz system is currently disabled';
            redirect('/');
            return;
        }
        
        $testaments = [
            'old' => BibleBook::getTestamentSummary('old'),
            'new' => BibleBook::getTestamentSummary('new')
        ];
        
        $categories = BibleBook::getCategories();
        $featuredQuizzes = Quiz::all(['active_only' => true, 'limit' => 6]);
        $globalStats = BibleBook::getGlobalStatistics();
        
        view('bible-quiz/index', [
            'testaments' => $testaments,
            'categories' => $categories,
            'featuredQuizzes' => $featuredQuizzes,
            'globalStats' => $globalStats,
            'userProgress' => isset($_SESSION['user_id']) ? BibleBook::getUserProgress($_SESSION['user_id']) : null
        ]);
    }
    
    // Browse by testament
    function testament($testament) {
        if (!in_array($testament, ['old', 'new'])) {
            $_SESSION['error'] = 'Invalid testament';
            redirect('/bible-quiz');
            return;
        }
        
        if (!\App\Models\Feature::isEnabled('quiz_system')) {
            $_SESSION['error'] = 'Quiz system is currently disabled';
            redirect('/');
            return;
        }
        
        $books = BibleBook::all($testament);
        $categories = BibleBook::getBooksByTestament($testament);
        $testamentSummary = BibleBook::getTestamentSummary($testament);
        $userProgress = isset($_SESSION['user_id']) ? BibleBook::getUserProgress($_SESSION['user_id'], ['testament' => $testament]) : null;
        
        view('bible-quiz/testament', [
            'testament' => $testament,
            'books' => $books,
            'categories' => $categories,
            'testamentSummary' => $testamentSummary,
            'userProgress' => $userProgress
        ]);
    }
    
    // Browse by category
    function category($categoryName) {
        $category = BibleBook::findCategoryByName($categoryName);
        if (!$category) {
            $_SESSION['error'] = 'Category not found';
            redirect('/bible-quiz');
            return;
        }
        
        if (!\App\Models\Feature::isEnabled('quiz_system')) {
            $_SESSION['error'] = 'Quiz system is currently disabled';
            redirect('/');
            return;
        }
        
        $books = BibleBook::getBooksByCategory($categoryName);
        $quizzes = Quiz::getByCategory($categoryName);
        $categoryStats = $this->getCategoryStats($categoryName);
        
        view('bible-quiz/category', [
            'category' => $category,
            'books' => $books,
            'quizzes' => $quizzes,
            'categoryStats' => $categoryStats
        ]);
    }
    
    // Browse by book
    function book($bookId) {
        $book = BibleBook::find($bookId);
        if (!$book) {
            $_SESSION['error'] = 'Book not found';
            redirect('/bible-quiz');
            return;
        }
        
        if (!\App\Models\Feature::isEnabled('quiz_system')) {
            $_SESSION['error'] = 'Quiz system is currently disabled';
            redirect('/');
            return;
        }
        
        $quizzes = Quiz::getByBook($bookId);
        $bookStats = Quiz::getBookStats($bookId);
        $userProgress = isset($_SESSION['user_id']) ? BibleBook::getUserBookProgress($_SESSION['user_id'], $bookId) : [];
        $quizTemplates = BibleBook::getQuizTemplates($bookId);
        
        view('bible-quiz/book', [
            'book' => $book,
            'quizzes' => $quizzes,
            'bookStats' => $bookStats,
            'userProgress' => $userProgress,
            'quizTemplates' => $quizTemplates
        ]);
    }
    
    // Quiz player interface
    function play($quizId) {
        if (!\App\Models\Feature::isEnabled('quiz_system')) {
            $_SESSION['error'] = 'Quiz system is currently disabled';
            redirect('/');
            return;
        }
        
        $quiz = Quiz::find($quizId);
        if (!$quiz || !$quiz->is_active) {
            $_SESSION['error'] = 'Quiz not found or not available';
            redirect('/bible-quiz');
            return;
        }
        
        $questions = Question::byQuiz($quizId);
        if (empty($questions)) {
            $_SESSION['error'] = 'No questions found for this quiz';
            redirect('/bible-quiz/book/' . $quiz->book_id);
            return;
        }
        
        // Initialize quiz session with enhanced tracking
        $_SESSION['quiz_' . $quizId] = [
            'start_time' => time(),
            'current_question' => 0,
            'total_questions' => count($questions),
            'timer' => $quiz->time_limit ?? 900, // 15 minutes default
            'quiz_id' => $quizId,
            'book_id' => $quiz->book_id,
            'difficulty' => $quiz->difficulty,
            'question_times' => [], // Track time per question
            'answers' => []
        ];
        
        view('bible-quiz/play', [
            'quiz' => $quiz,
            'questions' => $questions,
            'current_question' => 0,
            'timer' => $quiz->time_limit ?? 900,
            'book' => BibleBook::find($quiz->book_id)
        ]);
    }
    
    // Submit quiz answers
    function submit($quizId) {
        if (!isset($_SESSION['quiz_' . $quizId])) {
            $_SESSION['error'] = 'Quiz session not found';
            redirect('/bible-quiz');
            return;
        }
        
        $quizSession = $_SESSION['quiz_' . $quizId];
        $quiz = Quiz::find($quizId);
        
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz not found';
            redirect('/bible-quiz');
            return;
        }
        
        // Calculate detailed score
        $correctAnswers = 0;
        $totalQuestions = $quizSession['total_questions'];
        $questionDetails = [];
        
        if (isset($_POST['answers']) && is_array($_POST['answers'])) {
            foreach ($_POST['answers'] as $questionId => $chosenAnswer) {
                $question = Question::find($questionId);
                $isCorrect = ($chosenAnswer == $question->correct);
                
                if ($isCorrect) {
                    $correctAnswers++;
                }
                
                $questionDetails[] = [
                    'question_id' => $questionId,
                    'chosen' => $chosenAnswer,
                    'correct' => $question->correct,
                    'is_correct' => $isCorrect,
                    'explanation' => $question->explanation
                ];
            }
        }
        
        $score = ($totalQuestions > 0) ? round(($correctAnswers / $totalQuestions) * 100) : 0;
        $passed = ($score >= $quiz->passing_score);
        $completionTime = time() - $quizSession['start_time'];
        
        // Save detailed results
        if (isset($_SESSION['user_id'])) {
            $this->saveQuizResults($_SESSION['user_id'], $quizId, $questionDetails, $score, $completionTime, $passed);
            
            // Check for achievements
            $achievements = Quiz::checkAchievements($_SESSION['user_id'], $quizId, $score, $passed);
            
            // Update user mastery
            BibleBook::updateUserMastery($_SESSION['user_id']);
        }
        
        // Clear quiz session
        unset($_SESSION['quiz_' . $quizId]);
        
        view('bible-quiz/result', [
            'quiz' => $quiz,
            'score' => $score,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'passed' => $passed,
            'completion_time' => $completionTime,
            'question_details' => $questionDetails,
            'achievements' => $achievements ?? [],
            'book' => BibleBook::find($quiz->book_id)
        ]);
    }
    
    // Quiz results and review
    function result($quizId) {
        // This is handled by submit() function, but provide a direct access route
        if (isset($_SESSION['quiz_' . $quizId])) {
            // Quiz is still in progress, redirect to play
            redirect('/bible-quiz/play/' . $quizId);
            return;
        }
        
        $_SESSION['error'] = 'Quiz results not available';
        redirect('/bible-quiz');
    }
    
    // Leaderboard
    function leaderboard($bookId = null) {
        if (!\App\Models\Feature::isEnabled('quiz_system')) {
            $_SESSION['error'] = 'Quiz system is currently disabled';
            redirect('/');
            return;
        }
        
        $leaderboard = Quiz::getLeaderboard($bookId, 100);
        $book = $bookId ? BibleBook::find($bookId) : null;
        
        view('bible-quiz/leaderboard', [
            'leaderboard' => $leaderboard,
            'book' => $book,
            'globalStats' => BibleBook::getGlobalStatistics()
        ]);
    }
    
    // User progress dashboard
    function myProgress() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please login to view your progress';
            redirect('/login');
            return;
        }
        
        if (!\App\Models\Feature::isEnabled('quiz_system')) {
            $_SESSION['error'] = 'Quiz system is currently disabled';
            redirect('/');
            return;
        }
        
        $userProgress = BibleBook::getUserProgress($_SESSION['user_id']);
        $testamentProgress = [
            'old' => BibleBook::getUserProgress($_SESSION['user_id'], ['testament' => 'old']),
            'new' => BibleBook::getUserProgress($_SESSION['user_id'], ['testament' => 'new'])
        ];
        
        $achievements = BibleBook::checkAchievements($_SESSION['user_id']);
        $masteryLevel = BibleBook::getUserMasteryLevel($_SESSION['user_id']);
        
        view('bible-quiz/my-progress', [
            'userProgress' => $userProgress,
            'testamentProgress' => $testamentProgress,
            'achievements' => $achievements,
            'masteryLevel' => $masteryLevel,
            'globalStats' => BibleBook::getGlobalStatistics()
        ]);
    }
    
    // Achievement system
    function achievements() {
        if (!\App\Models\Feature::isEnabled('quiz_system')) {
            $_SESSION['error'] = 'Quiz system is currently disabled';
            redirect('/');
            return;
        }
        
        $achievements = \App\Models\BibleBook::getMasteryAchievements();
        $userAchievements = isset($_SESSION['user_id']) ? BibleBook::checkAchievements($_SESSION['user_id']) : [];
        
        view('bible-quiz/achievements', [
            'achievements' => $achievements,
            'userAchievements' => $userAchievements
        ]);
    }
    
    // Search functionality
    function search() {
        $query = $_GET['q'] ?? '';
        $testament = $_GET['testament'] ?? null;
        $category = $_GET['category'] ?? null;
        
        if (empty($query) && !$testament && !$category) {
            redirect('/bible-quiz');
            return;
        }
        
        if (!\App\Models\Feature::isEnabled('quiz_system')) {
            $_SESSION['error'] = 'Quiz system is currently disabled';
            redirect('/');
            return;
        }
        
        $books = BibleBook::searchBooks($query, $testament, $category);
        $quizzes = Quiz::searchQuizzes($query, $testament, $category);
        
        view('bible-quiz/search', [
            'query' => $query,
            'testament' => $testament,
            'category' => $category,
            'books' => $books,
            'quizzes' => $quizzes
        ]);
    }
    
    // Private helper methods
    private function saveQuizResults($userId, $quizId, $questionDetails, $score, $completionTime, $passed) {
        $quiz = Quiz::find($quizId);
        if (!$quiz) return false;
        
        // Get current stats or create new
        $existing = db()->prepare("
            SELECT * FROM bible_quiz_statistics 
            WHERE user_id = ? AND quiz_id = ? 
            ORDER BY last_attempt_at DESC LIMIT 1
        ")->execute([$userId, $quizId])->fetch();
        
        $attempts = $existing ? $existing->attempts + 1 : 1;
        $bestScore = $existing ? max($existing->best_score, $score) : $score;
        
        // Save to statistics
        $st = db()->prepare("
            INSERT INTO bible_quiz_statistics (user_id, book_id, quiz_id, score, correct_answers, total_questions, completion_time, attempts, best_score, passed, first_attempt_at, last_attempt_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, COALESCE(?, NOW()), NOW()) 
            ON DUPLICATE KEY UPDATE 
            score = VALUES(score), correct_answers = VALUES(correct_answers), completion_time = VALUES(completion_time), 
            attempts = VALUES(attempts), best_score = VALUES(best_score), passed = VALUES(passed), last_attempt_at = NOW()
        ");
        
        $st->execute([
            $userId, $quiz->book_id, $quizId, $score, 
            count(array_filter($questionDetails, function($q) { return $q['is_correct']; })), 
            count($questionDetails), $completionTime, $attempts, $bestScore, $passed,
            $existing ? $existing->first_attempt_at : null
        ]);
        
        // Save individual answers
        foreach ($questionDetails as $detail) {
            Answer::saveBatch($userId, $quizId, [$detail['question_id'] => $detail['chosen']]);
        }
        
        return true;
    }
    
    private function getCategoryStats($categoryName) {
        return [
            'total_books' => db()->prepare("SELECT COUNT(*) FROM bible_books WHERE category = ?")->execute([$categoryName])->fetchColumn(),
            'books_with_quizzes' => db()->prepare("SELECT COUNT(DISTINCT bb.id) FROM bible_books bb JOIN quizzes q ON bb.id = q.book_id WHERE bb.category = ? AND q.is_active = TRUE")->execute([$categoryName])->fetchColumn(),
            'total_quizzes' => db()->prepare("SELECT COUNT(*) FROM quizzes q JOIN bible_books bb ON q.book_id = bb.id WHERE bb.category = ? AND q.is_active = TRUE")->execute([$categoryName])->fetchColumn(),
            'avg_difficulty' => db()->prepare("SELECT AVG(CASE q.difficulty WHEN 'beginner' THEN 1 WHEN 'intermediate' THEN 2 WHEN 'advanced' THEN 3 WHEN 'expert' THEN 4 END) FROM quizzes q JOIN bible_books bb ON q.book_id = bb.id WHERE bb.category = ? AND q.is_active = TRUE")->execute([$categoryName])->fetchColumn()
        ];
    }
}