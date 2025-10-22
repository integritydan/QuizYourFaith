<?php namespace App\Models;
class Question{
    static function byQuiz($id){return db()->prepare("SELECT * FROM questions WHERE quiz_id=? ORDER BY id")->execute([$id])->fetchAll();}
}
