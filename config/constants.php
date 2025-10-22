<?php
define('BASE_PATH', realpath(__DIR__.'/../'));
define('APP_PATH',   BASE_PATH.'/app');
define('CONFIG_PATH',BASE_PATH.'/config');
define('STORAGE_PATH',BASE_PATH.'/storage');
define('PUBLIC_PATH',BASE_PATH.'/public');
define('ASSETS_URL', '/assets');

function config($k=null){static $c=null;if($c===null){$c=parse_ini_file(BASE_PATH.'/.env',false);}return $k?$c[$k]??null:$c;}
function db(){static $pdo=null;return $pdo??$pdo=require CONFIG_PATH.'/database.php';}
function view($f,$d=[]){extract($d);require APP_PATH."/views/$f.php";}
function redirect($u){header("Location: $u");exit;}
function csrf(){static $t=null;if(!$t){$t=bin2hex(random_bytes(16));$_SESSION['csrf']=$t;}return '<input type="hidden" name="csrf" value="'.$t.'">';}
