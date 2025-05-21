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

class NotificationController extends Controller {

    public $successStatus = 200;

    public function getNotification(Request $request) {
        $user = Auth::user();
        /**
         * commented because after this api call we are deleting all the tokens thus other api calls will not work
         */
        // if($user->is_deleted == 0) {
        // } else {
        //      Auth::user()->tokens->each(function($token, $key) {
        //         $token->delete();
        //     });
        // }
        $limit = 10;
        $page = 1;
        $data = Notification::where('user2', $user->id)->where('status', 'N')->orderBy('created_at', 'desc');
        $total_rows = $data->count();
        $total_page = isset($input['limit']) ? $input['limit'] : $limit;
        $page_size = ceil($total_rows / $total_page);
        $currentpage = isset($input['page_no']) ? $input['page_no'] : $page;
        $offset = $currentpage * $total_page - $total_page;
        //$data->take($total_page)->offset($offset);
        $getNotification = $data->take($total_page)->offset($offset)->get()->toArray();
        $more_page = false;
        if ($currentpage < $page_size) {
            $more_page = true;
        }
        if(count($getNotification) > 0) {
            $count = count($getNotification);
            for($i=0;$i<count($getNotification);$i++) {
                if($getNotification[$i]['type'] == 1) {
                    $getNotification[$i]['user'] = User::where('id', $getNotification[$i]['user1'])->get()->toArray();
                    //$data[$i]['post'] = array();
                } else {
                    $getNotification[$i]['user'] = User::where('id', $getNotification[$i]['user1'])->get()->toArray();
                    //$data[$i]['post'] = UserPosts::where('id', $data[$i]['type_id'])->get()->toArray();
                }
            }
            $record = [
                'count' => $count,
                'notification' => $getNotification
            ];
            return response()->json(['success' => $record, 'total' => $total_rows, 'size' => $total_page, 'total_page' => $page_size, 'page' => $currentpage, 'more' => $more_page], $this->successStatus);
        } else {
            return response()->json(['error' => 'No Record Found'], $this->successStatus);
        }
        //echo "<pre>";print_r($sports);die;
        
    }
    public function updateNotification(Request $request) {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        $data = Notification::where('id', $input['notification_id'])->get()->toArray();
            
        if(count($data) > 0) {
            $updateData = [
                'status' => 'Y'
            ];
            Notification::where('id', $input['notification_id'])->update($updateData);
            return response()->json(['success' => 'Notification Read by user'], $this->successStatus);
        } else {
            return response()->json(['error' => 'No Record Found'], $this->successStatus);
        }
    }
}
