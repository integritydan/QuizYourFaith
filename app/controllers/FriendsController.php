<?php namespace App\Controllers;
use App\Models\User;
use App\Middleware\Auth;

class FriendsController {
    
    function __construct() {
        Auth::userMiddleware();
    }
    
    // Friends list
    function index() {
        $userId = $_SESSION['user_id'];
        
        // Get friends
        $friends = User::getFriends($userId, 'accepted');
        
        // Get pending requests (sent by user)
        $sentRequests = db()->prepare("
            SELECT u.*, f.created_at as request_sent
            FROM friends f
            JOIN users u ON f.friend_id = u.id
            WHERE f.user_id = ? AND f.status = 'pending'
            ORDER BY f.created_at DESC
        ");
        $sentRequests->execute([$userId]);
        
        // Get pending requests (received by user)
        $receivedRequests = db()->prepare("
            SELECT u.*, f.created_at as request_sent
            FROM friends f
            JOIN users u ON f.user_id = u.id
            WHERE f.friend_id = ? AND f.status = 'pending'
            ORDER BY f.created_at DESC
        ");
        $receivedRequests->execute([$userId]);
        
        // Get blocked friends
        $blocked = db()->prepare("
            SELECT u.*, f.created_at as blocked_at
            FROM friends f
            JOIN users u ON (f.user_id = u.id OR f.friend_id = u.id)
            WHERE (f.user_id = ? OR f.friend_id = ?) 
            AND u.id != ?
            AND f.status = 'blocked'
            ORDER BY f.updated_at DESC
        ");
        $blocked->execute([$userId, $userId, $userId]);
        
        view('friends/index', [
            'friends' => $friends,
            'sent_requests' => $sentRequests->fetchAll(),
            'received_requests' => $receivedRequests->fetchAll(),
            'blocked' => $blocked->fetchAll()
        ]);
    }
    
    // Search users to add as friends
    function search() {
        $query = $_GET['q'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (strlen($query) < 2) {
            echo json_encode(['users' => []]);
            exit;
        }
        
        // Search users
        $users = User::searchUsers($query, $userId);
        
        // Check friendship status for each user
        foreach ($users as &$user) {
            $st = db()->prepare("
                SELECT status, 
                       CASE 
                           WHEN user_id = ? THEN 'sent'
                           WHEN friend_id = ? THEN 'received'
                       END as direction
                FROM friends
                WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
            ");
            $st->execute([$userId, $userId, $userId, $user->id, $user->id, $userId]);
            $friendship = $st->fetch();
            
            $user->friendship_status = $friendship ? $friendship->status : 'none';
            $user->request_direction = $friendship ? $friendship->direction : null;
        }
        
        echo json_encode(['users' => $users]);
    }
    
    // Send friend request
    function sendRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/friends');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        $friendId = $_POST['friend_id'] ?? 0;
        
        if ($friendId <= 0 || $friendId == $userId) {
            $_SESSION['error'] = 'Invalid user';
            redirect('/friends');
            exit;
        }
        
        // Check if user exists
        $friend = User::findById($friendId);
        if (!$friend) {
            $_SESSION['error'] = 'User not found';
            redirect('/friends');
            exit;
        }
        
        // Check if already friends or request exists
        $existing = db()->prepare("
            SELECT status FROM friends 
            WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
        ");
        $existing->execute([$userId, $friendId, $friendId, $userId]);
        $existing = $existing->fetch();
        
        if ($existing) {
            switch ($existing->status) {
                case 'accepted':
                    $_SESSION['info'] = 'You are already friends';
                    break;
                case 'pending':
                    $_SESSION['info'] = 'Friend request already sent';
                    break;
                case 'blocked':
                    $_SESSION['error'] = 'Cannot send friend request to this user';
                    break;
            }
            redirect('/friends');
            exit;
        }
        
        // Check friend limit
        $friendCount = db()->prepare("
            SELECT COUNT(*) 
            FROM friends 
            WHERE (user_id = ? OR friend_id = ?) AND status = 'accepted'
        ");
        $friendCount->execute([$userId, $userId]);
        $friendCount = $friendCount->fetchColumn();
        
        $maxFriends = User::findById($userId)->max_friends ?? 50;
        
        if ($friendCount >= $maxFriends) {
            $_SESSION['error'] = 'You have reached your friend limit';
            redirect('/friends');
            exit;
        }
        
        // Send friend request
        if (User::sendFriendRequest($userId, $friendId)) {
            $_SESSION['success'] = 'Friend request sent successfully';
            
            // Create notification
            $this->createNotification($friendId, 'friend_request', [
                'from_user_id' => $userId,
                'from_user_name' => $_SESSION['username']
            ]);
            
            Auth::logAction('friend_request_sent', ['to_user_id' => $friendId]);
        } else {
            $_SESSION['error'] = 'Failed to send friend request';
        }
        
        redirect('/friends');
    }
    
    // Accept friend request
    function acceptRequest($friendId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/friends');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        
        // Check if request exists
        $request = db()->prepare("
            SELECT id FROM friends 
            WHERE user_id = ? AND friend_id = ? AND status = 'pending'
        ");
        $request->execute([$friendId, $userId]);
        
        if (!$request->fetch()) {
            $_SESSION['error'] = 'Friend request not found';
            redirect('/friends');
            exit;
        }
        
        // Accept request
        if (User::acceptFriendRequest($userId, $friendId)) {
            $_SESSION['success'] = 'Friend request accepted';
            
            // Create notification
            $this->createNotification($friendId, 'friend_request_accepted', [
                'by_user_id' => $userId,
                'by_user_name' => $_SESSION['username']
            ]);
            
            Auth::logAction('friend_request_accepted', ['from_user_id' => $friendId]);
        } else {
            $_SESSION['error'] = 'Failed to accept friend request';
        }
        
        redirect('/friends');
    }
    
    // Decline friend request
    function declineRequest($friendId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/friends');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        
        // Check if request exists
        $request = db()->prepare("
            SELECT id FROM friends 
            WHERE user_id = ? AND friend_id = ? AND status = 'pending'
        ");
        $request->execute([$friendId, $userId]);
        
        if (!$request->fetch()) {
            $_SESSION['error'] = 'Friend request not found';
            redirect('/friends');
            exit;
        }
        
        // Decline request
        if (User::declineFriendRequest($userId, $friendId)) {
            $_SESSION['success'] = 'Friend request declined';
            Auth::logAction('friend_request_declined', ['from_user_id' => $friendId]);
        } else {
            $_SESSION['error'] = 'Failed to decline friend request';
        }
        
        redirect('/friends');
    }
    
    // Remove friend
    function removeFriend($friendId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/friends');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        
        // Remove friendship (both directions)
        $st = db()->prepare("
            DELETE FROM friends 
            WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
        ");
        $st->execute([$userId, $friendId, $friendId, $userId]);
        
        if ($st->rowCount() > 0) {
            $_SESSION['success'] = 'Friend removed successfully';
            Auth::logAction('friend_removed', ['friend_id' => $friendId]);
        } else {
            $_SESSION['error'] = 'Friend not found';
        }
        
        redirect('/friends');
    }
    
    // Block user
    function blockUser($userId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/friends');
            exit;
        }
        
        Auth::validateCSRF();
        
        $currentUserId = $_SESSION['user_id'];
        
        if ($userId == $currentUserId) {
            $_SESSION['error'] = 'Cannot block yourself';
            redirect('/friends');
            exit;
        }
        
        // Remove existing friendship
        $st = db()->prepare("
            DELETE FROM friends 
            WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
        ");
        $st->execute([$currentUserId, $userId, $userId, $currentUserId]);
        
        // Add block record
        $st = db()->prepare("
            INSERT INTO friends (user_id, friend_id, status, created_at)
            VALUES (?, ?, 'blocked', NOW())
        ");
        $st->execute([$currentUserId, $userId]);
        
        $_SESSION['success'] = 'User blocked successfully';
        Auth::logAction('user_blocked', ['blocked_user_id' => $userId]);
        
        redirect('/friends');
    }
    
    // Unblock user
    function unblockUser($userId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/friends');
            exit;
        }
        
        Auth::validateCSRF();
        
        $currentUserId = $_SESSION['user_id'];
        
        // Remove block
        $st = db()->prepare("
            DELETE FROM friends 
            WHERE user_id = ? AND friend_id = ? AND status = 'blocked'
        ");
        $st->execute([$currentUserId, $userId]);
        
        if ($st->rowCount() > 0) {
            $_SESSION['success'] = 'User unblocked successfully';
            Auth::logAction('user_unblocked', ['unblocked_user_id' => $userId]);
        } else {
            $_SESSION['error'] = 'Block not found';
        }
        
        redirect('/friends');
    }
    
    // Friend's profile
    function friendProfile($friendId) {
        $userId = $_SESSION['user_id'];
        
        // Check if they are friends
        $friendship = db()->prepare("
            SELECT status FROM friends 
            WHERE ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?))
            AND status = 'accepted'
        ");
        $friendship->execute([$userId, $friendId, $friendId, $userId]);
        
        if (!$friendship->fetch()) {
            $_SESSION['error'] = 'You can only view profiles of your friends';
            redirect('/friends');
            exit;
        }
        
        $friend = User::findById($friendId);
        if (!$friend) {
            $_SESSION['error'] = 'User not found';
            redirect('/friends');
            exit;
        }
        
        $stats = User::getStats($friendId);
        $recentMatches = User::getMatchHistory($friendId, 5);
        $achievements = db()->prepare("
            SELECT ma.*, uma.earned_at
            FROM multiplayer_achievements ma
            JOIN user_multiplayer_achievements uma ON ma.id = uma.achievement_id
            WHERE uma.user_id = ?
            ORDER BY uma.earned_at DESC
            LIMIT 10
        ");
        $achievements->execute([$friendId]);
        
        view('friends/friend_profile', [
            'friend' => $friend,
            'stats' => $stats,
            'recent_matches' => $recentMatches,
            'achievements' => $achievements->fetchAll()
        ]);
    }
    
    // Invite friend to match
    function inviteToMatch($friendId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/friends');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        $matchId = $_POST['match_id'] ?? 0;
        
        // Check if they are friends
        $friendship = db()->prepare("
            SELECT status FROM friends 
            WHERE ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?))
            AND status = 'accepted'
        ");
        $friendship->execute([$userId, $friendId, $friendId, $userId]);
        
        if (!$friendship->fetch()) {
            $_SESSION['error'] = 'You can only invite friends to matches';
            redirect('/friends');
            exit;
        }
        
        // Check if match exists and is joinable
        $match = db()->prepare("
            SELECT * FROM matches 
            WHERE id = ? AND status = 'waiting' AND current_players < max_players
        ");
        $match->execute([$matchId]);
        $match = $match->fetch();
        
        if (!$match) {
            $_SESSION['error'] = 'Match not found or is full';
            redirect('/friends');
            exit;
        }
        
        // Check if friend is already in match
        $existing = db()->prepare("
            SELECT id FROM match_players WHERE match_id = ? AND user_id = ?
        ");
        $existing->execute([$matchId, $friendId]);
        
        if ($existing->fetch()) {
            $_SESSION['info'] = 'Friend is already in this match';
            redirect('/friends');
            exit;
        }
        
        // Create invitation
        $st = db()->prepare("
            INSERT INTO invitations 
            (sender_id, receiver_id, match_id, invitation_type, status, created_at)
            VALUES (?, ?, ?, 'match', 'pending', NOW())
        ");
        $st->execute([$userId, $friendId, $matchId]);
        
        $_SESSION['success'] = 'Invitation sent successfully';
        Auth::logAction('match_invitation_sent', ['friend_id' => $friendId, 'match_id' => $matchId]);
        
        redirect('/friends');
    }
    
    // Helper methods
    private function createNotification($userId, $type, $data) {
        $st = db()->prepare("
            INSERT INTO notifications (user_id, type, data, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $st->execute([$userId, $type, json_encode($data)]);
    }
}