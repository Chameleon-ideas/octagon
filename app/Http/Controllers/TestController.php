<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPosts;
use App\Models\UserComments;
use App\Models\UserFollows;
use App\Models\UserLikes;
use App\Models\UserTag;
use App\Models\UserPostImageVideo;
use App\Models\UserImageVideo;
use App\Models\OtpVerification;
use App\Models\Report;
use App\Models\PostReport;
use App\Models\PostCategory;
use App\Models\PostFavorite;
use App\Models\UserSport;
use App\Models\UserTeam;
use App\Models\PostSave;
use App\Models\FireBase;
use App\Models\Sport;
use App\Models\Team;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Helpers\Helper;

class TestController extends Controller {

    public $successStatus = 200;
    public function commentList(Request $request) {
        $postId = 40;
        $comments = UserComments::where('post_id', $postId)->get()->toArray();

        echo "<pre>";
        //print_r($comments);
        echo "</pre>";

        $commentN = [];
        $parentIdArr = [];
        for($i=count($comments)-1;$i>=0;$i--) {
            if($comments[$i]['parent_comment_id'] > 0 ) {
                if(array_key_exists($comments[$i]['id'], $parentIdArr)) {
                    $parentIdArr[$comments[$i]['parent_comment_id']][] = $comments[$i];
                    $parentIdArr[$comments[$i]['parent_comment_id']]['comments'] = $parentIdArr[$comments[$i]['id']];
                } else {
                    $parentIdArr[$comments[$i]['parent_comment_id']][] = $comments[$i];
                }
              
            } 
        }

        for($i=count($comments)-1;$i>=0;$i--) {
            //if($comments[$i]['parent_comment_id'] == 0 ) {
              if(array_key_exists($comments[$i]['id'], $parentIdArr)) {
                $commentN[$comments[$i]['id']] = $comments[$i];
                $commentN[$comments[$i]['id']]['comments'] = $parentIdArr[$comments[$i]['id']]; 
              } else {
                $commentN[$comments[$i]['id']] = $comments[$i];
              }
            /*} else if($comments[$i]['parent_comment_id'] > 0 && array_key_exists($comments[$i]['id'], $parentIdArr)) {
                $commentN[$comments[$i]['id']] = $comments[$i];
                $commentN[$comments[$i]['id']]['comments'] = $parentIdArr[$comments[$i]['id']];
            }*/ 
        }


        echo "<pre>";
        print_r($parentIdArr);
        //print_r($commentN);
        echo "</pre>";

    }
}
?>