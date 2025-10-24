<?php namespace App\Controllers\Admin;
use App\Models\User;
use App\Middleware\Auth;

class GameAdminController {
    
    function __construct() {
        Auth::adminMiddleware();
    }
    
    // Dashboard for game admins
    function dashboard() {
        $stats = [
            'active_matches' => db()->query("SELECT COUNT(*) FROM matches WHERE status = 'active'")->fetchColumn(),
            'reported_matches' => db()->query("SELECT COUNT(*) FROM match_reports WHERE status = 'pending'")->fetchColumn(),
            'online_players' => db()->query("SELECT COUNT(*) FROM users WHERE online_status = 'online'")->fetchColumn(),
            'tournaments_today' => db()->query("SELECT COUNT(*) FROM tournaments WHERE DATE(start_time) = CURDATE()")->fetchColumn(),
            'recent_reports' => $this->getRecentReports(5),
            'active_tournaments' => $this->getActiveTournaments()
        ];
        
        view('admin/game/dashboard', ['stats' => $stats]);
    }
    
    // Active matches monitoring
    function activeMatches() {
        $matches = db()->query("
            SELECT 
                m.*, 
                q.title as quiz_title,
                COUNT(mp.user_id) as current_players,
                u.name as creator_name
            FROM matches m
            JOIN quizzes q ON m.quiz_id = q.id
            JOIN users u ON m.created_by = u.id
            LEFT JOIN match_players mp ON m.id = mp.match_id
            WHERE m.status = 'active'
            GROUP BY m.id
            ORDER BY m.start_time DESC
        ")->fetchAll();
        
        view('admin/game/active_matches', ['matches' => $matches]);
    }
    
    // Match details and moderation
    function matchDetails($matchId) {
        $match = db()->prepare("
            SELECT 
                m.*, 
                q.title as quiz_title,
                u.name as creator_name
            FROM matches m
            JOIN quizzes q ON m.quiz_id = q.id
            JOIN users u ON m.created_by = u.id
            WHERE m.id = ?
        ");
        $match->execute([$matchId]);
        $match = $match->fetch();
        
        if (!$match) {
            $_SESSION['error'] = 'Match not found';
            redirect('/admin/game/active-matches');
            exit;
        }
        
        $players = db()->prepare("
            SELECT 
                mp.*,
                u.name as player_name,
                u.online_status
            FROM match_players mp
            JOIN users u ON mp.user_id = u.id
            WHERE mp.match_id = ?
            ORDER BY mp.score DESC
        ");
        $players->execute([$matchId]);
        
        $chatMessages = db()->prepare("
            SELECT 
                cm.*,
                u.name as user_name
            FROM chat_messages cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.match_id = ?
            ORDER BY cm.created_at DESC
            LIMIT 50
        ");
        $chatMessages->execute([$matchId]);
        
        view('admin/game/match_details', [
            'match' => $match,
            'players' => $players->fetchAll(),
            'chat_messages' => $chatMessages->fetchAll()
        ]);
    }
    
    // Kick player from match
    function kickPlayer($matchId, $userId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $reason = $_POST['reason'] ?? 'Violation of game rules';
            
            // Remove player from match
            $st = db()->prepare("DELETE FROM match_players WHERE match_id = ? AND user_id = ?");
            $st->execute([$matchId, $userId]);
            
            // Add system message to chat
            $st = db()->prepare("
                INSERT INTO chat_messages (match_id, user_id, message, message_type, created_at)
                VALUES (?, ?, ?, 'system', NOW())
            ");
            $userName = User::findById($userId)->name;
            $message = "Player {$userName} has been removed from the match by moderator. Reason: {$reason}";
            $st->execute([$matchId, $_SESSION['user_id'], $message]);
            
            // Log the action
            Auth::logAction('player_kicked', [
                'match_id' => $matchId,
                'user_id' => $userId,
                'reason' => $reason
            ]);
            
            $_SESSION['success'] = 'Player kicked successfully';
            redirect("/admin/game/match/{$matchId}");
            exit;
        }
    }
    
    // End match forcibly
    function endMatch($matchId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $reason = $_POST['reason'] ?? 'Match ended by moderator';
            
            // Update match status
            $st = db()->prepare("
                UPDATE matches 
                SET status = 'completed', end_time = NOW() 
                WHERE id = ?
            ");
            $st->execute([$matchId]);
            
            // Add system message
            $st = db()->prepare("
                INSERT INTO chat_messages (match_id, user_id, message, message_type, created_at)
                VALUES (?, ?, ?, 'system', NOW())
            ");
            $st->execute([$matchId, $_SESSION['user_id'], "Match ended by moderator. Reason: {$reason}"]);
            
            // Log the action
            Auth::logAction('match_ended', [
                'match_id' => $matchId,
                'reason' => $reason
            ]);
            
            $_SESSION['success'] = 'Match ended successfully';
            redirect('/admin/game/active-matches');
            exit;
        }
    }
    
    // Match reports handling
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
        
        view('admin/game/match_reports', [
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
                case 'warn':
                    $status = 'resolved';
                    $this->warnUser($reportId, $notes);
                    break;
                case 'ban_temp':
                    $status = 'resolved';
                    $this->tempBanUser($reportId, $notes);
                    break;
                case 'ban_perm':
                    $status = 'resolved';
                    $this->permBanUser($reportId, $notes);
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
            
            redirect('/admin/game/match-reports');
            exit;
        }
    }
    
    // Tournament management
    function tournaments() {
        $status = $_GET['status'] ?? 'all';
        
        $where = $status !== 'all' ? "WHERE t.status = '{$status}'" : "";
        
        $tournaments = db()->query("
            SELECT 
                t.*,
                u.name as creator_name,
                COUNT(tp.id) as participant_count
            FROM tournaments t
            JOIN users u ON t.created_by = u.id
            LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id
            {$where}
            GROUP BY t.id
            ORDER BY t.start_time DESC
        ")->fetchAll();
        
        view('admin/game/tournaments', ['tournaments' => $tournaments, 'status' => $status]);
    }
    
    // Tournament details
    function tournamentDetails($tournamentId) {
        $tournament = db()->prepare("
            SELECT 
                t.*,
                u.name as creator_name
            FROM tournaments t
            JOIN users u ON t.created_by = u.id
            WHERE t.id = ?
        ");
        $tournament->execute([$tournamentId]);
        $tournament = $tournament->fetch();
        
        if (!$tournament) {
            $_SESSION['error'] = 'Tournament not found';
            redirect('/admin/game/tournaments');
            exit;
        }
        
        $participants = db()->prepare("
            SELECT 
                tp.*,
                u.name as participant_name
            FROM tournament_participants tp
            JOIN users u ON tp.user_id = u.id
            WHERE tp.tournament_id = ?
            ORDER BY tp.position, tp.score DESC
        ");
        $participants->execute([$tournamentId]);
        
        view('admin/game/tournament_details', [
            'tournament' => $tournament,
            'participants' => $participants->fetchAll()
        ]);
    }
    
    // Chat moderation
    function chatModeration() {
        $messages = db()->query("
            SELECT 
                cm.*,
                u.name as user_name,
                m.title as match_title
            FROM chat_messages cm
            JOIN users u ON cm.user_id = u.id
            JOIN matches m ON cm.match_id = m.id
            WHERE cm.is_moderated = FALSE
            ORDER BY cm.created_at DESC
            LIMIT 100
        ")->fetchAll();
        
        view('admin/game/chat_moderation', ['messages' => $messages]);
    }
    
    // Moderate chat message
    function moderateMessage($messageId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $action = $_POST['action'] ?? '';
            
            $st = db()->prepare("
                UPDATE chat_messages 
                SET is_moderated = TRUE 
                WHERE id = ?
            ");
            $st->execute([$messageId]);
            
            if ($action === 'delete') {
                // Additional action: warn user or ban if repeated offenses
                $message = db()->prepare("SELECT user_id, match_id FROM chat_messages WHERE id = ?");
                $message->execute([$messageId]);
                $msg = $message->fetch();
                
                if ($msg) {
                    Auth::logAction('chat_message_deleted', [
                        'message_id' => $messageId,
                        'user_id' => $msg->user_id,
                        'match_id' => $msg->match_id
                    ]);
                }
            }
            
            $_SESSION['success'] = 'Message moderated successfully';
            redirect('/admin/game/chat-moderation');
            exit;
        }
    }
    
    // Send system announcement
    function sendAnnouncement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $message = $_POST['message'] ?? '';
            $target = $_POST['target'] ?? 'all';
            
            if (empty($message)) {
                $_SESSION['error'] = 'Message cannot be empty';
                redirect('/admin/game/send-announcement');
                exit;
            }
            
            // Log the announcement
            Auth::logAction('system_announcement', [
                'message' => $message,
                'target' => $target
            ]);
            
            $_SESSION['success'] = 'Announcement sent successfully';
            redirect('/admin/game/dashboard');
            exit;
        }
        
        $csrf_token = Auth::generateCSRF();
        view('admin/game/send_announcement', ['csrf_token' => $csrf_token]);
    }
    
    // Helper methods
    private function getRecentReports($limit = 5) {
        $st = db()->prepare("
            SELECT 
                mr.*,
                reporter.name as reporter_name,
                reported.name as reported_name
            FROM match_reports mr
            JOIN users reporter ON mr.reporter_id = reporter.id
            JOIN users reported ON mr.reported_user_id = reported.id
            WHERE mr.status = 'pending'
            ORDER BY mr.created_at DESC
            LIMIT ?
        ");
        $st->execute([$limit]);
        return $st->fetchAll();
    }
    
    private function getActiveTournaments() {
        return db()->query("
            SELECT 
                t.*,
                COUNT(tp.id) as participant_count
            FROM tournaments t
            LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id
            WHERE t.status = 'active'
            GROUP BY t.id
            ORDER BY t.start_time DESC
            LIMIT 5
        ")->fetchAll();
    }
    
    private function warnUser($reportId, $notes) {
        // Get reported user ID
        $st = db()->prepare("SELECT reported_user_id FROM match_reports WHERE id = ?");
        $st->execute([$reportId]);
        $report = $st->fetch();
        
        if ($report) {
            // Log warning
            Auth::logAction('user_warned', [
                'user_id' => $report->reported_user_id,
                'report_id' => $reportId,
                'notes' => $notes
            ]);
        }
    }
    
    private function tempBanUser($reportId, $notes) {
        $st = db()->prepare("SELECT reported_user_id FROM match_reports WHERE id = ?");
        $st->execute([$reportId]);
        $report = $st->fetch();
        
        if ($report) {
            User::banUser($report->reported_user_id, $notes, 7); // 7 days ban
            
            Auth::logAction('user_temp_banned', [
                'user_id' => $report->reported_user_id,
                'report_id' => $reportId,
                'duration' => 7,
                'notes' => $notes
            ]);
        }
    }
    
    private function permBanUser($reportId, $notes) {
        $st = db()->prepare("SELECT reported_user_id FROM match_reports WHERE id = ?");
        $st->execute([$reportId]);
        $report = $st->fetch();
        
        if ($report) {
            User::banUser($report->reported_user_id, $notes);
            
            Auth::logAction('user_perm_banned', [
                'user_id' => $report->reported_user_id,
                'report_id' => $reportId,
                'notes' => $notes
            ]);
        }
    }
}