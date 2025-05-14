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
use App\Models\FireBase;
use Illuminate\Support\Facades\Auth;
use Validator;

class LikeController extends Controller {

    public $successStatus = 200;

    public function saveUserLikes(Request $request) {
        $validator = Validator::make($request->all(), [
            'like' => 'required',
            'content_id' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $user = Auth::user();
        if (isset($input['like'])) {
            $like = $input['like'];
            $content_id = $input['content_id'];
            $type = $input['type'];
            if($like == 1) {
                // Add like
                $f = 0;
                $likeUserId = 0;
                if($type == 1) {
                    $userPost = UserPosts::where('id', $content_id)
                        ->first();
                    if($userPost) {
                        $f = 1;
                        $likeUserId = $userPost->user_id;
                    }
                } else if($type == 2) {
                    $userComment = UserComments::where('id', $content_id)
                        ->first();
                    if($userComment) {
                        $f = 1;
                        $likeUserId = $userComment->user_id;
                    }
                }
                if($f == 1) {
                    $userLikes = UserLikes::where('user_id', $user->id)
                        ->where('content_id', $content_id)
                        ->where('type', $type)
                        ->first();
                    if(!$userLikes) {
                        $userLikesRecord = new UserLikes();
                        $userLikesRecord->user_id = $user->id;
                        $userLikesRecord->content_id = $content_id;
                        $userLikesRecord->type = $type;
                        $userLikesRecord->save();

                        $newNotification = new Notification();
                        $newNotification->user1 = $user->id;
                        $newNotification->user2 = $likeUserId;
                        $newNotification->type_id = $content_id;
                        if($type == 1) {
                            $notMessage = $user->name." likes your post";    
                        } else {
                            $notMessage = $user->name." likes your comment";
                        }
                        
                        $newNotification->notification = $notMessage;
                        $newNotification->type = 2;
                        $newNotification->save();

                        $userDetails = User::where('id', $likeUserId)->first();

                        $fireBase = new FireBase();
                        $fireBase->curlCall($userDetails->fcm_token, $notMessage, $notMessage);
                    }
                }
            } else if($like == 0){
                UserLikes::where('user_id', $user->id)
                        ->where('content_id', $content_id)
                        ->where('type', $type)
                        ->delete();
            }
            $fRecord1 = UserLikes::where('user_id', $user->id)
                        ->where('content_id', $content_id)
                        ->where('type', $type)
                        ->count();
            $record = [
                'likes' => $fRecord1
            ];
            return response()->json(['success' => $record], $this->successStatus);
            //echo "<pre>";print_r($sports);die;
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function getLikes(Request $request) {
        $validator = Validator::make($request->all(), [
            'content_id' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $user = Auth::user();
        if (isset($input['content_id']) && isset($input['type'])) {
            $content_id = $input['content_id'];
            $type = $input['type'];
            
            $userLikes = UserLikes::where('content_id', $content_id)
                ->where('type', $type)
                ->count();
            /*$user = User::with(['likes'])->where(function ($query) use ($content_id, $type) {
                $query->where('content_id', $content_id);
                $query->where('type', $type);
            })->get(); */   
            $invoices = User::with(array('likes' => function($query) use($content_id, $type) {
                $query->where('content_id', '=', $content_id)
                      ->where('type', '=', $type);
            }))->get();
            /*$user = User::with('likes')
                ->where('content_id', $content_id)
                ->where('type', $type)
                ->get();*/
            if($userLikes > 0) {
                $record = [
                    'likes' => $userLikes,
                    'user' => $user
                ];
                return response()->json(['success' => $record], $this->successStatus);
            } else {
                return response()->json(['error' => 'No Record Found'], $this->successStatus);
            }
            //echo "<pre>";print_r($sports);die;
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }
    public function getLikedPost() {
        $user = Auth::user();
        //echo $user->id;die;
        $postIds = UserLikes::where('user_id', $user->id)->where('type', 1)->get(['content_id']);
        $limit = 1;
        $page = 1;
        //$getUserPosts = UserPosts::whereIn('id', $postIds)->where('type', 1)->where('is_deleted', '0')->orderBy('updated_at', 'desc')->get()->toArray();
        //echo "<pre>";print_r($getUserPosts);die;
        $getPostQuery = UserPosts::whereIn('id', $postIds)->where('type', 1)->where('is_deleted', '0')->orderBy('updated_at', 'desc');
        $total_rows = $getPostQuery->count();
        $total_page = isset($input['limit']) ? $input['limit'] : $limit;
        $page_size = ceil($total_rows / $total_page);
        $currentpage = isset($input['page_no']) ? $input['page_no'] : $page;
        $offset = $currentpage * $total_page - $total_page;
        $getPostQuery->take($total_page)->offset($offset);
        $getUserPosts = $getPostQuery->get()->toArray();
        $more_page = false;
        if ($currentpage < $page_size) {
            $more_page = true;
        }
        if(count($getUserPosts) == 0) {
            return response()->json(['error' => 'Record not founds'], $this->successStatus);
        }
        $userPostIdArr = $finalArr = $userPostFilesArr = $userNameArr = $userPhotoArr = array();
        for ($p = 0; $p < count($getUserPosts); $p++) {
            $userPostIdArr[] = $getUserPosts[$p]['id'];
        }
        $getUserPostImage = UserPostImageVideo::whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
        for ($v = 0; $v < count($getUserPostImage); $v++) {
            $userPostFilesArr[$getUserPostImage[$v]['post_id']][] = $getUserPostImage[$v];
        }
        for ($pa = 0; $pa < count($getUserPosts); $pa++) {
            if (isset($userPostFilesArr[$getUserPosts[$pa]['id']])) {
                $getUserPosts[$pa]['videos'] = array();
                $getUserPosts[$pa]['images'] = array();
                $userPostFiles = $userPostFilesArr[$getUserPosts[$pa]['id']];
                for ($b = 0; $b < count($userPostFiles); $b++) {
                    if ($userPostFiles[$b]['type'] == "0") {
                        $getUserPosts[$pa]['images'][] = $userPostFiles[$b];
                    } elseif ($userPostFiles[$b]['type'] == "1") {
                        $getUserPosts[$pa]['videos'][] = $userPostFiles[$b];
                    }
                }
            } else {
                $getUserPosts[$pa]['videos'] = array();
                $getUserPosts[$pa]['images'] = array();
            }
            $finalArr[] = $getUserPosts[$pa];
        }
        return response()->json(['success' => $finalArr, 'total' => $total_rows, 'size' => $total_page, 'total_page' => $page_size, 'page' => $currentpage, 'more' => $more_page], $this->successStatus);
    }

}
