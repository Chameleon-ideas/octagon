<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use Validator;
use Exception;
use DB;

class UserGroupController extends Controller {

    public $successStatus = 200;

    public function saveUserGroup(Request $request) {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
        try {
             $validator = Validator::make($request->all(), [
                    'title' => 'required',
                    'dates' => 'required',
                    'description' => 'required',
                    'is_public' => 'required',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                }
                $input = $request->all();

                $BASE_URL = Helper::checkServer()['BASE_URL'];
                $allowedfileExtension = ['pdf', 'jpg', 'png', 'JPG', 'PNG', 'jpeg', 'gif', 'GIF'];
                if (!$request->hasFile('photo')) {
                    $input['photo'] = $user->photo;
                } else {
                    $photo = $request->file('photo');
                    $extension = $photo->getClientOriginalExtension();
                    $check = in_array($extension, $allowedfileExtension);
                    if ($check) {

                        $filename = date('YmdHi') . $photo->getClientOriginalName();
                        $photo->move(public_path('image/profile'), $filename);
                        $input['photo'] = 'image/profile/' . $filename;
                    }
                }

                $user = UserGroup::create($input);
                return response()->json(['success' => $user], $this->successStatus);
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

    }
    public function getGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $userLogin = Auth::user();
        $getUserGroup = UserGroup::where('user_id', $request->user_id)->get()->toArray();
        
        return response()->json(['success' => $getUserGroup], $this->successStatus);
    }

    public function getGroupById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $userLogin = Auth::user();
        $getUserGroup = UserGroup::where('id', $request->group_id)->get()->toArray();
        
        return response()->json(['success' => $getUserGroup], $this->successStatus);
    }

    public function updateGroup(Request $request)
    {
        $user = Auth::user();
        $input = $request->all();
         $userUpdate = UserGroup::where('id', $input['group_id'])->first();
        //echo $input['bio'];
        //dd('test');
        $BASE_URL = Helper::checkServer()['BASE_URL'];
        $allowedfileExtension = ['pdf', 'jpg', 'png', 'JPG', 'PNG', 'jpeg', 'gif', 'GIF'];
        if (!$request->hasFile('photo')) {
            $userUpdate->photo = $user->photo;
        } else {
            $photo = $request->file('photo');
            $extension = $photo->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);
            if ($check) {

                $filename = date('YmdHi') . $photo->getClientOriginalName();
                $photo->move(public_path('image/profile'), $filename);
                $userUpdate->photo = 'image/profile/' . $filename;
            }
        }


        if (isset($input['title']) && trim($input['title']) != "") {
            $userUpdate->title = $input['title'];
        }

        if (isset($input['options']) && trim($input['options']) != "") {
            $userUpdate->options = $input['options'];
        }

        if (isset($input['dates']) && trim($input['dates']) != "") {
            $userUpdate->dates = $input['dates'];
        }

        if (isset($input['description']) && trim($input['description']) != "") {
            $userUpdate->description = $input['description'];
        }

        $userUpdate->update();
        return response()->json(['success' => $userUpdate], $this->successStatus);
    }
}
