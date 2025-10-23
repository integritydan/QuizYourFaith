<?php
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Core\DB;

class HardenController extends Controller{
    public function index(){ $this->view('admin/harden'); }

    public function run(){
        $report=[];
        // 1. HTTPS redirect file
        $htaccess=ROOT.'/.htaccess';
        $secureHtaccess='
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Protect sensitive files
<Files ~ "\.(env|json|lock|md|git|sh)$">
    Order allow,deny
    Deny from all
</Files>

# Disable directory listing
Options -Indexes
';
        if(file_put_contents($htaccess,$secureHtaccess)) $report['https']='ok'; else $report['https']='fail';

        // 2. Production PHP flag
        $prodIni=ROOT.'/.user.ini';
        file_put_contents($prodIni,"display_errors=Off\nexpose_php=Off");
        $report['php_harden']='ok';

        // 3. Lock installer
        $lock=ROOT.'/storage/installed.lock';
        file_put_contents($lock,date('Y-m-d H:i:s'));
        $report['installer']='ok';

        // 4. Remove build artifacts
        $toRemove=['install','docker-compose.yml','.gitignore','*.md','*.sh'];
        foreach($toRemove as $pat){
            foreach(glob(ROOT.'/'.$pat) as $f) if(is_file($f)) unlink($f);
        }
        $report['cleanup']='ok';

        // 5. Correct permissions (leave owner exec)
        chmod(ROOT.'/.htaccess',0644);
        chmod(ROOT.'/.user.ini',0644);
        chmod($lock,0660);
        chmod(ROOT.'/storage',0770);
        chmod(ROOT.'/public/uploads',0770);
        $report['perms']='ok';

        echo json_encode($report);
    }
}
