<?php namespace App\Models;
class Quiz{
    static function all(){return db()->query("SELECT * FROM quizzes")->fetchAll();}
    static function find($id){return db()->prepare("SELECT * FROM quizzes WHERE id=? LIMIT 1")->execute([$id])->fetch();}
}
