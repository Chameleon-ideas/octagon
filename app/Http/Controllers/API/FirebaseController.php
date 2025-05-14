<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sport;
use App\Models\User;
use App\Models\UserLikes;
use App\Models\UserPosts;
use App\Models\UserComments;
use App\Models\UserPostImageVideo;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Validator;

class FirebaseController extends Controller {

    public $successStatus = 200;

    public function sendNotification(Request $request) {

        $SERVER_API_KEY = 'AAAAeyvqWOE:APA91bEikAqlSZ2TL-fMcsPEjoneQ0b6d3vAL309wk7PXtYl-xtm52z45ebaDXyMUvzh-JiTlOoK-a8gVcPyM8BllGj7dWL6F7GoUlhCQm_iyRq0TPrMZsPnqcqGuC6Ko7coZ4Q-tHWX';
    
        /*$data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,  
            ]
        ];*/

        $data = [
            "to" => "ccdSN92lRnKREv-Jd3v72Y:APA91bG82XvDy3kVRN9As0tpI6yo-lv1RTUqaxRqde7cxZrgz7tKu2yBU12AX_7YyKdyqCzhwWxYfXiZc1PNi64kLxKg4RrLwpH3u-EmBwQZVFaHMVnV0HXnNh9gpVEzK4qa7-VAOqU4",
            "notification" => [
                "title" => "test notification",
                "body" => "this is test body",  
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

        dd($response);
    }

}
