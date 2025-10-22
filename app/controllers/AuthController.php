<?php namespace App\Controllers;
use App\Models\User;
class AuthController{
    function login(){
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $u=User::findByEmail($_POST['email']);
            if($u&&password_verify($_POST['password'],$u->password)){
                $_SESSION['user_id']=$u->id;$_SESSION['role']=$u->role;
                redirect('/dashboard');
            }$_SESSION['error']='Invalid credentials';
        }view('auth/login');
    }
    function register(){
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $id=User::create([
                'name'=>$_POST['name'],
                'email'=>$_POST['email'],
                'password'=>password_hash($_POST['password'],PASSWORD_DEFAULT)
            ]);
            $_SESSION['user_id']=$id;redirect('/dashboard');
        }view('auth/register');
    }
    function logout(){session_destroy();redirect('/');}
}
