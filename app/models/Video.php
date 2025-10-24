<?php namespace App\Models;

class Video {
    // Basic CRUD operations
    static function all($activeOnly = true) {
        $sql = "SELECT v.*, c.name as category_name, c.color as category_color, u.name as created_by_name 
                FROM youtube_videos v 
                LEFT JOIN video_categories c ON v.category_id = c.id 
                LEFT JOIN users u ON v.created_by = u.id";
        
        if ($activeOnly) {
            $sql .= " WHERE v.is_active = TRUE";
        }
        
        $sql .= " ORDER BY v.display_order ASC, v.created_at DESC";
        
        return db()->query($sql)->fetchAll();
    }
    
    static function find($id) {
        $st = db()->prepare("
            SELECT v.*, c.name as category_name, c.color as category_color, u.name as created_by_name 
            FROM youtube_videos v 
            LEFT JOIN video_categories c ON v.category_id = c.id 
            LEFT JOIN users u ON v.created_by = u.id 
            WHERE v.id = ? LIMIT 1
        ");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    static function findByVideoId($youtubeVideoId) {
        $st = db()->prepare("SELECT * FROM youtube_videos WHERE youtube_video_id = ? LIMIT 1");
        $st->execute([$youtubeVideoId]);
        return $st->fetch();
    }
    
    static function create($data) {
        $sql = "INSERT INTO youtube_videos (
            title, description, youtube_url, youtube_video_id, thumbnail_url, 
            duration, category_id, tags, display_order, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $st = db()->prepare($sql);
        $st->execute([
            $data['title'], $data['description'], $data['youtube_url'], 
            $data['youtube_video_id'], $data['thumbnail_url'], $data['duration'], 
            $data['category_id'], $data['tags'], $data['display_order'], $data['created_by']
        ]);
        
        return db()->lastInsertId();
    }
    
    static function update($id, $data) {
        $allowedFields = ['title', 'description', 'youtube_url', 'youtube_video_id', 
                         'thumbnail_url', 'duration', 'category_id', 'tags', 
                         'is_active', 'display_order'];
        
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
        
        $values[] = $id;
        $sql = "UPDATE youtube_videos SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = ?";
        $st = db()->prepare($sql);
        return $st->execute($values);
    }
    
    static function delete($id) {
        $st = db()->prepare("DELETE FROM youtube_videos WHERE id = ?");
        return $st->execute([$id]);
    }
    
    // Slider specific methods
    static function getActiveVideos($limit = 10) {
        $st = db()->prepare("
            SELECT v.*, c.name as category_name, c.color as category_color 
            FROM youtube_videos v 
            LEFT JOIN video_categories c ON v.category_id = c.id 
            WHERE v.is_active = TRUE 
            ORDER BY v.display_order ASC, v.created_at DESC 
            LIMIT ?
        ");
        $st->execute([$limit]);
        return $st->fetchAll();
    }
    
    static function getVideosByCategory($categoryId, $limit = 10) {
        $st = db()->prepare("
            SELECT v.*, c.name as category_name, c.color as category_color 
            FROM youtube_videos v 
            LEFT JOIN video_categories c ON v.category_id = c.id 
            WHERE v.is_active = TRUE AND v.category_id = ? 
            ORDER BY v.display_order ASC, v.created_at DESC 
            LIMIT ?
        ");
        $st->execute([$categoryId, $limit]);
        return $st->fetchAll();
    }
    
    // Category methods
    static function getCategories($activeOnly = true) {
        $sql = "SELECT * FROM video_categories";
        if ($activeOnly) {
            $sql .= " WHERE is_active = TRUE";
        }
        $sql .= " ORDER BY name ASC";
        
        return db()->query($sql)->fetchAll();
    }
    
    static function findCategory($id) {
        $st = db()->prepare("SELECT * FROM video_categories WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    static function createCategory($data) {
        $st = db()->prepare("
            INSERT INTO video_categories (name, slug, description, color, icon, is_active) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $st->execute([
            $data['name'], $data['slug'], $data['description'], 
            $data['color'], $data['icon'], $data['is_active']
        ]);
        return db()->lastInsertId();
    }
    
    // Analytics methods
    static function incrementViews($videoId) {
        $st = db()->prepare("UPDATE youtube_videos SET views_count = views_count + 1 WHERE id = ?");
        return $st->execute([$videoId]);
    }
    
    static function recordView($videoId, $userId = null, $ipAddress = null, $userAgent = null) {
        $st = db()->prepare("
            INSERT INTO video_views (video_id, user_id, ip_address, user_agent, viewed_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $st->execute([$videoId, $userId, $ipAddress, $userAgent]);
    }
    
    static function getVideoStats($videoId) {
        $st = db()->prepare("
            SELECT 
                v.views_count,
                (SELECT COUNT(*) FROM video_views WHERE video_id = v.id) as total_views,
                (SELECT COUNT(*) FROM video_reactions WHERE video_id = v.id AND reaction = 'like') as likes,
                (SELECT COUNT(*) FROM video_reactions WHERE video_id = v.id AND reaction = 'dislike') as dislikes
            FROM youtube_videos v 
            WHERE v.id = ?
        ");
        $st->execute([$videoId]);
        return $st->fetch();
    }
    
    // Reaction methods
    static function addReaction($videoId, $userId, $reaction) {
        // Remove existing reaction first
        $st = db()->prepare("DELETE FROM video_reactions WHERE video_id = ? AND user_id = ?");
        $st->execute([$videoId, $userId]);
        
        // Add new reaction
        $st = db()->prepare("INSERT INTO video_reactions (video_id, user_id, reaction) VALUES (?, ?, ?)");
        return $st->execute([$videoId, $userId, $reaction]);
    }
    
    static function removeReaction($videoId, $userId) {
        $st = db()->prepare("DELETE FROM video_reactions WHERE video_id = ? AND user_id = ?");
        return $st->execute([$videoId, $userId]);
    }
    
    static function getUserReaction($videoId, $userId) {
        $st = db()->prepare("SELECT reaction FROM video_reactions WHERE video_id = ? AND user_id = ? LIMIT 1");
        $st->execute([$videoId, $userId]);
        $result = $st->fetch();
        return $result ? $result->reaction : null;
    }
    
    // Utility methods for YouTube integration
    static function extractYouTubeVideoId($url) {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return false;
    }
    
    static function getYouTubeThumbnail($videoId) {
        return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
    }
    
    static function getYouTubeEmbedUrl($videoId) {
        return "https://www.youtube.com/embed/{$videoId}";
    }
}