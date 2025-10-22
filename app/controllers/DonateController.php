<?php namespace App\Controllers;
class DonateController{
    function handle(){
        if($_SERVER['REQUEST_METHOD']!=='POST')redirect('/');
        // basic validation
        $amt=(float)($_POST['amount']??0);$gate=$_POST['gateway']??'';
        if($amt<=0)die('Invalid amount');
        // insert pending donation
        $ref=bin2hex(random_bytes(16));
        $stmt=db()->prepare("INSERT INTO donations (user_id,gateway,amount,currency,status,reference,created_at) VALUES (?,?,?,'NGN','pending',?,NOW())");
        $stmt->execute([$_SESSION['user_id']??null,$gate,$amt,$ref]);
        // redirect to gateway (demo uses Paystack)
        header("Location: https://paystack.com/pay/xxxxxxxxxxxxx"); // replace with your Paystack inline URL
        exit;
    }
}
