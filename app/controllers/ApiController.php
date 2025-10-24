<?php namespace App\Controllers;
use App\Models\User;
use App\Middleware\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController {
    
    private $jwtSecret;
    private $jwtAlgorithm = 'HS256';
    private $jwtExpiration = 3600; // 1 hour
    
    function __construct() {
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-default-secret-key-change-this';
    }
    
    // Generate JWT token
    function generateToken($userId, $userRole) {
        $payload = [
            'iss' => 'quizyourfaith',
            'aud' => 'quizyourfaith-users',
            'iat' => time(),
            'exp' => time() + $this->jwtExpiration,
            'userId' => $userId,
            'userRole' => $userRole
        ];
        
        return JWT::encode($payload, $this->jwtSecret, $this->jwtAlgorithm);
    }
    
    // Validate JWT token
    function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, $this->jwtAlgorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    // API endpoint to get JWT token
    function getToken() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'] ?? 'user';
        
        // Generate token
        $token = $this->generateToken($userId, $userRole);
        
        echo json_encode([
            'token' => $token,
            'expiresIn' => $this->jwtExpiration,
            'user' => [
                'id' => $userId,
                'name' => $_SESSION['username'] ?? '',
                'role' => $userRole
            ]
        ]);
    }
    
    // Update user online status
    function updateOnlineStatus() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        // Validate JWT token
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }
        
        $token = $matches[1];
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        
        $userId = $decoded['userId'];
        $status = $_POST['status'] ?? 'online';
        
        if (!in_array($status, ['online', 'offline', 'away', 'playing'])) {
            echo json_encode(['error' => 'Invalid status']);
            exit;
        }
        
        // Update status
        User::updateOnlineStatus($userId, $status);
        
        echo json_encode(['success' => true, 'status' => $status]);
    }
    
    // Get user stats
    function getUserStats() {
        header('Content-Type: application/json');
        
        // Validate JWT token
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }
        
        $token = $matches[1];
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        
        $userId = $decoded['userId'];
        $stats = User::getStats($userId);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    // Get match players
    function getMatchPlayers($matchId) {
        header('Content-Type: application/json');
        
        // Validate JWT token
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }
        
        $token = $matches[1];
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        
        $userId = $decoded['userId'];
        
        // Check if user is in the match
        $st = db()->prepare("
            SELECT id FROM match_players 
            WHERE match_id = ? AND user_id = ?
        ");
        $st->execute([$matchId, $userId]);
        
        if (!$st->fetch()) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }
        
        // Get match players
        $players = db()->prepare("
            SELECT 
                mp.*,
                u.name as player_name,
                u.avatar,
                u.online_status
            FROM match_players mp
            JOIN users u ON mp.user_id = u.id
            WHERE mp.match_id = ?
            ORDER BY mp.score DESC, mp.joined_at ASC
        ");
        $players->execute([$matchId]);
        
        echo json_encode([
            'success' => true,
            'players' => $players->fetchAll()
        ]);
    }
    
    // Get online friends
    function getOnlineFriends() {
        header('Content-Type: application/json');
        
        // Validate JWT token
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }
        
        $token = $matches[1];
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        
        $userId = $decoded['userId'];
        
        // Get online friends
        $friends = User::getFriends($userId, 'accepted');
        $onlineFriends = array_filter($friends, function($friend) {
            return $friend->online_status === 'online';
        });
        
        echo json_encode([
            'success' => true,
            'friends' => array_values($onlineFriends)
        ]);
    }
    
    // Get match invitations
    function getMatchInvitations() {
        header('Content-Type: application/json');
        
        // Validate JWT token
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }
        
        $token = $matches[1];
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        
        $userId = $decoded['userId'];
        
        // Get pending match invitations
        $invitations = db()->prepare("
            SELECT 
                i.*,
                u.name as sender_name,
                m.title as match_title,
                q.title as quiz_title
            FROM invitations i
            JOIN users u ON i.sender_id = u.id
            JOIN matches m ON i.match_id = m.id
            JOIN quizzes q ON m.quiz_id = q.id
            WHERE i.receiver_id = ? 
            AND i.invitation_type = 'match'
            AND i.status = 'pending'
            AND i.expires_at > NOW()
            ORDER BY i.created_at DESC
        ");
        $invitations->execute([$userId]);
        
        echo json_encode([
            'success' => true,
            'invitations' => $invitations->fetchAll()
        ]);
    }
    
    // Accept match invitation
    function acceptMatchInvitation($invitationId) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        // Validate JWT token
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }
        
        $token = $matches[1];
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        
        $userId = $decoded['userId'];
        
        // Get invitation
        $invitation = db()->prepare("
            SELECT * FROM invitations 
            WHERE id = ? AND receiver_id = ? AND status = 'pending'
        ");
        $invitation->execute([$invitationId, $userId]);
        $invitation = $invitation->fetch();
        
        if (!$invitation) {
            echo json_encode(['error' => 'Invitation not found']);
            exit;
        }
        
        // Check if match is still joinable
        $match = db()->prepare("
            SELECT * FROM matches 
            WHERE id = ? AND status = 'waiting' AND current_players < max_players
        ");
        $match->execute([$invitation->match_id]);
        $match = $match->fetch();
        
        if (!$match) {
            echo json_encode(['error' => 'Match is no longer available']);
            exit;
        }
        
        // Add user to match
        $st = db()->prepare("
            INSERT INTO match_players (match_id, user_id, joined_at) 
            VALUES (?, ?, NOW())
        ");
        $st->execute([$invitation->match_id, $userId]);
        
        // Update match player count
        $st = db()->prepare("UPDATE matches SET current_players = current_players + 1 WHERE id = ?");
        $st->execute([$invitation->match_id]);
        
        // Update invitation status
        $st = db()->prepare("UPDATE invitations SET status = 'accepted' WHERE id = ?");
        $st->execute([$invitationId]);
        
        echo json_encode([
            'success' => true,
            'match_id' => $invitation->match_id
        ]);
    }
    
    // Get tournament list
    function getTournaments() {
        header('Content-Type: application/json');
        
        $status = $_GET['status'] ?? 'upcoming';
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        $offset = ($page - 1) * $limit;
        
        $tournaments = db()->prepare("
            SELECT 
                t.*,
                u.name as creator_name,
                (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = t.id) as participant_count
            FROM tournaments t
            JOIN users u ON t.created_by = u.id
            WHERE t.status = ?
            ORDER BY t.start_time ASC
            LIMIT ? OFFSET ?
        ");
        $tournaments->execute([$status, $limit, $offset]);
        
        echo json_encode([
            'success' => true,
            'tournaments' => $tournaments->fetchAll()
        ]);
    }
    
    // Join tournament
    function joinTournament($tournamentId) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        // Validate JWT token
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }
        
        $token = $matches[1];
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        
        $userId = $decoded['userId'];
        
        // Check if tournament exists and is joinable
        $tournament = db()->prepare("
            SELECT * FROM tournaments 
            WHERE id = ? AND status = 'upcoming' 
            AND current_participants < max_participants
        ");
        $tournament->execute([$tournamentId]);
        $tournament = $tournament->fetch();
        
        if (!$tournament) {
            echo json_encode(['error' => 'Tournament not found or is full']);
            exit;
        }
        
        // Check if already joined
        $existing = db()->prepare("
            SELECT id FROM tournament_participants 
            WHERE tournament_id = ? AND user_id = ?
        ");
        $existing->execute([$tournamentId, $userId]);
        
        if ($existing->fetch()) {
            echo json_encode(['error' => 'Already joined this tournament']);
            exit;
        }
        
        // Add to tournament
        $st = db()->prepare("
            INSERT INTO tournament_participants (tournament_id, user_id, joined_at) 
            VALUES (?, ?, NOW())
        ");
        $st->execute([$tournamentId, $userId]);
        
        // Update participant count
        $st = db()->prepare("UPDATE tournaments SET current_participants = current_participants + 1 WHERE id = ?");
        $st->execute([$tournamentId]);
        
        echo json_encode([
            'success' => true,
            'tournament_id' => $tournamentId
        ]);
    }
    
    // Report player
    function reportPlayer() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        // Validate JWT token
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }
        
        $token = $matches[1];
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        
        $reporterId = $decoded['userId'];
        $reportedUserId = $_POST['reported_user_id'] ?? 0;
        $matchId = $_POST['match_id'] ?? 0;
        $reportType = $_POST['report_type'] ?? 'other';
        $description = $_POST['description'] ?? '';
        
        if ($reportedUserId <= 0 || $matchId <= 0 || empty($description)) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }
        
        // Create report
        $st = db()->prepare("
            INSERT INTO match_reports 
            (reporter_id, reported_user_id, match_id, report_type, description, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $st->execute([$reporterId, $reportedUserId, $matchId, $reportType, $description]);
        
        echo json_encode(['success' => true]);
    }
}