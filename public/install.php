<?php
/* QuizYourFaith – 3-step installer (lives in public/) */
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();
$step=$_GET['step']??1;
?>
<!doctype html>
<html lang="en"><head>
  <meta charset="utf-8"><title>QuizYourFaith Installer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body>
<div class="container mt-5"><div class="card shadow"><div class="card-body">
<h2 class="mb-4">QuizYourFaith Setup Wizard <span class="badge bg-info">Step <?=$step?></span></h2>

<?php
switch($step):
case 1: // Server check
    $ok=true;
    $checks=[
        'PHP >= 8.0'=>PHP_VERSION_ID>=80000,
        'PDO'=>extension_loaded('pdo'),
        'PDO MySQL'=>extension_loaded('pdo_mysql'),
        'folder /storage/logs writable'=>is_writable(dirname(__DIR__).'/storage/logs'),
    ]; ?>
    <ul class="list-group mb-3">
    <?php foreach($checks as $text=>$pass): if(!$pass)$ok=false; ?>
      <li class="list-group-item d-flex justify-content-between">
        <?=$text?><span class="badge bg-<?=$pass?'success':'danger'?>"><?=$pass?'✔':'✖'?></span>
      </li>
    <?php endforeach; ?>
    </ul>
    <?php if($ok): ?>
      <a href="?step=2" class="btn btn-primary">Next → Database</a>
    <?php else: ?>
      <div class="alert alert-danger">Fix the red items, then refresh.</div>
    <?php endif;
    break;

case 2: // DB credentials ?>
    <form method="post" action="?step=3">
      <h5>MySQL Details</h5>
      <div class="mb-3"><label>Host</label><input type="text" class="form-control" name="db_host" value="localhost" required></div>
      <div class="mb-3"><label>Database name</label><input type="text" class="form-control" name="db_name" required></div>
      <div class="mb-3"><label>Username</label><input type="text" class="form-control" name="db_user" required></div>
      <div class="mb-3"><label>Password</label><input type="text" class="form-control" name="db_pass"></div>
      <button type="submit" class="btn btn-primary">Connect & Install Tables</button>
    </form>
    <?php
    break;

case 3: // create tables + .env
    if($_SERVER['REQUEST_METHOD']!=='POST')redirect('?step=2');
    try{
        $dsn='mysql:host='.$_POST['db_host'].';dbname='.$_POST['db_name'].';charset=utf8mb4';
        $pdo=new PDO($dsn,$_POST['db_user'],$_POST['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        // run full SQL dump
        $sql=file_get_contents(dirname(__DIR__).'/sql/qyf_v1.sql');
        foreach(array_filter(array_map('trim',explode(';',$sql))) as $stmt){
            if($stmt) $pdo->exec($stmt);
        }

        // write .env
        $env="DB_HOST={$_POST['db_host']}\nDB_NAME={$_POST['db_name']}\nDB_USER={$_POST['db_user']}\nDB_PASS={$_POST['db_pass']}\n";
        file_put_contents(dirname(__DIR__).'/.env',$env.file_get_contents(dirname(__DIR__).'/.env.example'));

        echo '<div class="alert alert-success">Tables & settings saved ✔</div>';
        echo '<a href="?step=4" class="btn btn-primary">Next → Admin Account</a>';
    }catch(PDOException $e){
        echo '<div class="alert alert-danger">Connection failed: '.htmlspecialchars($e->getMessage()).'</div>';
        echo '<a href="?step=2" class="btn btn-secondary">← Retry</a>';
    }
    break;

case 4: // create admin ?>
    <form method="post" action="?step=5">
      <h5>Create Admin Account</h5>
      <div class="mb-3"><label>Name</label><input type="text" class="form-control" name="name" required></div>
      <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="email" required></div>
      <div class="mb-3"><label>Password</label><input type="password" class="form-control" name="password" required></div>
      <button type="submit" class="btn btn-primary">Create Admin</button>
    </form>
    <?php
    break;

case 5: // save admin & finish
    require dirname(__DIR__).'/config/constants.php';
    $pdo=db();
    $stmt=$pdo->prepare("INSERT INTO users (name,email,password,role,created_at) VALUES (?,?,?,'admin',NOW())");
    $stmt->execute([$_POST['name'],$_POST['email'],password_hash($_POST['password'],PASSWORD_DEFAULT)]);
    ?>
    <div class="alert alert-success">Installation complete ✔</div>
    <div class="d-flex gap-2">
      <a href="/" class="btn btn-success">Go to Site</a>
      <a href="/admin" class="btn btn-outline-primary">Admin Dashboard</a>
    </div>
    <hr>
    <b>Security:</b> Delete this file (<code>public/install.php</code>) now.
    <?php
    break;
endswitch;
?>
</div></div></div></body></html>
