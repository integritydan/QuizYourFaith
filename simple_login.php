<?php
/**
 * Simple Login Page for Webuzo Deployment
 * Basic authentication without complex features
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Login</title>
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
        
        .login-container {
            background: var(--dark-card);
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 2.5rem;
            margin: 2rem auto;
            max-width: 400px;
        }
        
        .form-control {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid #475569;
            color: var(--text-light);
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus {
            background: rgba(30, 41, 59, 0.7);
            border-color: var(--primary-blue);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), #3b82f6);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }
        
        .btn-outline-light {
            border: 2px solid #475569;
            color: var(--text-light);
        }
        
        .btn-outline-light:hover {
            background: #475569;
            border-color: #475569;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .navbar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #334155;
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: 700;
        }
        
        .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: var(--primary-blue) !important;
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
                        <a class="nav-link" href="?page=quiz">Bible Quiz</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="?page=login">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Container -->
    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <i class="fas fa-sign-in-alt fa-3x text-primary mb-3"></i>
                <h2 class="text-white">Welcome Back</h2>
                <p class="text-muted">Sign in to your account to continue</p>
            </div>

            <?php
            // Handle login form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyToken($_POST['csrf_token'])) {
                $username = sanitize($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                
                if (!empty($username) && !empty($password)) {
                    try {
                        $db = getDB();
                        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
                        $stmt->execute([$username, $username]);
                        $user = $stmt->fetch();
                        
                        if ($user && password_verify($password, $user['password'])) {
                            // Simple session login
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['logged_in'] = true;
                            
                            echo '<div class="alert alert-success">';
                            echo '<i class="fas fa-check-circle me-2"></i>Login successful! Redirecting...';
                            echo '</div>';
                            echo '<script>setTimeout(function(){ window.location.href = "/"; }, 2000);</script>';
                        } else {
                            echo '<div class="alert alert-danger">';
                            echo '<i class="fas fa-exclamation-circle me-2"></i>Invalid username or password.';
                            echo '</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">';
                        echo '<i class="fas fa-exclamation-circle me-2"></i>Database error. Please try again.';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-warning">';
                    echo '<i class="fas fa-exclamation-triangle me-2"></i>Please fill in all fields.';
                    echo '</div>';
                }
            }
            ?>

            <!-- Login Form -->
            <form method="POST" action="?page=login">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label text-light">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           placeholder="Enter your username or email">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label text-light">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </div>
                
                <div class="text-center">
                    <p class="text-muted mb-0">Don't have an account?</p>
                    <a href="?page=register" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </a>
                </div>
            </form>
            
            <!-- Demo Login Info -->
            <div class="mt-4 p-3 bg-dark rounded">
                <h6 class="text-white mb-2">Demo Login</h6>
                <p class="text-muted mb-1"><strong>Username:</strong> admin</p>
                <p class="text-muted mb-0"><strong>Password:</strong> password</p>
                <small class="text-muted">(Change this in the database after setup)</small>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4">
        <div class="container">
            <p class="text-muted mb-0">&copy; 2025 <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p class="text-muted mb-0">Developed by Dan Onos</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>