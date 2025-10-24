<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
    <!-- Quiz Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-3">
                <img src="https://img.youtube.com/vi/<?= htmlspecialchars($book->id) ?>/maxresdefault.jpg" 
                     alt="<?= htmlspecialchars($book->name) ?>" 
                     class="rounded me-3" style="width: 80px; height: 60px; object-fit: cover;">
                <div>
                    <h2 class="mb-1"><?= htmlspecialchars($quiz->title) ?></h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-book"></i> <?= htmlspecialchars($book->name) ?> 
                        <span class="badge ms-2" style="background-color: <?= Quiz::getDifficultyColor($quiz->difficulty) ?>">
                            <?= ucfirst($quiz->difficulty) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-end">
                <div class="quiz-timer mb-2">
                    <div class="timer-display">
                        <i class="fas fa-clock"></i>
                        <span id="timer"><?= gmdate("i:s", $timer) ?></span>
                    </div>
                </div>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-primary" id="progressBar" style="width: 0%"></div>
                </div>
                <small class="text-muted">
                    Question <span id="currentQuestion">1</span> of <?= count($questions) ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Quiz Form -->
    <form id="quizForm" action="/bible-quiz/submit/<?= $quiz->id ?>" method="POST">
        <div class="row">
            <div class="col-lg-8">
                <!-- Question Container -->
                <div id="questionContainer" class="card border-0 shadow-lg mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-question-circle"></i> Question <span id="questionNumber">1</span>
                            </h5>
                            <div class="question-timer">
                                <i class="fas fa-hourglass-half"></i>
                                <span id="questionTimer">00:00</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div id="questionContent">
                            <!-- Questions will be loaded here dynamically -->
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="d-flex justify-content-between mb-4">
                    <button type="button" id="prevBtn" class="btn btn-outline-secondary" onclick="changeQuestion(-1)" disabled>
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <div>
                        <button type="button" id="flagBtn" class="btn btn-outline-warning me-2" onclick="flagQuestion()">
                            <i class="fas fa-flag"></i> Flag
                        </button>
                        <button type="button" id="nextBtn" class="btn btn-primary" onclick="changeQuestion(1)">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Flagged Questions Indicator -->
                <div id="flaggedIndicator" class="alert alert-warning d-none">
                    <i class="fas fa-flag"></i> You have flagged <span id="flaggedCount">0</span> question(s)
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Question Navigator -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-list-ol"></i> Question Navigator
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="questionNavigator" class="question-grid">
                            <!-- Question buttons will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Book Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i> About <?= htmlspecialchars($book->name) ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge" style="background-color: <?= htmlspecialchars($book->category_color) ?>">
                                <i class="fas fa-<?= htmlspecialchars($book->category_icon) ?>"></i> 
                                <?= htmlspecialchars($book->category_name) ?>
                            </span>
                            <span class="badge bg-secondary ms-2">
                                <?= $book->chapters ?> Chapters
                            </span>
                        </div>
                        <p class="small text-muted mb-3"><?= htmlspecialchars($book->description) ?></p>
                        <?php if ($book->key_verses): ?>
                        <div class="bg-light p-3 rounded">
                            <small class="text-muted d-block mb-1">Key Verse:</small>
                            <em class="small">"<?= htmlspecialchars($book->key_verses) ?>"</em>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quiz Info -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-cog"></i> Quiz Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-bullseye text-primary"></i> 
                                <strong>Passing Score:</strong> <?= $quiz->passing_score ?? 70 ?>%
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-clock text-warning"></i> 
                                <strong>Time Limit:</strong> <?= round(($quiz->time_limit ?? 600) / 60) ?> minutes
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-signal text-info"></i> 
                                <strong>Difficulty:</strong> <?= ucfirst($quiz->difficulty) ?>
                            </li>
                            <li>
                                <i class="fas fa-book text-success"></i> 
                                <strong>Questions:</strong> <?= count($questions) ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center mt-4">
            <button type="button" id="submitBtn" class="btn btn-success btn-lg px-5" onclick="confirmSubmit()">
                <i class="fas fa-check-circle"></i> Submit Quiz
            </button>
        </div>
    </form>
</div>

<!-- JavaScript for Quiz Functionality -->
<script>
// Quiz state
let currentQuestionIndex = 0;
let questions = <?= json_encode($questions) ?>;
let totalQuestions = questions.length;
let flaggedQuestions = new Set();
let questionStartTime = Date.now();
let quizStartTime = <?= time() * 1000 ?>;
let timeLimit = <?= $timer ?>;

// Initialize quiz
document.addEventListener('DOMContentLoaded', function() {
    displayQuestion(0);
    createQuestionNavigator();
    startTimer();
    startQuestionTimer();
});

// Display current question
function displayQuestion(index) {
    if (index < 0 || index >= totalQuestions) return;
    
    currentQuestionIndex = index;
    let question = questions[index];
    
    // Update question content
    document.getElementById('questionNumber').textContent = index + 1;
    document.getElementById('currentQuestion').textContent = index + 1;
    document.getElementById('questionContent').innerHTML = `
        <div class="question-text mb-4">
            <h4>${question.question}</h4>
            ${question.chapter_reference ? `<small class="text-muted"><i class="fas fa-book"></i> Reference: ${question.chapter_reference}</small>` : ''}
        </div>
        <div class="options">
            ${createOptions(question)}
        </div>
    `;
    
    // Restore previous answer if exists
    let previousAnswer = sessionStorage.getItem(`quiz_<?= $quiz->id ?>_q${index}`);
    if (previousAnswer) {
        document.querySelector(`input[name="answers[${question.id}]"][value="${previousAnswer}"]`).checked = true;
    }
    
    // Update navigation
    updateNavigation();
    updateQuestionNavigator();
    updateProgressBar();
    
    // Reset question timer
    questionStartTime = Date.now();
}

// Create options HTML
function createOptions(question) {
    let options = ['option_a', 'option_b', 'option_c', 'option_d'];
    let letters = ['A', 'B', 'C', 'D'];
    
    return options.map((opt, i) => `
        <div class="form-check mb-3 p-3 border rounded option-hover ${flaggedQuestions.has(currentQuestionIndex) ? 'border-warning' : ''}" 
             onclick="selectOption('${question.id}', '${letters[i].toLowerCase()}')">
            <input class="form-check-input" type="radio" 
                   name="answers[${question.id}]" 
                   id="option_${letters[i].toLowerCase()}_${question.id}" 
                   value="${letters[i].toLowerCase()}"
                   onchange="saveAnswer(${question.id}, '${letters[i].toLowerCase()}')">
            <label class="form-check-label w-100" for="option_${letters[i].toLowerCase()}_${question.id}">
                <strong>${letters[i]}.</strong> ${question[opt]}
            </label>
        </div>
    `).join('');
}

// Select option visually
function selectOption(questionId, option) {
    // Remove previous selections
    document.querySelectorAll(`input[name="answers[${questionId}]"]`).forEach(input => {
        input.closest('.form-check').classList.remove('selected');
    });
    
    // Add selection to clicked option
    document.querySelector(`input[name="answers[${questionId}]"][value="${option}"]`).closest('.form-check').classList.add('selected');
}

// Save answer to session storage
function saveAnswer(questionId, answer) {
    sessionStorage.setItem(`quiz_<?= $quiz->id ?>_q${currentQuestionIndex}`, answer);
}

// Change question
function changeQuestion(direction) {
    let newIndex = currentQuestionIndex + direction;
    if (newIndex >= 0 && newIndex < totalQuestions) {
        displayQuestion(newIndex);
    }
}

// Flag question
function flagQuestion() {
    if (flaggedQuestions.has(currentQuestionIndex)) {
        flaggedQuestions.delete(currentQuestionIndex);
    } else {
        flaggedQuestions.add(currentQuestionIndex);
    }
    updateFlaggedIndicator();
    updateQuestionNavigator();
}

// Update flagged indicator
function updateFlaggedIndicator() {
    let flaggedCount = flaggedQuestions.size;
    let indicator = document.getElementById('flaggedIndicator');
    let countSpan = document.getElementById('flaggedCount');
    
    if (flaggedCount > 0) {
        indicator.classList.remove('d-none');
        countSpan.textContent = flaggedCount;
    } else {
        indicator.classList.add('d-none');
    }
}

// Create question navigator
function createQuestionNavigator() {
    let navigator = document.getElementById('questionNavigator');
    navigator.innerHTML = '';
    
    for (let i = 0; i < totalQuestions; i++) {
        let btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-secondary m-1 question-nav-btn';
        btn.textContent = i + 1;
        btn.onclick = () => displayQuestion(i);
        btn.id = `nav-btn-${i}`;
        navigator.appendChild(btn);
    }
}

// Update question navigator
function updateQuestionNavigator() {
    document.querySelectorAll('.question-nav-btn').forEach((btn, index) => {
        btn.classList.remove('btn-primary', 'btn-warning', 'btn-success');
        
        if (index === currentQuestionIndex) {
            btn.classList.add('btn-primary');
        } else if (flaggedQuestions.has(index)) {
            btn.classList.add('btn-warning');
        } else if (sessionStorage.getItem(`quiz_<?= $quiz->id ?>_q${index}`)) {
            btn.classList.add('btn-success');
        }
    });
}

// Update navigation buttons
function updateNavigation() {
    document.getElementById('prevBtn').disabled = (currentQuestionIndex === 0);
    document.getElementById('nextBtn').disabled = (currentQuestionIndex === totalQuestions - 1);
}

// Update progress bar
function updateProgressBar() {
    let progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
    document.getElementById('progressBar').style.width = progress + '%';
}

// Timer functionality
function startTimer() {
    let timeLeft = timeLimit;
    let timerInterval = setInterval(function() {
        timeLeft--;
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        document.getElementById('timer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            autoSubmit();
        }
    }, 1000);
}

// Question timer
function startQuestionTimer() {
    let questionTime = 0;
    let questionInterval = setInterval(function() {
        questionTime++;
        let minutes = Math.floor(questionTime / 60);
        let seconds = questionTime % 60;
        document.getElementById('questionTimer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }, 1000);
    
    // Store interval ID for cleanup when changing questions
    window.currentQuestionInterval = questionInterval;
}

// Auto submit when time runs out
function autoSubmit() {
    if (confirm('Time is up! Submit your quiz now?')) {
        document.getElementById('quizForm').submit();
    } else {
        document.getElementById('quizForm').submit();
    }
}

// Confirm submit
function confirmSubmit() {
    let unanswered = 0;
    for (let i = 0; i < totalQuestions; i++) {
        if (!sessionStorage.getItem(`quiz_<?= $quiz->id ?>_q${i}`)) {
            unanswered++;
        }
    }
    
    let message = `You have answered ${totalQuestions - unanswered} out of ${totalQuestions} questions.`;
    if (unanswered > 0) {
        message += `\n\nYou have ${unanswered} unanswered question(s).`;
    }
    if (flaggedQuestions.size > 0) {
        message += `\n\nYou have flagged ${flaggedQuestions.size} question(s).`;
    }
    message += `\n\nAre you sure you want to submit your quiz?`;
    
    if (confirm(message)) {
        document.getElementById('quizForm').submit();
    }
}

// Add CSS for better styling
const style = document.createElement('style');
style.textContent = `
    .option-hover {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .option-hover:hover {
        background-color: #f8f9fa;
        border-color: #007bff !important;
    }
    .option-hover.selected {
        background-color: #e3f2fd;
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    .question-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
        gap: 5px;
    }
    .question-nav-btn {
        width: 40px;
        height: 40px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .quiz-timer {
        font-size: 1.5rem;
        font-weight: bold;
        color: #dc3545;
    }
    .question-timer {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .timer-display {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
    }
`;
document.head.appendChild(style);
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>