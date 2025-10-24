<?php namespace App\Middleware;

use App\Models\User;

class Auth {
    // Check if user is logged in
    static function check() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/login');
            exit;
        }
        return true;
    }
    
    // Check if user has specific role
    static function hasRole($role) {
        self::check();
        
        $userRole = User::getRole($_SESSION['user_id']);
        
        switch ($role) {
            case 'super_admin':
                return $userRole === 'super_admin';
            case 'admin':
                return in_array($userRole, ['admin', 'super_admin']);
            case 'user':
                return in_array($userRole, ['user', 'admin', 'super_admin']);
            default:
                return false;
        }
    }
    
    // Require specific role
    static function requireRole($role) {
        if (!self::hasRole($role)) {
            $_SESSION['error'] = 'Insufficient permissions';
            redirect('/dashboard');
            exit;
        }
    }
    
    // Check if user is banned
    static function checkBanned() {
        self::check();
        
        if (User::isBanned($_SESSION['user_id'])) {
            session_destroy();
            $_SESSION['error'] = 'Your account has been banned';
            redirect('/login');
            exit;
        }
    }
    
    // Get current user data
    static function user() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        return User::findById($_SESSION['user_id']);
    }
    
    // Check if user can access admin panel
    static function canAccessAdmin() {
        return self::hasRole('admin');
    }
    
    // Check if user can access super admin features
    static function canAccessSuperAdmin() {
        return self::hasRole('super_admin');
    }
    
    // Middleware for admin routes
    static function adminMiddleware() {
        self::check();
        self::checkBanned();
        self::requireRole('admin');
    }
    
    // Middleware for super admin routes
    static function superAdminMiddleware() {
        self::check();
        self::checkBanned();
        self::requireRole('super_admin');
    }
    
    // Middleware for user routes
    static function userMiddleware() {
        self::check();
        self::checkBanned();
    }
    
    // Check if user can moderate matches
    static function canModerateMatches() {
        return self::hasRole('admin');
    }
    
    // Check if user can manage system settings
    static function canManageSettings() {
        return self::hasRole('super_admin');
    }
    
    // Rate limiting check
    static function checkRateLimit($action, $limit = 10, $window = 3600) {
        $key = "rate_limit_{$action}_{$_SESSION['user_id']}";
        $current = isset($_SESSION[$key]) ? $_SESSION[$key] : 0;
        $lastReset = isset($_SESSION["{$key}_last_reset"]) ? $_SESSION["{$key}_last_reset"] : time();
        
        // Reset counter if window has passed
        if (time() - $lastReset > $window) {
            $current = 0;
            $_SESSION["{$key}_last_reset"] = time();
        }
        
        if ($current >= $limit) {
            $_SESSION['error'] = 'Rate limit exceeded. Please try again later.';
            redirect('/dashboard');
            exit;
        }
        
        $_SESSION[$key] = $current + 1;
        return true;
    }
    
    // CSRF protection
    static function validateCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
                $_SESSION['error'] = 'Invalid request';
                redirect('/dashboard');
                exit;
            }
            
            if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = 'Invalid security token';
                redirect('/dashboard');
                exit;
            }
        }
    }
    
    // Generate CSRF token
    static function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // Log user action
    static function logAction($action, $metadata = []) {
        if (isset($_SESSION['user_id'])) {
            $st = db()->prepare("INSERT INTO logs (user_id, action, metadata, ip, created_at) VALUES (?, ?, ?, ?, NOW())");
            $st->execute([
                $_SESSION['user_id'],
                $action,
                json_encode($metadata),
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        }
    }
}