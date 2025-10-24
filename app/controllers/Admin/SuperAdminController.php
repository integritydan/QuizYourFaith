<?php namespace App\Controllers\Admin;
use App\Models\User;
use App\Middleware\Auth;

class SuperAdminController {
    
    function __construct() {
        Auth::superAdminMiddleware();
    }
    
    // Dashboard for super admin
    function dashboard() {
        $stats = [
            'total_users' => db()->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'online_users' => db()->query("SELECT COUNT(*) FROM users WHERE online_status = 'online'")->fetchColumn(),
            'banned_users' => db()->query("SELECT COUNT(*) FROM users WHERE is_banned = TRUE")->fetchColumn(),
            'total_matches' => db()->query("SELECT COUNT(*) FROM matches")->fetchColumn(),
            'active_matches' => db()->query("SELECT COUNT(*) FROM matches WHERE status = 'active'")->fetchColumn(),
            'total_tournaments' => db()->query("SELECT COUNT(*) FROM tournaments")->fetchColumn(),
            'active_tournaments' => db()->query("SELECT COUNT(*) FROM tournaments WHERE status = 'active'")->fetchColumn(),
            'total_reports' => db()->query("SELECT COUNT(*) FROM match_reports WHERE status = 'pending'")->fetchColumn(),
            'system_load' => $this->getSystemLoad(),
            'recent_logs' => $this->getRecentLogs(10)
        ];
        
        view('admin/super/dashboard', ['stats' => $stats]);
    }
    
    // User management
    function users() {
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = [];
        $params = [];
        
        if ($search) {
            $where[] = "(name LIKE ? OR email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($role) {
            $where[] = "role = ?";
            $params[] = $role;
        }
        
        if ($status === 'banned') {
            $where[] = "is_banned = TRUE";
        } elseif ($status === 'online') {
            $where[] = "online_status = 'online'";
        }
        
        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
        
        $users = db()->prepare("
            SELECT * FROM users 
            {$whereClause}
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $users->execute($params);
        
        $totalUsers = db()->prepare("SELECT COUNT(*) FROM users {$whereClause}");
        $totalUsers->execute(array_slice($params, 0, -2));
        $totalCount = $totalUsers->fetchColumn();
        
        view('admin/super/users', [
            'users' => $users->fetchAll(),
            'total_pages' => ceil($totalCount / $limit),
            'current_page' => $page,
            'search' => $search,
            'role' => $role,
            'status' => $status
        ]);
    }
    
    // Edit user
    function editUser($userId) {
        $user = User::findById($userId);
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            redirect('/admin/super/users');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $data = [];
            
            if (isset($_POST['name'])) {
                $data['name'] = $_POST['name'];
            }
            
            if (isset($_POST['email'])) {
                $data['email'] = $_POST['email'];
            }
            
            if (isset($_POST['role']) && in_array($_POST['role'], ['user', 'admin', 'super_admin'])) {
                $data['role'] = $_POST['role'];
            }
            
            if (isset($_POST['max_friends'])) {
                $data['max_friends'] = max(10, min(200, (int)$_POST['max_friends']));
            }
            
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            // Build update query
            $setParts = [];
            $params = [];
            
            foreach ($data as $field => $value) {
                $setParts[] = "{$field} = ?";
                $params[] = $value;
            }
            
            if (!empty($setParts)) {
                $params[] = $userId;
                $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
                $st = db()->prepare($sql);
                $st->execute($params);
                
                $_SESSION['success'] = 'User updated successfully';
                Auth::logAction('user_updated', ['user_id' => $userId, 'fields' => array_keys($data)]);
            }
            
            redirect('/admin/super/users');
            exit;
        }
        
        $csrf_token = Auth::generateCSRF();
        view('admin/super/edit_user', ['user' => $user, 'csrf_token' => $csrf_token]);
    }
    
    // Ban/Unban user
    function banUser($userId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $reason = $_POST['reason'] ?? '';
            $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : null;
            
            User::banUser($userId, $reason, $duration);
            
            $_SESSION['success'] = 'User banned successfully';
            Auth::logAction('user_banned', ['user_id' => $userId, 'reason' => $reason, 'duration' => $duration]);
            
            redirect('/admin/super/users');
            exit;
        }
    }
    
    function unbanUser($userId) {
        User::unbanUser($userId);
        
        $_SESSION['success'] = 'User unbanned successfully';
        Auth::logAction('user_unbanned', ['user_id' => $userId]);
        
        redirect('/admin/super/users');
    }
    
    // System settings
    function settings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $settings = [
                'site_title' => $_POST['site_title'] ?? 'QuizYourFaith',
                'max_friends_per_user' => max(10, min(200, (int)($_POST['max_friends_per_user'] ?? 50))),
                'max_match_players' => max(2, min(16, (int)($_POST['max_match_players'] ?? 8))),
                'match_timeout_minutes' => max(5, min(120, (int)($_POST['match_timeout_minutes'] ?? 30))),
                'chat_enabled' => isset($_POST['chat_enabled']) ? 'true' : 'false',
                'tournament_entry_fee' => max(0, (float)($_POST['tournament_entry_fee'] ?? 0)),
                'anti_cheat_enabled' => isset($_POST['anti_cheat_enabled']) ? 'true' : 'false',
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? 'true' : 'false'
            ];
            
            foreach ($settings as $key => $value) {
                $st = db()->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
                $st->execute([$key, $value, $value]);
            }
            
            $_SESSION['success'] = 'Settings updated successfully';
            Auth::logAction('system_settings_updated', $settings);
        }
        
        // Get current settings
        $settings = [];
        $result = db()->query("SELECT * FROM settings")->fetchAll();
        foreach ($result as $setting) {
            $settings[$setting->key] = $setting->value;
        }
        
        $csrf_token = Auth::generateCSRF();
        view('admin/super/settings', ['settings' => $settings, 'csrf_token' => $csrf_token]);
    }
    
    // Multiplayer server management
    function multiplayerServers() {
        // Get server status (this would integrate with your WebSocket server)
        $servers = [
            [
                'id' => 1,
                'name' => 'Main Server',
                'status' => 'online',
                'players_online' => db()->query("SELECT COUNT(*) FROM users WHERE online_status = 'online'")->fetchColumn(),
                'active_matches' => db()->query("SELECT COUNT(*) FROM matches WHERE status = 'active'")->fetchColumn(),
                'uptime' => '24h 30m',
                'load' => '45%'
            ]
        ];
        
        view('admin/super/multiplayer_servers', ['servers' => $servers]);
    }
    
    // Match reports management
    function matchReports() {
        $status = $_GET['status'] ?? 'pending';
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $reports = db()->prepare("
            SELECT 
                mr.*,
                reporter.name as reporter_name,
                reported.name as reported_name,
                m.title as match_title
            FROM match_reports mr
            JOIN users reporter ON mr.reporter_id = reporter.id
            JOIN users reported ON mr.reported_user_id = reported.id
            JOIN matches m ON mr.match_id = m.id
            WHERE mr.status = ?
            ORDER BY mr.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $reports->execute([$status, $limit, $offset]);
        
        $totalReports = db()->prepare("SELECT COUNT(*) FROM match_reports WHERE status = ?");
        $totalReports->execute([$status]);
        $totalCount = $totalReports->fetchColumn();
        
        view('admin/super/match_reports', [
            'reports' => $reports->fetchAll(),
            'status' => $status,
            'total_pages' => ceil($totalCount / $limit),
            'current_page' => $page
        ]);
    }
    
    // Handle match report
    function handleReport($reportId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $action = $_POST['action'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            $st = db()->prepare("
                UPDATE match_reports 
                SET status = ?, reviewed_by = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            
            switch ($action) {
                case 'resolve':
                    $status = 'resolved';
                    break;
                case 'dismiss':
                    $status = 'dismissed';
                    break;
                default:
                    $status = 'reviewed';
                    break;
            }
            
            $st->execute([$status, $_SESSION['user_id'], $reportId]);
            
            $_SESSION['success'] = 'Report handled successfully';
            Auth::logAction('report_handled', ['report_id' => $reportId, 'action' => $action]);
            
            redirect('/admin/super/match-reports');
            exit;
        }
    }
    
    // Tournament management
    function tournaments() {
        $tournaments = db()->query("
            SELECT t.*, u.name as creator_name 
            FROM tournaments t
            JOIN users u ON t.created_by = u.id
            ORDER BY t.created_at DESC
        ")->fetchAll();
        
        view('admin/super/tournaments', ['tournaments' => $tournaments]);
    }
    
    // Create tournament
    function createTournament() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $st = db()->prepare("
                INSERT INTO tournaments 
                (name, description, created_by, max_participants, start_time, end_time, prize_pool, entry_fee, rules)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $st->execute([
                $_POST['name'],
                $_POST['description'],
                $_SESSION['user_id'],
                $_POST['max_participants'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['prize_pool'],
                $_POST['entry_fee'],
                json_encode($_POST['rules'] ?? [])
            ]);
            
            $_SESSION['success'] = 'Tournament created successfully';
            Auth::logAction('tournament_created', ['name' => $_POST['name']]);
            
            redirect('/admin/super/tournaments');
            exit;
        }
        
        $csrf_token = Auth::generateCSRF();
        view('admin/super/create_tournament', ['csrf_token' => $csrf_token]);
    }
    
    // System logs
    function systemLogs() {
        $page = $_GET['page'] ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $logs = db()->prepare("
            SELECT l.*, u.name as user_name 
            FROM logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $logs->execute([$limit, $offset]);
        
        $totalLogs = db()->query("SELECT COUNT(*) FROM logs")->fetchColumn();
        
        view('admin/super/system_logs', [
            'logs' => $logs->fetchAll(),
            'total_pages' => ceil($totalLogs / $limit),
            'current_page' => $page
        ]);
    }
    
    // Analytics dashboard
    function analytics() {
        $analytics = [
            'user_growth' => $this->getUserGrowth(),
            'match_activity' => $this->getMatchActivity(),
            'popular_quizzes' => $this->getPopularQuizzes(),
            'revenue_stats' => $this->getRevenueStats(),
            'system_performance' => $this->getSystemPerformance()
        ];
        
        view('admin/super/analytics', ['analytics' => $analytics]);
    }
    
    // Helper methods
    private function getSystemLoad() {
        // This would integrate with your server monitoring
        return [
            'cpu' => rand(20, 80),
            'memory' => rand(40, 90),
            'disk' => rand(30, 70)
        ];
    }
    
    private function getRecentLogs($limit = 10) {
        $st = db()->prepare("
            SELECT l.*, u.name as user_name 
            FROM logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT ?
        ");
        $st->execute([$limit]);
        return $st->fetchAll();
    }
    
    private function getUserGrowth() {
        $st = db()->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM users 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $st->execute();
        return $st->fetchAll();
    }
    
    private function getMatchActivity() {
        $st = db()->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM matches 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $st->execute();
        return $st->fetchAll();
    }
    
    private function getPopularQuizzes() {
        return db()->query("
            SELECT q.*, c.name as category_name, COUNT(mp.id) as play_count
            FROM quizzes q
            JOIN categories c ON q.category_id = c.id
            LEFT JOIN match_players mp ON q.id = mp.match_id
            GROUP BY q.id
            ORDER BY play_count DESC
            LIMIT 10
        ")->fetchAll();
    }
    
    private function getRevenueStats() {
        return [
            'total_donations' => db()->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success'")->fetchColumn(),
            'monthly_donations' => db()->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")->fetchColumn(),
            'tournament_revenue' => db()->query("SELECT COALESCE(SUM(entry_fee * current_participants), 0) FROM tournaments WHERE status = 'completed'")->fetchColumn()
        ];
    }
    
    private function getSystemPerformance() {
        return [
            'avg_response_time' => rand(100, 500),
            'uptime_percentage' => rand(95, 100),
            'error_rate' => rand(0, 5)
        ];
    }
}