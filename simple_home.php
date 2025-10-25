<?php
/**
 * Simple Homepage for Webuzo Deployment
 * Professional corporate design with basic functionality
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Biblical Gaming Platform</title>
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
        }
        
        .navbar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #334155;
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-blue) !important;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #3b82f6 100%);
            padding: 4rem 0;
            margin-top: 76px;
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: white;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-light);
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), #3b82f6);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }
        
        .card {
            background: var(--dark-card);
            border: 1px solid #334155;
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border-color: var(--primary-blue);
        }
        
        .book-card {
            background: linear-gradient(135deg, var(--dark-card), #334155);
            border: 1px solid #475569;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .book-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary-blue);
        }
        
        .book-category {
            color: var(--primary-gold);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .book-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }
        
        .book-description {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .book-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        
        .footer {
            background: #0f172a;
            border-top: 1px solid #334155;
            padding: 2rem 0;
            margin-top: 4rem;
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
                        <a class="nav-link active" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=quiz">Bible Quiz</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=register">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-8">
                    <h1 class="hero-title">Master the Scriptures</h1>
                    <p class="hero-subtitle">Join thousands of players in the ultimate biblical knowledge challenge through interactive gaming.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="?page=quiz" class="btn btn-primary btn-lg">
                            <i class="fas fa-play me-2"></i>Start Playing
                        </a>
                        <a href="?page=register" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Join Free
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card text-center p-3">
                                <h3 class="text-primary mb-1">66</h3>
                                <small class="text-muted">Bible Books</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card text-center p-3">
                                <h3 class="text-primary mb-1">1.2M</h3>
                                <small class="text-muted">Questions</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card text-center p-3">
                                <h3 class="text-primary mb-1">50K+</h3>
                                <small class="text-muted">Players</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card text-center p-3">
                                <h3 class="text-primary mb-1">24/7</h3>
                                <small class="text-muted">Available</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bible Books Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="text-white mb-3">Sacred Scripture Collection</h2>
                <p class="text-muted">Explore all 66 books of the Bible through interactive quizzes</p>
            </div>
            
            <div class="row">
                <?php
                // Get Bible books from database
                try {
                    $db = getDB();
                    $stmt = $db->query("SELECT * FROM bible_books ORDER BY name LIMIT 6");
                    $books = $stmt->fetchAll();
                    
                    foreach ($books as $book):
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="book-card">
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
                <?php
                    endforeach;
                } catch (Exception $e) {
                    // Show placeholder if database not ready
                    ?>
                    <div class="col-12">
                        <div class="card text-center p-5">
                            <i class="fas fa-database fa-3x text-muted mb-3"></i>
                            <h4>Database Setup Required</h4>
                            <p class="text-muted">Please run the simple_install.sql file in your Webuzo database manager to set up the Bible books.</p>
                            <a href="simple_install.sql" class="btn btn-primary" download>
                                <i class="fas fa-download me-2"></i>Download SQL File
                            </a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="text-white mb-3">Gaming Features</h2>
                <p class="text-muted">Advanced gaming mechanics to enhance your biblical learning experience</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card text-center p-4">
                        <i class="fas fa-trophy fa-2x text-primary mb-3"></i>
                        <h4>Leaderboards</h4>
                        <p class="text-muted small">Compete with players worldwide</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-4">
                        <i class="fas fa-fire fa-2x text-primary mb-3"></i>
                        <h4>Daily Streaks</h4>
                        <p class="text-muted small">Build consistent study habits</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-4">
                        <i class="fas fa-bolt fa-2x text-primary mb-3"></i>
                        <h4>Quick Play</h4>
                        <p class="text-muted small">Jump into quizzes instantly</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-4">
                        <i class="fas fa-chart-line fa-2x text-primary mb-3"></i>
                        <h4>Progress Tracking</h4>
                        <p class="text-muted small">Monitor your learning journey</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-cross me-2"></i><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted">The ultimate biblical gaming platform designed to help you master Scripture through interactive quizzes.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">&copy; 2025 <?php echo SITE_NAME; ?>. All rights reserved.</p>
                    <p class="text-muted mb-0">Developed by Dan Onos</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>