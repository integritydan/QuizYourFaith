<?php
namespace App\Controllers\Admin;
use App\Core\Controller;
use ZipArchive;

class UpdateController extends Controller{
    public function index(){ $this->view('admin/update'); }
    public function upload(){
        $file=$_FILES['update_zip']??null;
        if(!$file||$file['error']!==UPLOAD_ERR_OK){
            echo json_encode(['status'=>'error','msg'=>'Upload failed']); exit;
        }
        $zipPath=$file['tmp_name'];
        $staging=ROOT.'/staging';
        shell_exec("rm -rf $staging && mkdir -p $staging");
        $zip=new ZipArchive;
        if($zip->open($zipPath)!==true){ echo json_encode(['status'=>'error','msg'=>'Corrupt zip']); exit; }
        $zip->extractTo($staging); $zip->close();
        if(!file_exists($staging.'/index.php')||!file_exists($staging.'/app')){
            echo json_encode(['status'=>'error','msg'=>'Zip missing core files']); exit;
        }
        $live=ROOT; $backup=$ROOT.'_backup_'.date('YmdHis');
        shell_exec("mv $live $backup && mv $staging $live && cp -r $backup/.git $live/");
        echo json_encode(['status'=>'ok','msg'=>'Update complete']);
    }
}
