<?php namespace App\Controllers;
use App\Models\User;
use App\Middleware\Auth;

class AuthController {
    function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $u = User::findByEmail($_POST['email']);
            
            if ($u && password_verify($_POST['password'], $u->password)) {
                // Check if user is banned
                if (User::isBanned($u->id)) {
                    $_SESSION['error'] = 'Your account has been banned. Please contact support.';
                    redirect('/login');
                    exit;
                }
                
                // Set session data
                $_SESSION['user_id'] = $u->id;
                $_SESSION['role'] = $u->role;
                $_SESSION['username'] = $u->name;
                $_SESSION['login_time'] = time();
                
                // Update online status
                User::updateOnlineStatus($u->id, 'online');
                
                // Log the login action
                Auth::logAction('user_login', ['email' => $_POST['email']]);
                
                // Redirect based on role
                switch ($u->role) {
                    case 'super_admin':
                        redirect('/admin/super');
                        break;
                    case 'admin':
                        redirect('/admin');
                        break;
                    default:
                        redirect('/dashboard');
                        break;
                }
                exit;
            }
            
            $_SESSION['error'] = 'Invalid credentials';
            Auth::logAction('failed_login', ['email' => $_POST['email']]);
        }
        
        // Generate CSRF token
        $csrf_token = Auth::generateCSRF();
        view('auth/login', ['csrf_token' => $csrf_token]);
    }
    
    function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            // Validate input
            $errors = [];
            
            if (empty($_POST['name'])) {
                $errors[] = 'Name is required';
            }
            
            if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            }
            
            if (empty($_POST['password']) || strlen($_POST['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }
            
            if ($_POST['password'] !== $_POST['password_confirm']) {
                $errors[] = 'Passwords do not match';
            }
            
            // Check if email already exists
            if (User::findByEmail($_POST['email'])) {
                $errors[] = 'Email already registered';
            }
            
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                redirect('/register');
                exit;
            }
            
            try {
                $id = User::create([
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
                ]);
                
                // Set session data
                $_SESSION['user_id'] = $id;
                $_SESSION['role'] = 'user';
                $_SESSION['username'] = $_POST['name'];
                $_SESSION['login_time'] = time();
                
                // Update online status
                User::updateOnlineStatus($id, 'online');
                
                // Log the registration
                Auth::logAction('user_register', ['email' => $_POST['email']]);
                
                redirect('/dashboard');
                exit;
                
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Registration failed. Please try again.';
                Auth::logAction('registration_error', ['email' => $_POST['email'], 'error' => $e->getMessage()]);
            }
        }
        
        // Generate CSRF token
        $csrf_token = Auth::generateCSRF();
        view('auth/register', ['csrf_token' => $csrf_token]);
    }
    
    function logout() {
        if (isset($_SESSION['user_id'])) {
            // Update online status
            User::updateOnlineStatus($_SESSION['user_id'], 'offline');
            
            // Log the logout
            Auth::logAction('user_logout');
            
            // Destroy session
            session_destroy();
        }
        
        redirect('/');
    }
    
    // Password reset functionality
    function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $email = $_POST['email'] ?? '';
            $user = User::findByEmail($email);
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token in database
                $st = db()->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $st->execute([$user->id, $token, $expires]);
                
                // Send reset email (implementation depends on your email system)
                // mail($email, 'Password Reset', "Reset link: /reset-password?token={$token}");
                
                $_SESSION['success'] = 'Password reset instructions have been sent to your email';
                Auth::logAction('password_reset_request', ['email' => $email]);
            } else {
                $_SESSION['error'] = 'Email not found';
            }
            
            redirect('/login');
        }
        
        $csrf_token = Auth::generateCSRF();
        view('auth/forgot_password', ['csrf_token' => $csrf_token]);
    }
    
    function resetPassword() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['error'] = 'Invalid reset token';
            redirect('/login');
            exit;
        }
        
        // Validate token
        $st = db()->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $st->execute([$token]);
        $reset = $st->fetch();
        
        if (!$reset) {
            $_SESSION['error'] = 'Invalid or expired reset token';
            redirect('/login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            if (empty($_POST['password']) || strlen($_POST['password']) < 6) {
                $_SESSION['error'] = 'Password must be at least 6 characters';
                redirect("/reset-password?token={$token}");
                exit;
            }
            
            if ($_POST['password'] !== $_POST['password_confirm']) {
                $_SESSION['error'] = 'Passwords do not match';
                redirect("/reset-password?token={$token}");
                exit;
            }
            
            // Update password
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $st = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
            $st->execute([$hashedPassword, $reset->user_id]);
            
            // Delete reset token
            $st = db()->prepare("DELETE FROM password_resets WHERE token = ?");
            $st->execute([$token]);
            
            $_SESSION['success'] = 'Password has been reset successfully';
            Auth::logAction('password_reset_completed', ['user_id' => $reset->user_id]);
            
            redirect('/login');
            exit;
        }
        
        $csrf_token = Auth::generateCSRF();
        view('auth/reset_password', ['csrf_token' => $csrf_token, 'token' => $token]);
    }
    
    // Profile update functionality
    function updateProfile() {
        Auth::userMiddleware();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $userId = $_SESSION['user_id'];
            $data = [];
            
            if (!empty($_POST['name'])) {
                $data['name'] = $_POST['name'];
            }
            
            if (!empty($_POST['bio'])) {
                $data['bio'] = $_POST['bio'];
            }
            
            if (isset($_POST['max_friends']) && is_numeric($_POST['max_friends'])) {
                $maxFriends = min(max((int)$_POST['max_friends'], 10), 200);
                $data['max_friends'] = $maxFriends;
            }
            
            // Handle avatar upload
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 2 * 1024 * 1024; // 2MB
                
                if (in_array($_FILES['avatar']['type'], $allowedTypes) && $_FILES['avatar']['size'] <= $maxSize) {
                    $uploadDir = 'uploads/avatars/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $filename = $userId . '_' . time() . '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $filepath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filepath)) {
                        $data['avatar'] = '/' . $filepath;
                    }
                }
            }
            
            if (User::updateProfile($userId, $data)) {
                $_SESSION['success'] = 'Profile updated successfully';
                $_SESSION['username'] = $data['name'] ?? $_SESSION['username'];
                Auth::logAction('profile_updated', $data);
            } else {
                $_SESSION['error'] = 'Failed to update profile';
            }
            
            redirect('/account/profile');
        }
    }
}
