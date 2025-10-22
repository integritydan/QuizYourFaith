<?php namespace App\Controllers;
class UserController{
    function dashboard(){
        view('user/dashboard',[
            'user'=>db()->prepare("SELECT * FROM users WHERE id=?")->execute([$_SESSION['user_id']])->fetch(),
            'stats'=>db()->prepare("SELECT COUNT(*) AS taken,SUM(is_correct)/COUNT(*) AS acc FROM answers WHERE user_id=?")->execute([$_SESSION['user_id']])->fetch()
        ]);
    }
}
