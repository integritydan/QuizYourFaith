<?php
namespace App\Controllers;
use App\Core\Controller;

class AccountController extends Controller{
    public function index(){ $this->view('account/index'); }
    public function settings(){ $this->view('account/settings'); }
    public function profile(){ $this->view('account/profile'); }
    public function donations(){ $this->view('account/donations'); }
}
