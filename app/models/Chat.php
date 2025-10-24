<?php namespace App\Models;

class Chat {
    
    /**
     * Clear all chat messages for a specific match
     */
    static function clearMatchChat($matchId) {
        $st = db()->prepare("DELETE FROM chat_messages WHERE match_id = ?");
        return $st->execute([$matchId]);
    }
    
    /**
     * Clear chat messages for a specific user in a match
     */
    static function clearUserChat($userId, $matchId) {
        $st = db()->prepare("DELETE FROM chat_messages WHERE user_id = ? AND match_id = ?");
        return $st->execute([$userId, $matchId]);
    }
    
    /**
     * Clear all chat messages older than specified hours
     */
    static function clearOldMessages($hours = 24) {
        $st = db()->prepare("DELETE FROM chat_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)");
        return $st->execute([$hours]);
    }
    
    /**
     * Clear chat messages when a user leaves a match
     */
    static function clearChatOnUserLeave($userId, $matchId) {
        // Clear user's messages from the match
        self::clearUserChat($userId, $matchId);
        
        // Add system message about user leaving
        $st = db()->prepare("
            INSERT INTO chat_messages (match_id, user_id, message, message_type, created_at)
            VALUES (?, 0, ?, 'system', NOW())
        ");
        
        $user = User::findById($userId);
        $userName = $user ? $user->name : 'User';
        $message = "{$userName} left the match and chat history was cleared";
        
        return $st->execute([$matchId, $message]);
    }
    
    /**
     * Clear chat messages when a match ends
     */
    static function clearChatOnMatchEnd($matchId) {
        // Clear all messages from the match
        return self::clearMatchChat($matchId);
    }
    
    /**
     * Clear chat messages when all players leave a match
     */
    static function clearChatIfMatchEmpty($matchId) {
        // Check if match has any players left
        $st = db()->prepare("SELECT COUNT(*) FROM match_players WHERE match_id = ?");
        $st->execute([$matchId]);
        $playerCount = $st->fetchColumn();
        
        if ($playerCount == 0) {
            // Clear all chat messages
            return self::clearMatchChat($matchId);
        }
        
        return true;
    }
    
    /**
     * Get chat messages for a match (with optional limit)
     */
    static function getMatchMessages($matchId, $limit = 50) {
        $st = db()->prepare("
            SELECT 
                cm.*,
                u.name as user_name,
                u.avatar
            FROM chat_messages cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.match_id = ?
            ORDER BY cm.created_at DESC
            LIMIT ?
        ");
        $st->execute([$matchId, $limit]);
        return array_reverse($st->fetchAll()); // Reverse to show oldest first
    }
    
    /**
     * Get recent chat messages for WebSocket
     */
    static function getRecentMessages($matchId, $lastMessageId = 0) {
        $st = db()->prepare("
            SELECT 
                cm.*,
                u.name as user_name,
                u.avatar
            FROM chat_messages cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.match_id = ? AND cm.id > ?
            ORDER BY cm.created_at ASC
        ");
        $st->execute([$matchId, $lastMessageId]);
        return $st->fetchAll();
    }
    
    /**
     * Save a chat message
     */
    static function saveMessage($matchId, $userId, $message, $messageType = 'text') {
        $st = db()->prepare("
            INSERT INTO chat_messages (match_id, user_id, message, message_type, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $st->execute([$matchId, $userId, $message, $messageType]);
    }
    
    /**
     * Get chat statistics for a match
     */
    static function getChatStats($matchId) {
        $st = db()->prepare("
            SELECT 
                COUNT(*) as total_messages,
                COUNT(DISTINCT user_id) as unique_users,
                MAX(created_at) as last_message_time
            FROM chat_messages
            WHERE match_id = ?
        ");
        $st->execute([$matchId]);
        return $st->fetch();
    }
    
    /**
     * Check if user has sent messages in a match
     */
    static function hasUserSentMessages($userId, $matchId) {
        $st = db()->prepare("SELECT COUNT(*) FROM chat_messages WHERE user_id = ? AND match_id = ?");
        $st->execute([$userId, $matchId]);
        return $st->fetchColumn() > 0;
    }
    
    /**
     * Clear chat messages for inactive matches
     */
    static function clearInactiveMatchChats($hours = 2) {
        // Get matches that ended more than specified hours ago
        $st = db()->prepare("
            SELECT id FROM matches 
            WHERE status = 'completed' 
            AND end_time < DATE_SUB(NOW(), INTERVAL ? HOUR)
        ");
        $st->execute([$hours]);
        $matches = $st->fetchAll();
        
        $cleared = 0;
        foreach ($matches as $match) {
            if (self::clearMatchChat($match->id)) {
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Archive old chat messages instead of deleting (for compliance)
     */
    static function archiveOldMessages($days = 30) {
        $archiveDir = BASE_PATH . '/storage/archives/chat';
        if (!is_dir($archiveDir)) {
            mkdir($archiveDir, 0755, true);
        }
        
        $archiveFile = $archiveDir . '/chat_archive_' . date('Y-m-d') . '.json';
        
        // Get messages older than specified days
        $st = db()->prepare("
            SELECT cm.*, u.name as user_name
            FROM chat_messages cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $st->execute([$days]);
        $messages = $st->fetchAll();
        
        if (empty($messages)) {
            return 0;
        }
        
        // Save to archive file
        $archiveData = [
            'archived_at' => date('Y-m-d H:i:s'),
            'messages' => $messages
        ];
        
        file_put_contents($archiveFile, json_encode($archiveData, JSON_PRETTY_PRINT));
        
        // Delete archived messages
        $st = db()->prepare("DELETE FROM chat_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        return $st->execute([$days]);
    }
}