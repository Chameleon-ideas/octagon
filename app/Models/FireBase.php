<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FireBase extends Model {

    public function curlCall($token, $title, $message) {

        $SERVER_API_KEY = 'AAAAeyvqWOE:APA91bEikAqlSZ2TL-fMcsPEjoneQ0b6d3vAL309wk7PXtYl-xtm52z45ebaDXyMUvzh-JiTlOoK-a8gVcPyM8BllGj7dWL6F7GoUlhCQm_iyRq0TPrMZsPnqcqGuC6Ko7coZ4Q-tHWX';
  
        $data = [
            "to" => $token,
            "notification" => [
                "title" => $title,
                "body" => $message,  
            ]
        ];

        $dataString = json_encode($data);
      
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
      
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
               
        $response = curl_exec($ch);
      
        curl_close($ch);
      
        return $response;

    }

}
