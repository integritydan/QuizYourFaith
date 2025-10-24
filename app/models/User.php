<?php namespace App\Models;

class User {
    // Basic user methods
    static function findByEmail($e) {
        $st = db()->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $st->execute([$e]);
        return $st->fetch();
    }
    
    static function findById($id) {
        $st = db()->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    static function create($d) {
        db()->prepare("INSERT INTO users (name,email,password,role,created_at) VALUES (?,?,?,'user',NOW())")
            ->execute([$d['name'], $d['email'], $d['password']]);
        return db()->lastInsertId();
    }
    
    // Role-based methods
    static function isSuperAdmin($userId) {
        $st = db()->prepare("SELECT role FROM users WHERE id=? AND role='super_admin'");
        $st->execute([$userId]);
        return $st->fetch() !== false;
    }
    
    static function isAdmin($userId) {
        $st = db()->prepare("SELECT role FROM users WHERE id=? AND role IN ('admin', 'super_admin')");
        $st->execute([$userId]);
        return $st->fetch() !== false;
    }
    
    static function isUser($userId) {
        $st = db()->prepare("SELECT role FROM users WHERE id=? AND role='user'");
        $st->execute([$userId]);
        return $st->fetch() !== false;
    }
    
    static function getRole($userId) {
        $st = db()->prepare("SELECT role FROM users WHERE id=?");
        $st->execute([$userId]);
        $result = $st->fetch();
        return $result ? $result->role : null;
    }
    
    // Multiplayer status methods
    static function updateOnlineStatus($userId, $status) {
        $st = db()->prepare("UPDATE users SET online_status=?, last_seen_at=NOW() WHERE id=?");
        return $st->execute([$status, $userId]);
    }
    
    static function getOnlineStatus($userId) {
        $st = db()->prepare("SELECT online_status FROM users WHERE id=?");
        $st->execute([$userId]);
        $result = $st->fetch();
        return $result ? $result->online_status : 'offline';
    }
    
    // Friend system methods
    static function getFriends($userId, $status = 'accepted') {
        $st = db()->prepare("
            SELECT u.*, f.status as friendship_status, f.created_at as friends_since 
            FROM friends f 
            JOIN users u ON (f.friend_id = u.id OR f.user_id = u.id) 
            WHERE (f.user_id = ? OR f.friend_id = ?) 
            AND u.id != ? 
            AND f.status = ?
            ORDER BY u.name
        ");
        $st->execute([$userId, $userId, $userId, $status]);
        return $st->fetchAll();
    }
    
    static function sendFriendRequest($userId, $friendId) {
        // Check if already friends or request exists
        $st = db()->prepare("
            SELECT status FROM friends 
            WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
        ");
        $st->execute([$userId, $friendId, $friendId, $userId]);
        $existing = $st->fetch();
        
        if ($existing) {
            return false; // Already friends or request exists
        }
        
        $st = db()->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
        return $st->execute([$userId, $friendId]);
    }
    
    static function acceptFriendRequest($userId, $friendId) {
        $st = db()->prepare("
            UPDATE friends 
            SET status = 'accepted' 
            WHERE user_id = ? AND friend_id = ? AND status = 'pending'
        ");
        return $st->execute([$friendId, $userId]);
    }
    
    static function declineFriendRequest($userId, $friendId) {
        $st = db()->prepare("
            UPDATE friends 
            SET status = 'blocked' 
            WHERE user_id = ? AND friend_id = ? AND status = 'pending'
        ");
        return $st->execute([$friendId, $userId]);
    }
    
    // Match/Game methods
    static function getActiveMatches($userId) {
        $st = db()->prepare("
            SELECT m.*, mp.score, mp.correct_answers, mp.total_answers 
            FROM matches m 
            JOIN match_players mp ON m.id = mp.match_id 
            WHERE mp.user_id = ? AND m.status IN ('waiting', 'active')
            ORDER BY m.created_at DESC
        ");
        $st->execute([$userId]);
        return $st->fetchAll();
    }
    
    static function getMatchHistory($userId, $limit = 10) {
        $st = db()->prepare("
            SELECT m.*, mp.score, mp.result, mp.finished_at 
            FROM matches m 
            JOIN match_players mp ON m.id = mp.match_id 
            WHERE mp.user_id = ? AND m.status = 'completed'
            ORDER BY mp.finished_at DESC
            LIMIT ?
        ");
        $st->execute([$userId, $limit]);
        return $st->fetchAll();
    }
    
    // Ban/Unban methods (for admins)
    static function banUser($userId, $reason = null, $duration = null) {
        $bannedUntil = $duration ? date('Y-m-d H:i:s', strtotime("+{$duration} days")) : null;
        $st = db()->prepare("UPDATE users SET is_banned = TRUE, ban_reason = ?, banned_until = ? WHERE id = ?");
        return $st->execute([$reason, $bannedUntil, $userId]);
    }
    
    static function unbanUser($userId) {
        $st = db()->prepare("UPDATE users SET is_banned = FALSE, ban_reason = NULL, banned_until = NULL WHERE id = ?");
        return $st->execute([$userId]);
    }
    
    static function isBanned($userId) {
        $st = db()->prepare("SELECT is_banned, banned_until FROM users WHERE id = ?");
        $st->execute([$userId]);
        $result = $st->fetch();
        
        if (!$result || !$result->is_banned) {
            return false;
        }
        
        // Check if ban has expired
        if ($result->banned_until && strtotime($result->banned_until) < time()) {
            self::unbanUser($userId);
            return false;
        }
        
        return true;
    }
    
    // Statistics methods
    static function getStats($userId) {
        $st = db()->prepare("
            SELECT 
                (SELECT COUNT(*) FROM match_players WHERE user_id = ? AND result = 'win') as matches_won,
                (SELECT COUNT(*) FROM match_players WHERE user_id = ?) as total_matches,
                (SELECT COUNT(*) FROM friends WHERE (user_id = ? OR friend_id = ?) AND status = 'accepted') as friends_count,
                (SELECT COUNT(*) FROM user_multiplayer_achievements WHERE user_id = ?) as achievements_count,
                (SELECT COALESCE(SUM(score), 0) FROM match_players WHERE user_id = ?) as total_score
        ");
        $st->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
        return $st->fetch();
    }
    
    // Search users
    static function searchUsers($query, $excludeUserId, $limit = 10) {
        $st = db()->prepare("
            SELECT id, name, email, online_status, avatar 
            FROM users 
            WHERE (name LIKE ? OR email LIKE ?) 
            AND id != ? 
            AND is_banned = FALSE
            ORDER BY name 
            LIMIT ?
        ");
        $searchTerm = "%{$query}%";
        $st->execute([$searchTerm, $searchTerm, $excludeUserId, $limit]);
        return $st->fetchAll();
    }
    
    // Update user profile
    static function updateProfile($userId, $data) {
        $allowedFields = ['name', 'avatar', 'bio', 'max_friends'];
        $setParts = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $setParts[] = "{$field} = ?";
                $values[] = $value;
            }
        }
        
        if (empty($setParts)) {
            return false;
        }
        
        $values[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
        $st = db()->prepare($sql);
        return $st->execute($values);
    }
}
