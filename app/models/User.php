<?php namespace App\Models;
class User{
    static function findByEmail($e){$st=db()->prepare("SELECT * FROM users WHERE email=? LIMIT 1");$st->execute([$e]);return $st->fetchObject();}
    static function create($d){db()->prepare("INSERT INTO users (name,email,password,created_at) VALUES (?,?,?,NOW())")->execute([$d['name'],$d['email'],$d['password']]);return db()->lastInsertId();}
}
