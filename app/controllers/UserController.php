<?php namespace App\Controllers;
use App\Models\User;
use App\Middleware\Auth;

class UserController {
    
    function __construct() {
        Auth::userMiddleware();
    }
    
    // User dashboard with multiplayer features
    function dashboard() {
        $userId = $_SESSION['user_id'];
        
        // Get user stats
        $stats = User::getStats($userId);
        
        // Get active matches
        $activeMatches = User::getActiveMatches($userId);
        
        // Get recent match history
        $recentMatches = User::getMatchHistory($userId, 5);
        
        // Get friends
        $friends = User::getFriends($userId);
        $onlineFriends = array_filter($friends, function($friend) {
            return $friend->online_status === 'online';
        });
        
        // Get pending friend requests
        $pendingRequests = db()->prepare("
            SELECT u.*, f.created_at as request_sent
            FROM friends f
            JOIN users u ON f.user_id = u.id
            WHERE f.friend_id = ? AND f.status = 'pending'
            ORDER BY f.created_at DESC
        ");
        $pendingRequests->execute([$userId]);
        
        // Get available tournaments
        $availableTournaments = db()->prepare("
            SELECT t.*, u.name as creator_name,
                   (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = t.id) as participant_count
            FROM tournaments t
            JOIN users u ON t.created_by = u.id
            WHERE t.status = 'upcoming' 
            AND t.start_time > NOW()
            AND t.id NOT IN (SELECT tournament_id FROM tournament_participants WHERE user_id = ?)
            ORDER BY t.start_time ASC
            LIMIT 5
        ");
        $availableTournaments->execute([$userId]);
        
        // Get user achievements
        $achievements = db()->prepare("
            SELECT ma.*, uma.earned_at
            FROM multiplayer_achievements ma
            JOIN user_multiplayer_achievements uma ON ma.id = uma.achievement_id
            WHERE uma.user_id = ?
            ORDER BY uma.earned_at DESC
        ");
        $achievements->execute([$userId]);
        
        view('user/dashboard', [
            'stats' => $stats,
            'active_matches' => $activeMatches,
            'recent_matches' => $recentMatches,
            'friends' => $friends,
            'online_friends' => $onlineFriends,
            'pending_requests' => $pendingRequests->fetchAll(),
            'available_tournaments' => $availableTournaments->fetchAll(),
            'achievements' => $achievements->fetchAll()
        ]);
    }
    
    // Profile page
    function profile() {
        $userId = $_SESSION['user_id'];
        $user = User::findById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            redirect('/dashboard');
            exit;
        }
        
        $stats = User::getStats($userId);
        $recentMatches = User::getMatchHistory($userId, 10);
        $achievements = db()->prepare("
            SELECT ma.*, uma.earned_at
            FROM multiplayer_achievements ma
            JOIN user_multiplayer_achievements uma ON ma.id = uma.achievement_id
            WHERE uma.user_id = ?
            ORDER BY uma.earned_at DESC
        ");
        $achievements->execute([$userId]);
        
        view('user/profile', [
            'user' => $user,
            'stats' => $stats,
            'recent_matches' => $recentMatches,
            'achievements' => $achievements->fetchAll()
        ]);
    }
    
    // Edit profile
    function editProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $userId = $_SESSION['user_id'];
            $data = [];
            
            if (!empty($_POST['name'])) {
                $data['name'] = $_POST['name'];
            }
            
            if (isset($_POST['bio'])) {
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
            
            redirect('/user/profile');
            exit;
        }
        
        $user = User::findById($_SESSION['user_id']);
        $csrf_token = Auth::generateCSRF();
        view('user/edit_profile', ['user' => $user, 'csrf_token' => $csrf_token]);
    }
    
    // Change password
    function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $userId = $_SESSION['user_id'];
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Verify current password
            $user = User::findById($userId);
            if (!password_verify($currentPassword, $user->password)) {
                $_SESSION['error'] = 'Current password is incorrect';
                redirect('/user/change-password');
                exit;
            }
            
            // Validate new password
            if (strlen($newPassword) < 6) {
                $_SESSION['error'] = 'New password must be at least 6 characters';
                redirect('/user/change-password');
                exit;
            }
            
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'New passwords do not match';
                redirect('/user/change-password');
                exit;
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $st = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
            $st->execute([$hashedPassword, $userId]);
            
            $_SESSION['success'] = 'Password changed successfully';
            Auth::logAction('password_changed');
            
            redirect('/user/profile');
            exit;
        }
        
        $csrf_token = Auth::generateCSRF();
        view('user/change_password', ['csrf_token' => $csrf_token]);
    }
    
    // Settings page
    function settings() {
        $user = User::findById($_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $userId = $_SESSION['user_id'];
            $data = [];
            
            // Update notification preferences
            $notifications = [
                'email_matches' => isset($_POST['email_matches']) ? 1 : 0,
                'email_tournaments' => isset($_POST['email_tournaments']) ? 1 : 0,
                'email_friends' => isset($_POST['email_friends']) ? 1 : 0,
                'browser_notifications' => isset($_POST['browser_notifications']) ? 1 : 0
            ];
            
            // Save notification preferences (you might want to create a separate table for this)
            foreach ($notifications as $key => $value) {
                $st = db()->prepare("
                    INSERT INTO user_settings (user_id, setting_key, setting_value) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $st->execute([$userId, $key, $value, $value]);
            }
            
            // Update privacy settings
            $privacy = [
                'profile_visibility' => $_POST['profile_visibility'] ?? 'public',
                'show_online_status' => isset($_POST['show_online_status']) ? 1 : 0,
                'allow_friend_requests' => isset($_POST['allow_friend_requests']) ? 1 : 0
            ];
            
            foreach ($privacy as $key => $value) {
                $st = db()->prepare("
                    INSERT INTO user_settings (user_id, setting_key, setting_value) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $st->execute([$userId, $key, $value, $value]);
            }
            
            $_SESSION['success'] = 'Settings updated successfully';
            Auth::logAction('settings_updated');
            
            redirect('/user/settings');
            exit;
        }
        
        // Get current settings
        $settings = [];
        $st = db()->prepare("SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?");
        $st->execute([$_SESSION['user_id']]);
        foreach ($st->fetchAll() as $setting) {
            $settings[$setting->setting_key] = $setting->setting_value;
        }
        
        $csrf_token = Auth::generateCSRF();
        view('user/settings', ['user' => $user, 'settings' => $settings, 'csrf_token' => $csrf_token]);
    }
    
    // Match history
    function matchHistory() {
        $userId = $_SESSION['user_id'];
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $matches = db()->prepare("
            SELECT 
                m.*, 
                q.title as quiz_title,
                mp.score,
                mp.result,
                mp.finished_at,
                (SELECT COUNT(*) FROM match_players WHERE match_id = m.id) as total_players
            FROM matches m
            JOIN match_players mp ON m.id = mp.match_id
            JOIN quizzes q ON m.quiz_id = q.id
            WHERE mp.user_id = ? AND m.status = 'completed'
            ORDER BY mp.finished_at DESC
            LIMIT ? OFFSET ?
        ");
        $matches->execute([$userId, $limit, $offset]);
        
        $totalMatches = db()->prepare("
            SELECT COUNT(*) 
            FROM matches m
            JOIN match_players mp ON m.id = mp.match_id
            WHERE mp.user_id = ? AND m.status = 'completed'
        ");
        $totalMatches->execute([$userId]);
        $totalCount = $totalMatches->fetchColumn();
        
        view('user/match_history', [
            'matches' => $matches->fetchAll(),
            'total_pages' => ceil($totalCount / $limit),
            'current_page' => $page
        ]);
    }
    
    // Leaderboard
    function leaderboard() {
        $type = $_GET['type'] ?? 'overall';
        $page = $_GET['page'] ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        switch ($type) {
            case 'weekly':
                $dateCondition = "WHERE m.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'monthly':
                $dateCondition = "WHERE m.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            default:
                $dateCondition = "";
                break;
        }
        
        $leaderboard = db()->query("
            SELECT 
                u.id,
                u.name,
                u.avatar,
                SUM(mp.score) as total_score,
                COUNT(mp.id) as matches_played,
                AVG(mp.score) as avg_score,
                SUM(CASE WHEN mp.result = 'win' THEN 1 ELSE 0 END) as wins
            FROM users u
            JOIN match_players mp ON u.id = mp.user_id
            JOIN matches m ON mp.match_id = m.id
            {$dateCondition}
            GROUP BY u.id
            ORDER BY total_score DESC
            LIMIT ? OFFSET ?
        ")->fetchAll();
        
        // Get current user's rank
        $userRank = null;
        if ($type === 'overall') {
            $userRank = db()->prepare("
                SELECT rank FROM (
                    SELECT 
                        u.id,
                        ROW_NUMBER() OVER (ORDER BY SUM(mp.score) DESC) as rank
                    FROM users u
                    JOIN match_players mp ON u.id = mp.user_id
                    GROUP BY u.id
                ) ranked
                WHERE id = ?
            ");
            $userRank->execute([$_SESSION['user_id']]);
            $userRank = $userRank->fetch();
        }
        
        view('user/leaderboard', [
            'leaderboard' => $leaderboard,
            'type' => $type,
            'user_rank' => $userRank,
            'total_pages' => ceil(count($leaderboard) / $limit),
            'current_page' => $page
        ]);
    }
    
    // Achievements
    function achievements() {
        $userId = $_SESSION['user_id'];
        
        // Get earned achievements
        $earned = db()->prepare("
            SELECT ma.*, uma.earned_at
            FROM multiplayer_achievements ma
            JOIN user_multiplayer_achievements uma ON ma.id = uma.achievement_id
            WHERE uma.user_id = ?
            ORDER BY uma.earned_at DESC
        ");
        $earned->execute([$userId]);
        
        // Get available achievements
        $available = db()->prepare("
            SELECT ma.*
            FROM multiplayer_achievements ma
            WHERE ma.id NOT IN (
                SELECT achievement_id 
                FROM user_multiplayer_achievements 
                WHERE user_id = ?
            )
            ORDER BY ma.threshold ASC
        ");
        $available->execute([$userId]);
        
        view('user/achievements', [
            'earned' => $earned->fetchAll(),
            'available' => $available->fetchAll()
        ]);
    }
    
    // Statistics
    function statistics() {
        $userId = $_SESSION['user_id'];
        
        $stats = [
            'overall' => User::getStats($userId),
            'by_quiz' => $this->getQuizStats($userId),
            'by_month' => $this->getMonthlyStats($userId),
            'win_rate' => $this->getWinRate($userId),
            'best_performance' => $this->getBestPerformance($userId)
        ];
        
        view('user/statistics', ['stats' => $stats]);
    }
    
    // Helper methods
    private function getQuizStats($userId) {
        return db()->prepare("
            SELECT 
                q.title as quiz_name,
                COUNT(mp.id) as times_played,
                AVG(mp.score) as avg_score,
                MAX(mp.score) as best_score,
                SUM(CASE WHEN mp.result = 'win' THEN 1 ELSE 0 END) as wins
            FROM match_players mp
            JOIN matches m ON mp.match_id = m.id
            JOIN quizzes q ON m.quiz_id = q.id
            WHERE mp.user_id = ?
            GROUP BY q.id
            ORDER BY times_played DESC
            LIMIT 10
        ")->execute([$userId])->fetchAll();
    }
    
    private function getMonthlyStats($userId) {
        return db()->prepare("
            SELECT 
                DATE_FORMAT(m.created_at, '%Y-%m') as month,
                COUNT(mp.id) as matches_played,
                AVG(mp.score) as avg_score,
                SUM(mp.score) as total_score,
                SUM(CASE WHEN mp.result = 'win' THEN 1 ELSE 0 END) as wins
            FROM match_players mp
            JOIN matches m ON mp.match_id = m.id
            WHERE mp.user_id = ? AND m.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(m.created_at, '%Y-%m')
            ORDER BY month DESC
        ")->execute([$userId])->fetchAll();
    }
    
    private function getWinRate($userId) {
        $result = db()->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins
            FROM match_players
            WHERE user_id = ?
        ")->execute([$userId])->fetch();
        
        return $result ? ($result->total > 0 ? round(($result->wins / $result->total) * 100, 2) : 0) : 0;
    }
    
    private function getBestPerformance($userId) {
        return db()->prepare("
            SELECT 
                m.*,
                q.title as quiz_title,
                mp.score,
                mp.correct_answers,
                mp.total_answers
            FROM match_players mp
            JOIN matches m ON mp.match_id = m.id
            JOIN quizzes q ON m.quiz_id = q.id
            WHERE mp.user_id = ?
            ORDER BY mp.score DESC
            LIMIT 1
        ")->execute([$userId])->fetch();
    }
}
