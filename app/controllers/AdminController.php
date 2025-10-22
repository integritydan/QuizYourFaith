<?php namespace App\Controllers;
class AdminController{
    function dashboard(){
        view('admin/dashboard',[
            'users'=>db()->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'quizzes'=>db()->query("SELECT COUNT(*) FROM quizzes")->fetchColumn(),
            'donations'=>db()->query("SELECT SUM(amount) FROM donations WHERE status='success'")->fetchColumn()??0
        ]);
    }
    function quizzes(){
        view('admin/quizzes',['quizzes'=>db()->query("SELECT * FROM quizzes")->fetchAll()]);
    }
}
