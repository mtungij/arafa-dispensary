<?php

namespace App\Services;

class SmsService
{
    /**
     * Create a new class instance.
     */
   public function sendSms($phone, $message){
    $url = "https://sms-api.kadolab.com/api/send-sms";
    $token = getenv('SMS_TOKEN');

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '. $token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "phoneNumbers" => ["+$phone"],
        "message" => $message
    ]));

    $server_output = curl_exec($ch);
    curl_close($ch);

    return $server_output; // optional: return API response
}
  
}
