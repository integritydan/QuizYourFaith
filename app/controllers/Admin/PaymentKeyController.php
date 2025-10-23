<?php
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Models\PaymentKey;

class PaymentKeyController extends Controller{
    public function index(){
        $this->view('admin/payment_keys',[
            'paystack'=>PaymentKey::get('paystack'),
            'paypal'  =>PaymentKey::get('paypal'),
            'flutterwave'=>PaymentKey::get('flutterwave')
        ]);
    }
    public function save(){
        foreach(['paystack','paypal','flutterwave'] as $g){
            PaymentKey::set(
                $g,
                $_POST[$g.'_public']??'',
                $_POST[$g.'_secret']??'',
                $_POST[$g.'_encrypt']??'',
                isset($_POST[$g.'_sandbox'])?1:0,
                isset($_POST[$g.'_active'])?1:0
            );
        }
        redirect('/admin/payment-keys?saved=1');
    }
}
