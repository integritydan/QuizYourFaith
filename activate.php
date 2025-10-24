<?php
/**
 * QuizYourFaith Activation Page
 * This page handles the activation process
 */

// Include activation system
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/activation.php';

// Check if already activated
if (ActivationSystem::isActivated()) {
    header('Location: /');
    exit;
}

$message = '';
$error = '';

// Handle activation form submission
if ($_POST['activate'] ?? false) {
    $code = trim($_POST['activation_code'] ?? '');
    
    if (empty($code)) {
        $error = 'Please enter an activation code';
    } else {
        $result = ActivationSystem::activate($code);
        
        if ($result['success']) {
            $message = $result['message'];
            // Redirect to main page after successful activation
            header('Refresh: 2; URL=/');
        } else {
            $error = $result['message'];
        }
    }
}

// Handle contact request
if ($_POST['contact'] ?? false) {
    $message = 'Thank you for your interest. Please contact the system administrator for an activation code.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QuizYourFaith - Activation Required</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .activation-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 500px;
            width: 90%;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #667eea;
            font-weight: bold;
        }
        .activation-code {
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="activation-container">
        <div class="logo">
            <h1>QuizYourFaith</h1>
            <p class="text-muted">Activation Required</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Welcome to QuizYourFaith!</h5>
                <p class="card-text">
                    This application requires activation to ensure authorized usage. 
                    Please enter your activation code below to continue.
                </p>
            </div>
        </div>

        <form method="post" class="mb-4">
            <div class="mb-3">
                <label for="activation_code" class="form-label">Activation Code</label>
                <input type="text" 
                       class="form-control activation-code" 
                       id="activation_code" 
                       name="activation_code" 
                       placeholder="Enter your activation code"
                       required
                       maxlength="50">
                <div class="form-text">Format: QYF-YYYY-TYPE-###</div>
            </div>
            
            <button type="submit" name="activate" class="btn btn-primary w-100 mb-3">
                Activate Application
            </button>
        </form>

        <div class="text-center">
            <p class="text-muted mb-2">Don't have an activation code?</p>
            <form method="post" class="d-inline">
                <button type="submit" name="contact" class="btn btn-outline-secondary btn-sm">
                    Contact Administrator
                </button>
            </form>
        </div>

        <hr class="my-4">

        <div class="text-center text-muted small">
            <p>This application is protected by an activation system to prevent unauthorized usage.</p>
            <p>&copy; <?= date('Y') ?> QuizYourFaith. All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-format activation code input
        document.getElementById('activation_code').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
            e.target.value = value;
        });
    </script>
</body>
</html>