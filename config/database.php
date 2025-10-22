<?php
$conf=config();
return new PDO(
    "mysql:host={$conf['DB_HOST']};dbname={$conf['DB_NAME']};charset=utf8mb4",
    $conf['DB_USER'], $conf['DB_PASS'],
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_OBJ]
);
