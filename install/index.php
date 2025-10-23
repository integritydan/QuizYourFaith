<?php
session_start();
$step = $_GET['step'] ?? 1;
$err  = '';

if ($_SERVER['REQUEST_METHOD']==='POST'){
    if($step==1){
        try{
            $dsn="mysql:host={$_POST['db_host']};dbname={$_POST['db_name']};charset=utf8mb4";
            $pdo=new PDO($dsn,$_POST['db_user'],$_POST['db_pass']);
            $_SESSION['db']=$_POST;
            header('Location: ?step=2'); exit;
        }catch(PDOException $e){ $err='Connection failed: '.$e->getMessage(); }
    }
    if($step==2){
        $db=$_SESSION['db'];
        $dsn="mysql:host={$db['db_host']};dbname={$db['db_name']};charset=utf8mb4";
        $pdo=new PDO($dsn,$db['db_user'],$db['db_pass']);
        $sql=file_get_contents(__DIR__.'/quizyourfaith.sql');
        $pdo->exec($sql);
        $env="DB_HOST={$db['db_host']}\nDB_NAME={$db['db_name']}\nDB_USER={$db['db_user']}\nDB_PASS={$db['db_pass']}\n";
        file_put_contents(__DIR__.'/../.env',$env);
        header('Location: /'); exit;
    }
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>QuizYourFaith Installer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container py-5">
<h2>Step <?= $step ?> of 2</h2>
<?php if($err): ?><div class="alert alert-danger"><?= $err ?></div><?php endif; ?>
<?php if($step==1): ?>
<form method="post">
  <div class="mb-3"><label>Database Host</label><input class="form-control" name="db_host" required value="localhost"></div>
  <div class="mb-3"><label>Database Name (create first in cPanel)</label><input class="form-control" name="db_name" required placeholder="quizyourfaith22_qyf_db"></div>
  <div class="mb-3"><label>Database User</label><input class="form-control" name="db_user" required></div>
  <div class="mb-3"><label>Database Password</label><input type="password" class="form-control" name="db_pass" required></div>
  <button class="btn btn-primary">Test & Continue</button>
</form>
<?php else: ?>
<p>Click to finish installation.</p>
<form method="post"><button class="btn btn-success">Install Tables</button></form>
<?php endif; ?>
</div></body></html>
