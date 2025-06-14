<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserGroupMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use Validator;
use Exception;
use DB;

class UserGroupMemberController extends Controller {

    public $successStatus = 200;

    public function saveUserGroupMember(Request $request) {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
        try {
             $validator = Validator::make($request->all(), [
                    'group_id' => 'required',
                    'user_id' => 'required',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                }
                $input = $request->all();

                $user = UserGroupMember::create($input);
                return response()->json(['success' => $user], $this->successStatus);
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

    }
    public function getGroupMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $userLogin = Auth::user();
        $getUserGroup = UserGroupMember::where('group_id', $request->group_id)->get()->toArray();
        if (isset($getUserGroup) && count($getUserGroup) > 0) {
            
            foreach ($getUserGroup as $key=>$iduser) {
                $getUser = User::where('id', $iduser['user_id'])->first();
                    $getUserGroup[$key]['user'] = [
                        'id' => $getUser->id,
                        'name' => $getUser->name,
                        'email' => $getUser->email,
                        'photo' => $getUser->photo,
                    ];
                }
        }
        return response()->json(['success' => $getUserGroup], $this->successStatus);
    }

    public function getGroupMemberById(Request $request)
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

    public function deleteGroupMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'group_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        if (isset($input['group_id']) && trim($input['group_id']) > 0) {
            $group_id = trim($input['group_id']);
            $user_id = trim($input['user_id']);
            UserGroupMember::where('group_id', $group_id)->where('user_id', $user_id)->delete();
            return response()->json(['success' => 'Deleted'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }
}
