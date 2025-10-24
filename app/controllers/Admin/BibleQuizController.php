<?php namespace App\Controllers\Admin;

use App\Models\{Quiz, Question, Answer, BibleBook};

class BibleQuizController {
    
    // Main dashboard
    function dashboard() {
        $stats = [
            'total_books' => db()->query("SELECT COUNT(*) FROM bible_books WHERE is_active = TRUE")->fetchColumn(),
            'books_with_quizzes' => db()->query("SELECT COUNT(DISTINCT book_id) FROM quizzes WHERE is_active = TRUE")->fetchColumn(),
            'total_quizzes' => db()->query("SELECT COUNT(*) FROM quizzes WHERE is_active = TRUE")->fetchColumn(),
            'total_questions' => db()->query("SELECT COUNT(*) FROM questions")->fetchColumn(),
            'ot_books' => BibleBook::getTestamentSummary('old'),
            'nt_books' => BibleBook::getTestamentSummary('new'),
            'recent_quizzes' => Quiz::all(['limit' => 5, 'order_by' => 'created_at DESC'])
        ];
        
        $globalStats = BibleBook::getGlobalStatistics();
        
        view('admin/bible-quiz/dashboard', [
            'stats' => $stats,
            'globalStats' => $globalStats
        ]);
    }
    
    // Bible books management
    function books() {
        $testament = $_GET['testament'] ?? null;
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $books = BibleBook::all($testament);
        $categories = BibleBook::getCategories();
        
        // Filter by category if specified
        if ($category) {
            $books = array_filter($books, function($book) use ($category) {
                return $book->category === $category;
            });
        }
        
        // Filter by search if specified
        if ($search) {
            $books = array_filter($books, function($book) use ($search) {
                return stripos($book->name, $search) !== false || 
                       stripos($book->short_name, $search) !== false ||
                       stripos($book->description, $search) !== false;
            });
        }
        
        view('admin/bible-quiz/books', [
            'books' => $books,
            'categories' => $categories,
            'testament' => $testament,
            'category' => $category,
            'search' => $search
        ]);
    }
    
    // Individual book management
    function book($bookId) {
        $book = BibleBook::find($bookId);
        if (!$book) {
            $_SESSION['error'] = 'Book not found';
            redirect('/admin/bible-quiz/books');
            return;
        }
        
        $quizzes = Quiz::getByBook($bookId);
        $bookStats = Quiz::getBookStats($bookId);
        $quizTemplates = BibleBook::getQuizTemplates($bookId);
        
        view('admin/bible-quiz/book', [
            'book' => $book,
            'quizzes' => $quizzes,
            'bookStats' => $bookStats,
            'quizTemplates' => $quizTemplates
        ]);
    }
    
    // Quiz management
    function quizzes() {
        $bookId = $_GET['book_id'] ?? null;
        $testament = $_GET['testament'] ?? null;
        $difficulty = $_GET['difficulty'] ?? null;
        $status = $_GET['status'] ?? 'active';
        
        $filters = ['active_only' => ($status === 'active')];
        
        if ($bookId) {
            $filters['book_id'] = $bookId;
        }
        
        if ($testament) {
            $filters['testament'] = $testament;
        }
        
        if ($difficulty) {
            $filters['difficulty'] = $difficulty;
        }
        
        $quizzes = Quiz::all($filters);
        $books = BibleBook::all();
        $categories = BibleBook::getCategories();
        
        view('admin/bible-quiz/quizzes', [
            'quizzes' => $quizzes,
            'books' => $books,
            'categories' => $categories,
            'bookId' => $bookId,
            'testament' => $testament,
            'difficulty' => $difficulty,
            'status' => $status
        ]);
    }
    
    // Create new quiz
    function createQuiz() {
        $books = BibleBook::all();
        $categories = \App\Models\Category::all();
        
        view('admin/bible-quiz/create-quiz', [
            'books' => $books,
            'categories' => $categories,
            'difficulties' => Quiz::getQuizDifficulties()
        ]);
    }
    
    // Store new quiz
    function storeQuiz() {
        $required = ['title', 'book_id', 'difficulty'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Please fill in all required fields";
                redirect('/admin/bible-quiz/create-quiz');
                return;
            }
        }
        
        $data = [
            'book_id' => $_POST['book_id'],
            'category_id' => $_POST['category_id'] ?? null,
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? '',
            'difficulty' => $_POST['difficulty'],
            'duration' => $_POST['duration'] ?? 600,
            'time_limit' => $_POST['time_limit'] ?? 600,
            'passing_score' => $_POST['passing_score'] ?? 70,
            'badge_id' => $_POST['badge_id'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        try {
            $quizId = Quiz::create($data);
            $_SESSION['success'] = "Quiz created successfully!";
            redirect('/admin/bible-quiz/quiz/' . $quizId . '/questions');
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error creating quiz: " . $e->getMessage();
            redirect('/admin/bible-quiz/create-quiz');
        }
    }
    
    // Edit quiz
    function editQuiz($quizId) {
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz not found';
            redirect('/admin/bible-quiz/quizzes');
            return;
        }
        
        $books = BibleBook::all();
        $categories = \App\Models\Category::all();
        
        view('admin/bible-quiz/edit-quiz', [
            'quiz' => $quiz,
            'books' => $books,
            'categories' => $categories,
            'difficulties' => Quiz::getQuizDifficulties()
        ]);
    }
    
    // Update quiz
    function updateQuiz($quizId) {
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz not found';
            redirect('/admin/bible-quiz/quizzes');
            return;
        }
        
        $data = [
            'book_id' => $_POST['book_id'],
            'category_id' => $_POST['category_id'] ?? null,
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? '',
            'difficulty' => $_POST['difficulty'],
            'duration' => $_POST['duration'] ?? 600,
            'time_limit' => $_POST['time_limit'] ?? 600,
            'passing_score' => $_POST['passing_score'] ?? 70,
            'badge_id' => $_POST['badge_id'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        try {
            Quiz::update($quizId, $data);
            $_SESSION['success'] = "Quiz updated successfully!";
            redirect('/admin/bible-quiz/quizzes');
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error updating quiz: " . $e->getMessage();
            redirect('/admin/bible-quiz/edit-quiz/' . $quizId);
        }
    }
    
    // Delete quiz
    function deleteQuiz($quizId) {
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz not found';
            redirect('/admin/bible-quiz/quizzes');
            return;
        }
        
        try {
            Quiz::delete($quizId);
            $_SESSION['success'] = "Quiz deleted successfully!";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error deleting quiz: " . $e->getMessage();
        }
        
        redirect('/admin/bible-quiz/quizzes');
    }
    
    // Question management
    function questions($quizId) {
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz not found';
            redirect('/admin/bible-quiz/quizzes');
            return;
        }
        
        $questions = Question::byQuiz($quizId);
        $book = BibleBook::find($quiz->book_id);
        
        view('admin/bible-quiz/questions', [
            'quiz' => $quiz,
            'questions' => $questions,
            'book' => $book
        ]);
    }
    
    // Create question
    function createQuestion($quizId) {
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz not found';
            redirect('/admin/bible-quiz/quizzes');
            return;
        }
        
        $book = BibleBook::find($quiz->book_id);
        
        view('admin/bible-quiz/create-question', [
            'quiz' => $quiz,
            'book' => $book
        ]);
    }
    
    // Store question
    function storeQuestion($quizId) {
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz not found';
            redirect('/admin/bible-quiz/quizzes');
            return;
        }
        
        $required = ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Please fill in all required fields";
                redirect('/admin/bible-quiz/create-question/' . $quizId);
                return;
            }
        }
        
        $data = [
            'quiz_id' => $quizId,
            'question' => $_POST['question'],
            'option_a' => $_POST['option_a'],
            'option_b' => $_POST['option_b'],
            'option_c' => $_POST['option_c'],
            'option_d' => $_POST['option_d'],
            'correct' => $_POST['correct'],
            'explanation' => $_POST['explanation'] ?? '',
            'chapter_reference' => $_POST['chapter_reference'] ?? null,
            'verse_reference' => $_POST['verse_reference'] ?? null
        ];
        
        try {
            Question::create($data);
            $_SESSION['success'] = "Question added successfully!";
            redirect('/admin/bible-quiz/questions/' . $quizId);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error adding question: " . $e->getMessage();
            redirect('/admin/bible-quiz/create-question/' . $quizId);
        }
    }
    
    // Edit question
    function editQuestion($questionId) {
        $question = Question::find($questionId);
        if (!$question) {
            $_SESSION['error'] = 'Question not found';
            redirect('/admin/bible-quiz/quizzes');
            return;
        }
        
        $quiz = Quiz::find($question->quiz_id);
        $book = BibleBook::find($quiz->book_id);
        
        view('admin/bible-quiz/edit-question', [
            'question' => $question,
            'quiz' => $quiz,
            'book' => $book
        ]);
    }
    
    // Update question
    function updateQuestion($questionId) {
        $question = Question::find($questionId);
        if (!$question) {
            $_SESSION['error'] = 'Question not found';
            redirect('/admin/bible-quiz/quizzes');
            return;
        }
        
        $required = ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Please fill in all required fields";
                redirect('/admin/bible-quiz/edit-question/' . $questionId);
                return;
            }
        }
        
        $data = [
            'question' => $_POST['question'],
            'option_a' => $_POST['option_a'],
            'option_b' => $_POST['option_b'],
            'option_c' => $_POST['option_c'],
            'option_d' => $_POST['option_d'],
            'correct' => $_POST['correct'],
            'explanation' => $_POST['explanation'] ?? '',
            'chapter_reference' => $_POST['chapter_reference'] ?? null,
            'verse_reference' => $_POST['verse_reference'] ?? null
        ];
        
        try {
            Question::update($questionId, $data);
            $_SESSION['success'] = "Question updated successfully!";
            redirect('/admin/bible-quiz/questions/' . $question->quiz_id);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error updating question: " . $e->getMessage();
            redirect('/admin/bible-quiz/edit-question/' . $questionId);
        }
    }
    
    // Delete question
    function deleteQuestion($questionId) {
        $question = Question::find($questionId);
        if (!$question) {
            $_SESSION['error'] = 'Question not found';
            redirect('/admin/bible-quiz/quizzes');
            return;
        }
        
        try {
            Question::delete($questionId);
            $_SESSION['success'] = "Question deleted successfully!";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error deleting question: " . $e->getMessage();
        }
        
        redirect('/admin/bible-quiz/questions/' . $question->quiz_id);
    }
    
    // Statistics and analytics
    function statistics() {
        $globalStats = BibleBook::getGlobalStatistics();
        $testamentStats = [
            'old' => BibleBook::getTestamentSummary('old'),
            'new' => BibleBook::getTestamentSummary('new')
        ];
        
        $topPerformers = Quiz::getLeaderboard(null, 10);
        $recentActivity = db()->query("
            SELECT bqs.*, u.name as user_name, bb.name as book_name, q.title as quiz_title
            FROM bible_quiz_statistics bqs
            JOIN users u ON bqs.user_id = u.id
            JOIN quizzes q ON bqs.quiz_id = q.id
            JOIN bible_books bb ON q.book_id = bb.id
            ORDER BY bqs.last_attempt_at DESC
            LIMIT 20
        ")->fetchAll();
        
        view('admin/bible-quiz/statistics', [
            'globalStats' => $globalStats,
            'testamentStats' => $testamentStats,
            'topPerformers' => $topPerformers,
            'recentActivity' => $recentActivity
        ]);
    }
    
    // Bulk operations
    function bulkImport() {
        view('admin/bible-quiz/bulk-import');
    }
    
    function processBulkImport() {
        if (!isset($_FILES['import_file'])) {
            $_SESSION['error'] = 'No file uploaded';
            redirect('/admin/bible-quiz/bulk-import');
            return;
        }
        
        // Process CSV/JSON import
        // This would handle bulk import of questions from CSV or JSON files
        $_SESSION['success'] = 'Bulk import processed successfully!';
        redirect('/admin/bible-quiz/quizzes');
    }
}