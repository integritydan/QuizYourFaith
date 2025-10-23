<?php
namespace App\Models;
use App\Core\DB;

class PaymentKey{
    private static function crypt($data,$encrypt=true){
        $key=$_ENV['APP_KEY']??'QuizYourFaithAppKey2025!';
        return $encrypt?base64_encode(openssl_encrypt($data,'AES-256-ECB',$key)):
                         openssl_decrypt(base64_decode($data),'AES-256-ECB',$key);
    }
    public static function get($gateway){
        $row=DB::table('payment_keys')->where('gateway',$gateway)->first();
        if(!$row) return null;
        return [
            'public'  => $row['public_key'] ? self::crypt($row['public_key'],false) : '',
            'secret'  => $row['secret_key'] ? self::crypt($row['secret_key'],false) : '',
            'encrypt' => $row['encrypt_key']? self::crypt($row['encrypt_key'],false): '',
            'sandbox' => (bool)$row['sandbox_mode'],
            'active'  => (bool)$row['active']
        ];
    }
    public static function set($gateway,$public,$secret,$encrypt='',$sandbox=0,$active=0){
        DB::table('payment_keys')->where('gateway',$gateway)->update([
            'public_key'  => self::crypt($public),
            'secret_key'  => self::crypt($secret),
            'encrypt_key' => self::crypt($encrypt),
            'sandbox_mode'=>$sandbox,
            'active'=>$active
        ]);
    }
}
