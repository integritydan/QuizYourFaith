<?php namespace App\Models;

class Feature {
    
    // Core feature management methods
    static function findByName($name) {
        $st = db()->prepare("SELECT * FROM features WHERE name = ? LIMIT 1");
        $st->execute([$name]);
        return $st->fetch();
    }
    
    static function findById($id) {
        $st = db()->prepare("SELECT * FROM features WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    static function all($category = null, $enabledOnly = false) {
        $sql = "SELECT f.*, fc.display_name as category_display_name, fc.color as category_color, fc.icon as category_icon 
                FROM features f 
                LEFT JOIN feature_categories fc ON f.category = fc.name 
                WHERE 1=1";
        
        $params = [];
        
        if ($category) {
            $sql .= " AND f.category = ?";
            $params[] = $category;
        }
        
        if ($enabledOnly) {
            $sql .= " AND f.is_enabled = TRUE AND fc.is_active = TRUE";
        }
        
        $sql .= " ORDER BY fc.sort_order ASC, f.display_name ASC";
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    static function getActiveFeatures() {
        return db()->query("SELECT * FROM active_features")->fetchAll();
    }
    
    static function isEnabled($featureName) {
        $st = db()->prepare("SELECT is_enabled FROM features WHERE name = ? LIMIT 1");
        $st->execute([$featureName]);
        $result = $st->fetch();
        return $result ? (bool)$result->is_enabled : false;
    }
    
    static function enable($featureName, $userId = null) {
        $st = db()->prepare("UPDATE features SET is_enabled = TRUE, enabled_at = NOW(), disabled_at = NULL WHERE name = ?");
        $success = $st->execute([$featureName]);
        
        if ($success) {
            self::logChange($featureName, 'enabled', null, null, $userId);
        }
        
        return $success;
    }
    
    static function disable($featureName, $userId = null) {
        $st = db()->prepare("UPDATE features SET is_enabled = FALSE, disabled_at = NOW() WHERE name = ?");
        $success = $st->execute([$featureName]);
        
        if ($success) {
            self::logChange($featureName, 'disabled', null, null, $userId);
        }
        
        return $success;
    }
    
    static function toggle($featureName, $userId = null) {
        $feature = self::findByName($featureName);
        if (!$feature) return false;
        
        return $feature->is_enabled ? self::disable($featureName, $userId) : self::enable($featureName, $userId);
    }
    
    static function create($data) {
        $sql = "INSERT INTO features (name, display_name, description, is_enabled, category, requires_permission, config_data, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $st = db()->prepare($sql);
        $st->execute([
            $data['name'], $data['display_name'], $data['description'], 
            $data['is_enabled'], $data['category'], $data['requires_permission'], 
            $data['config_data'], $data['created_by']
        ]);
        
        $featureId = db()->lastInsertId();
        
        if ($data['is_enabled']) {
            self::logChange($data['name'], 'enabled', null, null, $data['created_by']);
        }
        
        return $featureId;
    }
    
    static function update($id, $data) {
        $allowedFields = ['display_name', 'description', 'category', 'requires_permission', 'config_data'];
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
        $sql = "UPDATE features SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = ?";
        $st = db()->prepare($sql);
        
        $success = $st->execute($values);
        
        if ($success) {
            self::logChange($data['name'] ?? self::findById($id)->name, 'updated', json_encode($data), null, $data['updated_by'] ?? null);
        }
        
        return $success;
    }
    
    static function delete($id) {
        $feature = self::findById($id);
        if (!$feature) return false;
        
        $st = db()->prepare("DELETE FROM features WHERE id = ?");
        $success = $st->execute([$id]);
        
        if ($success) {
            self::logChange($feature->name, 'deleted', null, null, null);
        }
        
        return $success;
    }
    
    // Category management
    static function getCategories($activeOnly = true) {
        $sql = "SELECT * FROM feature_categories";
        if ($activeOnly) {
            $sql .= " WHERE is_active = TRUE";
        }
        $sql .= " ORDER BY sort_order ASC, display_name ASC";
        
        return db()->query($sql)->fetchAll();
    }
    
    static function findCategory($id) {
        $st = db()->prepare("SELECT * FROM feature_categories WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    static function findCategoryByName($name) {
        $st = db()->prepare("SELECT * FROM feature_categories WHERE name = ? LIMIT 1");
        $st->execute([$name]);
        return $st->fetch();
    }
    
    // Audit logging
    static function logChange($featureName, $action, $oldValue, $newValue, $userId) {
        $st = db()->prepare("
            INSERT INTO feature_audit_log (feature_id, action, old_value, new_value, user_id, ip_address, user_agent, created_at) 
            VALUES ((SELECT id FROM features WHERE name = ?), ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        return $st->execute([$featureName, $action, $oldValue, $newValue, $userId, $ipAddress, $userAgent]);
    }
    
    static function getAuditLog($featureId = null, $limit = 50) {
        $sql = "SELECT fal.*, f.name as feature_name, u.name as user_name 
                FROM feature_audit_log fal 
                LEFT JOIN features f ON fal.feature_id = f.id 
                LEFT JOIN users u ON fal.user_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($featureId) {
            $sql .= " AND fal.feature_id = ?";
            $params[] = $featureId;
        }
        
        $sql .= " ORDER BY fal.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    
    // Utility methods
    static function checkDependencies($featureName) {
        $feature = self::findByName($featureName);
        if (!$feature || !$feature->dependencies) {
            return true; // No dependencies or feature not found
        }
        
        $dependencies = json_decode($feature->dependencies, true);
        if (!is_array($dependencies)) {
            return true;
        }
        
        foreach ($dependencies as $dependency) {
            if (!self::isEnabled($dependency)) {
                return false; // Dependency not enabled
            }
        }
        
        return true;
    }
    
    static function getConfig($featureName, $key = null) {
        $feature = self::findByName($featureName);
        if (!$feature || !$feature->config_data) {
            return null;
        }
        
        $config = json_decode($feature->config_data, true);
        if (!is_array($config)) {
            return null;
        }
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? null;
    }
    
    static function setConfig($featureName, $configData) {
        $st = db()->prepare("UPDATE features SET config_data = ? WHERE name = ?");
        return $st->execute([json_encode($configData), $featureName]);
    }
    
    // Bulk operations
    static function enableCategory($categoryName, $userId = null) {
        $st = db()->prepare("UPDATE features SET is_enabled = TRUE, enabled_at = NOW(), disabled_at = NULL WHERE category = ?");
        $success = $st->execute([$categoryName]);
        
        if ($success) {
            $features = self::all($categoryName);
            foreach ($features as $feature) {
                self::logChange($feature->name, 'enabled', null, null, $userId);
            }
        }
        
        return $success;
    }
    
    static function disableCategory($categoryName, $userId = null) {
        $st = db()->prepare("UPDATE features SET is_enabled = FALSE, disabled_at = NOW() WHERE category = ?");
        $success = $st->execute([$categoryName]);
        
        if ($success) {
            $features = self::all($categoryName);
            foreach ($features as $feature) {
                self::logChange($feature->name, 'disabled', null, null, $userId);
            }
        }
        
        return $success;
    }
}