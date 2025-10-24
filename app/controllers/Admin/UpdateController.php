<?php namespace App\Controllers\Admin;
use App\Middleware\Auth;
use ZipArchive;

class UpdateController {
    
    function __construct() {
        Auth::superAdminMiddleware();
    }
    
    /**
     * Display update interface
     */
    function index() {
        $currentVersion = $this->getCurrentVersion();
        $updateHistory = $this->getUpdateHistory();
        
        view('admin/update/index', [
            'current_version' => $currentVersion,
            'update_history' => $updateHistory,
            'backup_available' => $this->isBackupAvailable()
        ]);
    }
    
    /**
     * Handle ZIP file upload and update
     */
    function uploadUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/update');
            exit;
        }
        
        Auth::validateCSRF();
        
        // Validate file upload
        if (!isset($_FILES['update_file']) || $_FILES['update_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please select a valid ZIP file';
            redirect('/admin/update');
            exit;
        }
        
        $file = $_FILES['update_file'];
        $fileName = $file['name'];
        $fileTmpPath = $file['tmp_name'];
        $fileSize = $file['size'];
        
        // Validate file type and size
        if (!preg_match('/\.zip$/i', $fileName)) {
            $_SESSION['error'] = 'Only ZIP files are allowed';
            redirect('/admin/update');
            exit;
        }
        
        if ($fileSize > 50 * 1024 * 1024) { // 50MB limit
            $_SESSION['error'] = 'File size must be less than 50MB';
            redirect('/admin/update');
            exit;
        }
        
        // Create update directory
        $updateDir = BASE_PATH . '/storage/updates/' . date('Y-m-d_H-i-s');
        if (!is_dir($updateDir)) {
            mkdir($updateDir, 0755, true);
        }
        
        // Move uploaded file
        $zipPath = $updateDir . '/update.zip';
        if (!move_uploaded_file($fileTmpPath, $zipPath)) {
            $_SESSION['error'] = 'Failed to upload file';
            redirect('/admin/update');
            exit;
        }
        
        // Validate ZIP contents
        $validation = $this->validateUpdatePackage($zipPath);
        if (!$validation['valid']) {
            unlink($zipPath);
            $_SESSION['error'] = 'Invalid update package: ' . $validation['error'];
            redirect('/admin/update');
            exit;
        }
        
        // Start update process
        try {
            $this->performUpdate($zipPath, $updateDir);
            $_SESSION['success'] = 'Update completed successfully!';
            Auth::logAction('system_updated', ['version' => $validation['version']]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Update failed: ' . $e->getMessage();
            $this->rollbackUpdate($updateDir);
        }
        
        redirect('/admin/update');
    }
    
    /**
     * Validate update package contents
     */
    private function validateUpdatePackage($zipPath) {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== TRUE) {
            return ['valid' => false, 'error' => 'Cannot open ZIP file'];
        }
        
        // Check for required files
        $requiredFiles = ['app/', 'public/', 'config/', 'composer.json'];
        $foundFiles = [];
        
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            foreach ($requiredFiles as $required) {
                if (strpos($filename, $required) === 0) {
                    $foundFiles[] = $required;
                }
            }
        }
        
        $zip->close();
        
        // Check if all required files are present
        $missingFiles = array_diff($requiredFiles, array_unique($foundFiles));
        if (!empty($missingFiles)) {
            return ['valid' => false, 'error' => 'Missing required files: ' . implode(', ', $missingFiles)];
        }
        
        // Extract version from composer.json
        $version = $this->extractVersionFromZip($zipPath);
        
        return ['valid' => true, 'version' => $version];
    }
    
    /**
     * Extract version from composer.json in ZIP
     */
    private function extractVersionFromZip($zipPath) {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== TRUE) {
            return 'unknown';
        }
        
        // Look for composer.json
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if ($filename === 'composer.json') {
                $content = $zip->getFromIndex($i);
                $data = json_decode($content, true);
                if (isset($data['version'])) {
                    $zip->close();
                    return $data['version'];
                }
            }
        }
        
        $zip->close();
        return 'unknown';
    }
    
    /**
     * Perform the actual update
     */
    private function performUpdate($zipPath, $updateDir) {
        // Step 1: Create backup
        $backupDir = $this->createBackup();
        
        // Step 2: Extract update package
        $extractDir = $updateDir . '/extracted';
        $this->extractZip($zipPath, $extractDir);
        
        // Step 3: Validate extracted contents
        if (!$this->validateExtractedContents($extractDir)) {
            throw new \Exception('Extracted contents validation failed');
        }
        
        // Step 4: Apply update with data preservation
        $this->applyUpdateWithPreservation($extractDir);
        
        // Step 5: Run database migrations if needed
        $this->runDatabaseMigrations($extractDir);
        
        // Step 6: Clear caches
        $this->clearCaches();
        
        // Step 7: Record update
        $this->recordUpdate($this->extractVersionFromZip($zipPath), $backupDir);
    }
    
    /**
     * Create backup of current system
     */
    private function createBackup() {
        $backupDir = BASE_PATH . '/storage/backups/' . date('Y-m-d_H-i-s');
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // Backup critical files
        $itemsToBackup = [
            'app/config/config.php',
            'app/config/database.php',
            'public/assets/',
            'storage/logs/',
            'sql/' // Database schemas
        ];
        
        foreach ($itemsToBackup as $item) {
            $source = BASE_PATH . '/' . $item;
            $destination = $backupDir . '/' . $item;
            
            if (is_file($source)) {
                $destDir = dirname($destination);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                copy($source, $destination);
            } elseif (is_dir($source)) {
                $this->copyDirectory($source, $destination);
            }
        }
        
        // Create database backup
        $this->createDatabaseBackup($backupDir);
        
        return $backupDir;
    }
    
    /**
     * Apply update while preserving user data
     */
    private function applyUpdateWithPreservation($extractDir) {
        // List of files to preserve (user data, configs, etc.)
        $preserveFiles = [
            'app/config/config.php',
            'app/config/database.php',
            'app/config/activation.php',
            'storage/logs/',
            'storage/cache/',
            'uploads/' // User uploads
        ];
        
        // Create temporary preservation directory
        $preserveDir = BASE_PATH . '/storage/temp/preserve_' . time();
        mkdir($preserveDir, 0755, true);
        
        // Move current files to preserve
        foreach ($preserveFiles as $file) {
            $source = BASE_PATH . '/' . $file;
            $destination = $preserveDir . '/' . $file;
            
            if (file_exists($source)) {
                if (is_file($source)) {
                    $destDir = dirname($destination);
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    rename($source, $destination);
                } elseif (is_dir($source)) {
                    $this->copyDirectory($source, $destination);
                    $this->removeDirectory($source);
                }
            }
        }
        
        // Copy new files
        $this->copyDirectory($extractDir, BASE_PATH);
        
        // Restore preserved files
        foreach ($preserveFiles as $file) {
            $source = $preserveDir . '/' . $file;
            $destination = BASE_PATH . '/' . $file;
            
            if (file_exists($source)) {
                if (is_file($source)) {
                    $destDir = dirname($destination);
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    rename($source, $destination);
                } elseif (is_dir($source)) {
                    $this->copyDirectory($source, $destination);
                }
            }
        }
        
        // Clean up preservation directory
        $this->removeDirectory($preserveDir);
    }
    
    /**
     * Run database migrations
     */
    private function runDatabaseMigrations($extractDir) {
        $migrationsDir = $extractDir . '/sql/migrations';
        
        if (is_dir($migrationsDir)) {
            $migrationFiles = glob($migrationsDir . '/*.sql');
            sort($migrationFiles);
            
            foreach ($migrationFiles as $migrationFile) {
                $this->executeMigration($migrationFile);
            }
        }
    }
    
    /**
     * Execute a single migration file
     */
    private function executeMigration($migrationFile) {
        $content = file_get_contents($migrationFile);
        
        // Skip if migration already applied
        $migrationName = basename($migrationFile, '.sql');
        $st = db()->prepare("SELECT id FROM migrations WHERE name = ?");
        $st->execute([$migrationName]);
        
        if ($st->fetch()) {
            return; // Already applied
        }
        
        // Execute migration
        try {
            db()->exec($content);
            
            // Record migration
            $st = db()->prepare("INSERT INTO migrations (name, executed_at) VALUES (?, NOW())");
            $st->execute([$migrationName]);
            
        } catch (\Exception $e) {
            // Log error but continue (migration might be for different DB version)
            error_log("Migration failed: " . $migrationFile . " - " . $e->getMessage());
        }
    }
    
    /**
     * Clear all caches
     */
    private function clearCaches() {
        // Clear application cache
        $cacheDir = BASE_PATH . '/storage/cache';
        if (is_dir($cacheDir)) {
            $this->removeDirectoryContents($cacheDir);
        }
        
        // Clear session cache (optional)
        // session_destroy();
        // session_start();
        
        // Clear OPcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
    
    /**
     * Record update in database
     */
    private function recordUpdate($version, $backupDir) {
        $st = db()->prepare("
            INSERT INTO system_updates (version, backup_path, applied_at, applied_by) 
            VALUES (?, ?, NOW(), ?)
        ");
        $st->execute([$version, $backupDir, $_SESSION['user_id']]);
    }
    
    /**
     * Rollback update on failure
     */
    private function rollbackUpdate($updateDir) {
        // Restore from backup if available
        $backupDir = $this->getLatestBackup();
        if ($backupDir) {
            $this->restoreFromBackup($backupDir);
        }
        
        // Clean up update directory
        $this->removeDirectory($updateDir);
    }
    
    /**
     * Helper: Copy directory recursively
     */
    private function copyDirectory($source, $destination) {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $destPath = $destination . '/' . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $destPath);
            }
        }
    }
    
    /**
     * Helper: Remove directory recursively
     */
    private function removeDirectory($directory) {
        if (!is_dir($directory)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
        
        rmdir($directory);
    }
    
    /**
     * Helper: Remove directory contents only
     */
    private function removeDirectoryContents($directory) {
        if (!is_dir($directory)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
    }
    
    /**
     * Extract ZIP file
     */
    private function extractZip($zipPath, $extractTo) {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($extractTo);
            $zip->close();
        } else {
            throw new \Exception('Failed to extract ZIP file');
        }
    }
    
    /**
     * Create database backup
     */
    private function createDatabaseBackup($backupDir) {
        $dbConfig = config();
        $backupFile = $backupDir . '/database_backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            escapeshellarg($dbConfig['DB_HOST']),
            escapeshellarg($dbConfig['DB_USER']),
            escapeshellarg($dbConfig['DB_PASS']),
            escapeshellarg($dbConfig['DB_NAME']),
            escapeshellarg($backupFile)
        );
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('Database backup failed: ' . implode("\n", $output));
        }
        
        return $backupFile;
    }
    
    /**
     * Get current system version
     */
    private function getCurrentVersion() {
        $composerFile = BASE_PATH . '/composer.json';
        if (file_exists($composerFile)) {
            $content = file_get_contents($composerFile);
            $data = json_decode($content, true);
            return $data['version'] ?? '1.0.0';
        }
        return '1.0.0';
    }
    
    /**
     * Get update history
     */
    private function getUpdateHistory() {
        $st = db()->prepare("
            SELECT su.*, u.name as applied_by_name
            FROM system_updates su
            JOIN users u ON su.applied_by = u.id
            ORDER BY su.applied_at DESC
            LIMIT 10
        ");
        $st->execute();
        return $st->fetchAll();
    }
    
    /**
     * Check if backup is available
     */
    private function isBackupAvailable() {
        $backupDir = BASE_PATH . '/storage/backups';
        return is_dir($backupDir) && count(glob($backupDir . '/*')) > 0;
    }
    
    /**
     * Get latest backup
     */
    private function getLatestBackup() {
        $backupDir = BASE_PATH . '/storage/backups';
        if (!is_dir($backupDir)) {
            return null;
        }
        
        $backups = glob($backupDir . '/*');
        if (empty($backups)) {
            return null;
        }
        
        // Sort by modification time (newest first)
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        return $backups[0];
    }
    
    /**
     * Validate extracted contents
     */
    private function validateExtractedContents($extractDir) {
        // Check for required directories
        $requiredDirs = ['app', 'public', 'config', 'sql'];
        foreach ($requiredDirs as $dir) {
            if (!is_dir($extractDir . '/' . $dir)) {
                return false;
            }
        }
        
        // Check for critical files
        $requiredFiles = ['public/index.php', 'composer.json'];
        foreach ($requiredFiles as $file) {
            if (!file_exists($extractDir . '/' . $file)) {
                return false;
            }
        }
        
        return true;
    }
}
