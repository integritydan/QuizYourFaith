<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-primary mb-3">
            <i class="fas fa-book-open"></i> Bible Quiz Mastery
        </h1>
        <p class="lead text-muted mb-4">
            Test your knowledge of God's Word through all 66 books of the Bible. 
            From Genesis to Revelation, master every chapter and verse!
        </p>
        <div class="d-flex justify-content-center gap-3 mb-4">
            <a href="/bible-quiz/testament/old" class="btn btn-lg btn-primary">
                <i class="fas fa-scroll"></i> Old Testament
            </a>
            <a href="/bible-quiz/testament/new" class="btn btn-lg btn-success">
                <i class="fas fa-cross"></i> New Testament
            </a>
            <a href="/bible-quiz/my-progress" class="btn btn-lg btn-info">
                <i class="fas fa-chart-line"></i> My Progress
            </a>
        </div>
    </div>

    <!-- Global Statistics -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="display-4 text-primary mb-2">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3 class="fw-bold text-primary"><?= $globalStats->total_books ?? 0 ?></h3>
                    <p class="text-muted mb-0">Books of the Bible</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="display-4 text-success mb-2">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="fw-bold text-success"><?= number_format($globalStats->total_users ?? 0) ?></h3>
                    <p class="text-muted mb-0">Quiz Participants</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="display-4 text-warning mb-2">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 class="fw-bold text-warning"><?= number_format($globalStats->total_attempts ?? 0) ?></h3>
                    <p class="text-muted mb-0">Quiz Attempts</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="display-4 text-info mb-2">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <h3 class="fw-bold text-info"><?= round($globalStats->global_avg_score ?? 0) ?>%</h3>
                    <p class="text-muted mb-0">Average Score</p>
                </div>
            </div>
        </div>
    </div>

    <!-- User Progress (if logged in) -->
    <?php if (isset($_SESSION['user_id']) && $userProgress): ?>
    <div class="card mb-5 border-0 shadow-lg">
        <div class="card-header bg-gradient-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-user-graduate"></i> Your Bible Mastery Progress
            </h4>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-5 text-primary mb-2"><?= $userProgress->total_books_completed ?? 0 ?></div>
                        <p class="text-muted mb-0">Books Completed</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-5 text-success mb-2"><?= round($userProgress->avg_score ?? 0) ?>%</div>
                        <p class="text-muted mb-0">Average Score</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-5 text-warning mb-2"><?= $userProgress->perfect_scores ?? 0 ?></div>
                        <p class="text-muted mb-0">Perfect Scores</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-5 text-info mb-0">
                            <span class="badge bg-info fs-6"><?= ucfirst($userProgress->mastery_level ?? 'beginner') ?></span>
                        </div>
                        <p class="text-muted mb-0">Mastery Level</p>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bars -->
            <div class="mt-4">
                <h6 class="mb-3">Testament Progress</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Old Testament</span>
                        <span><?= $testaments['old']->books_with_quizzes ?? 0 ?>/39 Books</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary" style="width: <?= ($testaments['old']->books_with_quizzes / 39) * 100 ?>%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>New Testament</span>
                        <span><?= $testaments['new']->books_with_quizzes ?? 0 ?>/27 Books</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: <?= ($testaments['new']->books_with_quizzes / 27) * 100 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Testament Overview -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-scroll"></i> Old Testament
                        <small class="float-end"><?= $testaments['old']->books_with_quizzes ?? 0 ?>/39 Books</small>
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">From Genesis to Malachi - God's covenant with Israel and preparation for the Messiah</p>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h5 class="text-primary mb-1"><?= $testaments['old']->total_books ?? 0 ?></h5>
                                <small class="text-muted">Total Books</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h5 class="text-primary mb-1"><?= $testaments['old']->total_chapters ?? 0 ?></h5>
                                <small class="text-muted">Total Chapters</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="/bible-quiz/testament/old" class="btn btn-primary w-100">
                            <i class="fas fa-arrow-right"></i> Explore Old Testament
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-lg">
                <div class="card-header bg-gradient-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-cross"></i> New Testament
                        <small class="float-end"><?= $testaments['new']->books_with_quizzes ?? 0 ?>/27 Books</small>
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">From Matthew to Revelation - The life of Christ and the early church</p>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h5 class="text-success mb-1"><?= $testaments['new']->total_books ?? 0 ?></h5>
                                <small class="text-muted">Total Books</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h5 class="text-success mb-1"><?= $testaments['new']->total_chapters ?? 0 ?></h5>
                                <small class="text-muted">Total Chapters</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="/bible-quiz/testament/new" class="btn btn-success w-100">
                            <i class="fas fa-arrow-right"></i> Explore New Testament
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bible Categories -->
    <div class="mb-5">
        <h3 class="mb-4 text-center">
            <i class="fas fa-layer-group"></i> Explore by Category
        </h3>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-<?= htmlspecialchars($category->icon) ?> fa-3x" 
                               style="color: <?= htmlspecialchars($category->color) ?>"></i>
                        </div>
                        <h5 class="card-title"><?= htmlspecialchars($category->display_name) ?></h5>
                        <p class="card-text text-muted small"><?= htmlspecialchars($category->description) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-book"></i> <?= $this->countBooksInCategory($category->name) ?> books
                            </small>
                            <a href="/bible-quiz/category/<?= htmlspecialchars($category->name) ?>" 
                               class="btn btn-sm" 
                               style="background-color: <?= htmlspecialchars($category->color) ?>; color: white;">
                                Explore <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Featured Quizzes -->
    <?php if (!empty($featuredQuizzes)): ?>
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>
                <i class="fas fa-star"></i> Featured Quizzes
            </h3>
            <a href="/bible-quiz/search" class="btn btn-outline-primary">
                <i class="fas fa-search"></i> Browse All Quizzes
            </a>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredQuizzes as $quiz): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm quiz-card">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?= htmlspecialchars($quiz->book_name ?? 'General') ?></h6>
                            <span class="badge bg-light text-dark"><?= ucfirst($quiz->difficulty) ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($quiz->title) ?></h5>
                        <?php if ($quiz->description): ?>
                        <p class="card-text text-muted small"><?= htmlspecialchars(substr($quiz->description, 0, 100)) ?>...</p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <i class="fas fa-question-circle"></i> <?= $quiz->question_count ?? 10 ?> questions
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> <?= round(($quiz->time_limit ?? 600) / 60) ?> min
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-success">Pass: <?= $quiz->passing_score ?? 70 ?>%</span>
                            <a href="/bible-quiz/play/<?= $quiz->id ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-play"></i> Start Quiz
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="text-center">
        <div class="btn-group btn-group-lg" role="group">
            <a href="/bible-quiz/leaderboard" class="btn btn-outline-warning">
                <i class="fas fa-trophy"></i> Leaderboard
            </a>
            <a href="/bible-quiz/achievements" class="btn btn-outline-info">
                <i class="fas fa-medal"></i> Achievements
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/bible-quiz/my-progress" class="btn btn-outline-success">
                <i class="fas fa-chart-line"></i> My Progress
            </a>
            <?php else: ?>
            <a href="/login" class="btn btn-outline-primary">
                <i class="fas fa-sign-in-alt"></i> Login to Track Progress
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.quiz-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.quiz-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
.hover-shadow {
    transition: box-shadow 0.3s ease-in-out;
}
.hover-shadow:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}
.transition {
    transition: all 0.3s ease;
}
</style>

<?php include __DIR__.'/../partials/footer.php'; ?>