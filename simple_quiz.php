<?php
/**
 * Simple Quiz Page for Webuzo Deployment
 * Basic quiz functionality without complex features
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Bible Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-gold: #f59e0b;
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
            --text-light: #e2e8f0;
            --text-muted: #94a3b8;
        }
        
        body {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-light);
            padding-top: 76px;
        }
        
        .quiz-container {
            background: var(--dark-card);
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
        }
        
        .question-card {
            background: linear-gradient(135deg, var(--dark-card), #334155);
            border: 1px solid #475569;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .option-btn {
            background: transparent;
            border: 2px solid #475569;
            color: var(--text-light);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            text-align: left;
        }
        
        .option-btn:hover {
            border-color: var(--primary-blue);
            background: rgba(37, 99, 235, 0.1);
        }
        
        .option-btn.selected {
            border-color: var(--primary-blue);
            background: rgba(37, 99, 235, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), #3b82f6);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-gold));
        }
        
        .result-card {
            background: linear-gradient(135deg, var(--success-green), #10b981);
            color: white;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-cross me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="?page=quiz">Bible Quiz</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=login">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Quiz Container -->
    <div class="container">
        <div class="quiz-container">
            <?php
            // Get book ID from URL
            $book_id = isset($_GET['book']) ? intval($_GET['book']) : 0;
            
            if ($book_id > 0) {
                // Get book info and questions
                try {
                    $db = getDB();
                    
                    // Get book info
                    $stmt = $db->prepare("SELECT * FROM bible_books WHERE id = ?");
                    $stmt->execute([$book_id]);
                    $book = $stmt->fetch();
                    
                    if ($book) {
                        echo '<h2 class="text-white mb-4">' . htmlspecialchars($book['name']) . ' Quiz</h2>';
                        echo '<p class="text-muted mb-4">' . htmlspecialchars($book['description']) . '</p>';
                        
                        // Get questions for this book
                        $stmt = $db->prepare("SELECT * FROM questions WHERE bible_book_id = ? ORDER BY RAND() LIMIT 5");
                        $stmt->execute([$book_id]);
                        $questions = $stmt->fetchAll();
                        
                        if (count($questions) > 0) {
                            echo '<form method="POST" action="?page=quiz&book=' . $book_id . '">';
                            echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
                            
                            foreach ($questions as $index => $question):
                                $question_number = $index + 1;
                            ?>
                                <div class="question-card">
                                    <h5 class="text-white mb-3">
                                        <span class="text-primary">Question <?php echo $question_number; ?>:</span>
                                        <?php echo htmlspecialchars($question['question']); ?>
                                    </h5>
                                    <div class="options">
                                        <button type="button" class="option-btn w-100" onclick="selectOption(this, '<?php echo $question['id']; ?>', '<?php echo htmlspecialchars($question['answer']); ?>')">
                                            <?php echo htmlspecialchars($question['answer']); ?>
                                        </button>
                                        <button type="button" class="option-btn w-100" onclick="selectOption(this, '<?php echo $question['id']; ?>', 'Wrong Answer 1')">
                                            Wrong Answer 1
                                        </button>
                                        <button type="button" class="option-btn w-100" onclick="selectOption(this, '<?php echo $question['id']; ?>', 'Wrong Answer 2')">
                                            Wrong Answer 2
                                        </button>
                                    </div>
                                    <input type="hidden" name="answers[<?php echo $question['id']; ?>]" id="answer_<?php echo $question['id']; ?>">
                                </div>
                            <?php
                            endforeach;
                            
                            echo '<div class="text-center mt-4">';
                            echo '<button type="submit" class="btn btn-primary btn-lg">';
                            echo '<i class="fas fa-check me-2"></i>Submit Quiz';
                            echo '</button>';
                            echo '</div>';
                            echo '</form>';
                        } else {
                            echo '<div class="text-center">';
                            echo '<i class="fas fa-question-circle fa-3x text-muted mb-3"></i>';
                            echo '<h4>No Questions Available</h4>';
                            echo '<p class="text-muted">No questions found for this book yet.</p>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="text-center">';
                        echo '<i class="fas fa-book fa-3x text-muted mb-3"></i>';
                        echo '<h4>Book Not Found</h4>';
                        echo '<p class="text-muted">The requested Bible book was not found.</p>';
                        echo '</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="text-center">';
                    echo '<i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>';
                    echo '<h4>Database Error</h4>';
                    echo '<p class="text-muted">Please ensure the database is properly set up.</p>';
                    echo '<a href="simple_install.sql" class="btn btn-primary" download>';
                    echo '<i class="fas fa-download me-2"></i>Download Setup File';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                // Show available books
                echo '<h2 class="text-white mb-4">Choose a Bible Book</h2>';
                echo '<p class="text-muted mb-4">Select a book to start your quiz</p>';
                
                try {
                    $db = getDB();
                    $stmt = $db->query("SELECT * FROM bible_books ORDER BY name");
                    $books = $stmt->fetchAll();
                    
                    if (count($books) > 0) {
                        echo '<div class="row">';
                        foreach ($books as $book):
                        ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="book-category"><?php echo htmlspecialchars($book['category']); ?></div>
                                        <h5 class="book-title"><?php echo htmlspecialchars($book['name']); ?></h5>
                                        <p class="book-description"><?php echo htmlspecialchars($book['description']); ?></p>
                                        <div class="book-stats">
                                            <span><i class="fas fa-question-circle"></i> <?php echo $book['question_count']; ?> Questions</span>
                                        </div>
                                        <a href="?page=quiz&book=<?php echo $book['id']; ?>" class="btn btn-primary w-100">
                                            <i class="fas fa-play me-2"></i>Start Quiz
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php
                        endforeach;
                        echo '</div>';
                    } else {
                        echo '<div class="text-center">';
                        echo '<i class="fas fa-database fa-3x text-muted mb-3"></i>';
                        echo '<h4>No Books Available</h4>';
                        echo '<p class="text-muted">Please set up the database first.</p>';
                        echo '<a href="simple_install.sql" class="btn btn-primary" download>';
                        echo '<i class="fas fa-download me-2"></i>Download Setup File';
                        echo '</a>';
                        echo '</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="text-center">';
                    echo '<i class="fas fa-database fa-3x text-muted mb-3"></i>';
                    echo '<h4>Database Setup Required</h4>';
                    echo '<p class="text-muted">Please run the simple_install.sql file in your Webuzo database manager.</p>';
                    echo '<a href="simple_install.sql" class="btn btn-primary" download>';
                    echo '<i class="fas fa-download me-2"></i>Download SQL File';
                    echo '</a>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectOption(button, questionId, answer) {
            // Remove selected class from all buttons in this question
            const questionCard = button.closest('.question-card');
            const buttons = questionCard.querySelectorAll('.option-btn');
            buttons.forEach(btn => btn.classList.remove('selected'));
            
            // Add selected class to clicked button
            button.classList.add('selected');
            
            // Set the hidden input value
            document.getElementById('answer_' + questionId).value = answer;
        }
    </script>
</body>
</html>