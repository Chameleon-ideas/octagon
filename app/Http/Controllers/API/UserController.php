<?php

namespace App\Http\Controllers\Api;

use PHPMailer\PHPMailer\PHPMailer;

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
use App\Models\Sport;
use App\Models\Team;
use App\Models\Notification;
use App\Models\UserBlock;
use App\Models\UserGroupMember;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public $successStatus = 200;

    public function socialAuth(Request $request)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'nullable|string',
            'email' => 'nullable|email',
            'social_id' => 'required|string',
            'mobile' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Check if the social ID exists in the database
        $user = User::where('social_id', $request->social_id)->first();
        if ($user) {
            // Update fcm_token if provided
            if ($request->filled('fcm_token')) {
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }

            // Generate access token
            $user->tokens()->delete();
            $user->token = $user->createToken('MyLaravelApp')->accessToken;

            $user->userId = $user->id;

            $getUserSport = UserSport::where('user_id', $user->id)->orderBy('created_at', 'desc')->get('sport_id')->toArray();
            if (count($getUserSport) > 0) {
                $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
                for ($i = 0; $i < count($sportInfo); $i++) {
                    $getTeam = UserTeam::where('user_id', $user->id)->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                    if (count($getTeam) > 0) {
                        $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                        if (!empty($teamDetails)) {
                            foreach ($teamDetails as $tKey => $teamDetail) {
                                $teamDetails[$tKey]['strTeamLogo'] = asset($teamDetail['strTeamLogo']);
                            }
                        }
                        $sportInfo[$i]['team'] = $teamDetails;
                    } else {
                        $sportInfo[$i]['team'] = array();
                    }
                }

                $user->sport_info = $sportInfo;
            }

            return response()->json(['success' => $user], 200);
        }

        // Check if the email exists in the database
        $user = User::where('email', $request->email)->first();
        if ($user) {
            // Update fcm_token if provided
            if ($request->filled('fcm_token')) {
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }

            // Generate access token
            $user->token = $user->createToken('MyLaravelApp')->accessToken;

            $user->userId = $user->id;

            return response()->json(['success' => $user], 200);
        }

        // If neither social ID nor email exists, proceed with registration
        $user = User::create([
            'social_id' => $request->social_id,
            'email' => $request->email,
            'fcm_token' => $request->fcm_token,
            'name' => $request->email,
            'mobile' => $request->mobile
        ]);

        // Generate access token for the newly registered user
        $user->token = $user->createToken('MyLaravelApp')->accessToken;
        $user->user_id = $user->id;

        return response()->json(['success' => $user], 201);
    }



    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|max:10'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $checkDelete = '0';
        if (Auth::attempt(['email' => request('email'), 'password' => request('password'), 'is_deleted' => $checkDelete])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyLaravelApp')->accessToken;
            $success['userId'] = $user->id;
            if ($request->get('fcm_token') != null) {
                //dd('test');
                $updateArr = array();
                $updateArr['fcm_token'] = $request->get('fcm_token');
                User::where('email', request('email'))->update($updateArr);
                $getUserDetails = User::where('email', request('email'))
                    ->where('is_deleted', '0')
                    ->get(['id', 'email', 'name', 'mobile', 'gender', 'photo', 'background', 'dob', 'bio', 'country'])
                    ->toArray();
                if (count($getUserDetails) > 0) {
                    $getUserSport = UserSport::where('user_id', $getUserDetails[0]['id'])->orderBy('created_at', 'desc')->get('sport_id')->toArray();
                    if (count($getUserSport) > 0) {
                        $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
                        for ($i = 0; $i < count($sportInfo); $i++) {
                            $getTeam = UserTeam::where('user_id', $getUserDetails[0]['id'])->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                            if (count($getTeam) > 0) {
                                $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                                if (!empty($teamDetails)) {
                                    foreach ($teamDetails as $tKey => $teamDetail) {
                                        $teamDetails[$tKey]['strTeamLogo'] = asset($teamDetail['strTeamLogo']);
                                    }
                                }
                                $sportInfo[$i]['team'] = $teamDetails;
                            } else {
                                $sportInfo[$i]['team'] = array();
                            }
                        }
                        $success['sport_info'] = $sportInfo;
                    } else {
                        $success['sport_info'] = array();
                    }


                    $success['name'] = $getUserDetails[0]['name'];
                    $success['email'] = $getUserDetails[0]['email'];
                    $success['mobile'] = $getUserDetails[0]['mobile'];
                    $success['gender'] = $getUserDetails[0]['gender'];
                    $success['photo'] = $getUserDetails[0]['photo'];
                    $success['background'] = $getUserDetails[0]['background'];
                    $success['dob'] = $getUserDetails[0]['dob'];
                    $success['bio'] = $getUserDetails[0]['bio'];
                    $success['country'] = $getUserDetails[0]['country'];
                } else {
                    //dd('asd');
                    $statusValue = array('1', '2');
                    $getUserStatus = User::where('email', request('email'))
                        ->whereIn('is_deleted', $statusValue)
                        ->orderBy('id', 'desc')
                        ->get()
                        ->toArray();
                    if (count($getUserStatus) > 0) {
                        if ($getUserStatus[0]['is_deleted'] == 1) {
                            return response()->json(['error' => 'Your account is inactive now!'], $this->successStatus);
                        } else if ($getUserStatus[0]['is_deleted'] == 2) {
                            return response()->json(['error' => 'Your account is deleted!'], $this->successStatus);
                        }
                    }
                }
                //echo "<pre>";print_r($success);die;
                $success['fcm_token'] = $request->get('fcm_token');
            }
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised account!'], $this->successStatus);
        }
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        //$input = $request->all();
        //echo "<pre>";print_r($input);die;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'gender' => 'required',
            'country' => 'required',
            'user_type' => 'required',
            'password' => 'required|min:6|max:10',
            'c_password' => 'required|min:6|max:10|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['mobile'] = isset($input['mobile']) ? trim($input['mobile']) : '';
        $checkUserData = User::where('email', $input['email'])
            ->whereIn('is_deleted', ['0', '1'])
            ->first();
        if ($checkUserData) {
            return response()->json(['error' => 'You have already acount!'], $this->successStatus);
        }
        $user = User::create($input);
        //dd($user->id);
        $this->sendOtpMail($user->id, $input['mobile'], $input['email'], $input['name']);
        $success['token'] = $user->createToken('MyLaravelApp')->accessToken;
        $success['name'] = $user->name;
        return response()->json(['success' => $success], $this->successStatus);
    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function userDetails()
    {
        $user = Auth::user();
        $record = $this->getFollowers($user->id);
        $record['user'] = $user;
        $getUserSport = UserSport::where('user_id', $user->id)->orderBy('created_at', 'desc')->get('sport_id')->toArray();
        if (count($getUserSport) > 0) {
            $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
            for ($i = 0; $i < count($sportInfo); $i++) {
                $getTeam = UserTeam::where('user_id', $user->id)->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                if (count($getTeam) > 0) {
                    $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                    if (!empty($teamDetails)) {
                        foreach ($teamDetails as $tKey => $teamDetail) {
                            $teamDetails[$tKey]['strTeamLogo'] = asset($teamDetail['strTeamLogo']);
                        }
                    }
                    $sportInfo[$i]['team'] = $teamDetails;
                } else {
                    $sportInfo[$i]['team'] = array();
                }
            }
            $record['sport_info'] = $sportInfo;
        } else {
            $record['sport_info'] = array();
        }
        $post = $this->getUserPostCount($user->id);
        $record['post_count'] = $post[1];
        //$record['post'] = $post[0];
        $favoritePost = $this->getUserFavoritePostCount($user->id);
        $record['favorite_post_count'] = $favoritePost[1];
        //$record['favorite_post'] = $favoritePost[0];
        $savePost = $this->getUserSavePostCount($user->id);
        $record['save_post_count'] = $savePost[1];
        //$record['save_post'] = $savePost[0];
        $userLikesPost = $this->getUserLikePostCount($user->id);
        $record['like_post_count'] = $userLikesPost[1];
        //$record['like_post'] = $userLikesPost[0];
        return response()->json(['success' => $record], $this->successStatus);
    }

    public function userProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $userLogin = Auth::user();
        $checkUserBlock = UserBlock::where('user_id', $request->user_id)
            ->where('block_user_id', $userLogin->id)
            ->first();
        if ($checkUserBlock) {
            return response()->json(['error' => 'This user is block'], 200);
        }
        $user = User::find($request->user_id);
        $record = $this->getFollowers($request->user_id);
        $record['user'] = $user;
        $getUserSport = UserSport::where('user_id', $request->user_id)->orderBy('created_at', 'desc')->get('sport_id')->toArray();
        if (count($getUserSport) > 0) {
            $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
            for ($i = 0; $i < count($sportInfo); $i++) {
                $getTeam = UserTeam::where('user_id', $request->user_id)->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                if (count($getTeam) > 0) {
                    $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                    if (!empty($teamDetails)) {
                        foreach ($teamDetails as $tKey => $teamDetail) {
                            $teamDetails[$tKey]['strTeamLogo'] = asset($teamDetail['strTeamLogo']);
                        }
                    }
                    $sportInfo[$i]['team'] = $teamDetails;
                } else {
                    $sportInfo[$i]['team'] = array();
                }
            }
            $record['sport_info'] = $sportInfo;
        } else {
            $record['sport_info'] = array();
        }
        $post = $this->getUserPostCount($request->user_id);
        $record['post_count'] = $post[1];
        //$record['post'] = $post[0];
        $favoritePost = $this->getUserFavoritePostCount($user->id);
        $record['favorite_post_count'] = $favoritePost[1];
        //$record['favorite_post'] = $favoritePost[0];
        $savePost = $this->getUserSavePostCount($request->user_id);
        $record['save_post_count'] = $savePost[1];
        //$record['save_post'] = $savePost[0];
        $userLikesPost = $this->getUserLikePostCount($request->user_id);
        $record['like_post_count'] = $userLikesPost[1];
        //$record['like_post'] = $userLikesPost[0];
        return response()->json(['success' => $record], $this->successStatus);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        //echo "<pre>";print_r($input);die;
        $getUserDetails = User::where('email', trim($input['email']))
            ->where('is_deleted', '0')
            ->get(['id', 'email', 'mobile', 'name'])
            ->toArray();
        if (count($getUserDetails) > 0) {
            $email_id = $getUserDetails[0]['email'];
            $user_id = $getUserDetails[0]['id'];
            $mobile = $getUserDetails[0]['mobile'];
            $name = $getUserDetails[0]['name'];
            $response = $this->sendOtpMail($user_id, $mobile, $email_id, $name, 1);
            if ($response == 1) {
                return response()->json(['success' => "New password sent in email."], $this->successStatus);
            } else {
                return response()->json(['success' => $response], $this->successStatus);
            }
        } else {
            return response()->json(['error' => 'User data not found'], $this->successStatus);
        }
    }

    public function otpCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        //echo "<pre>";print_r($input);die;
        $getUserDetails = User::where('email', trim($input['email']))
            ->whereIn('is_deleted', ['0', '1'])
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'email', 'mobile'])
            ->toArray();
        if (count($getUserDetails) > 0) {
            $randOtp = rand(100000, 999999);
            $email_id = $getUserDetails[0]['email'];
            $user_id = $getUserDetails[0]['id'];
            $mobile = $getUserDetails[0]['mobile'];
            $response = $this->sendOtpMail($user_id, $mobile, $email_id, $getUserDetails[0]['name']);
            if ($response == 1) {
                return response()->json(['success' => 'Otp sent in email'], $this->successStatus);
            } else {
                return response()->json(['success' => $response], $this->successStatus);
            }
        } else {
            return response()->json(['error' => 'User data not found'], $this->successStatus);
        }
    }

    public function otpVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'otp' => 'required',
            'fcm_token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $otp = $mobile = $email = "";
        if (isset($input['otp']) && trim($input['otp']) != "") {
            $otp = trim($input['otp']);
        }
        if (isset($input['mobile']) && trim($input['mobile']) != "") {
            $mobile = trim($input['mobile']);
        }
        if (isset($input['email']) && trim($input['email']) != "") {
            $email = trim($input['email']);
        }
        if ($otp != "" && $email != "") {
            $getUserDetails = OtpVerification::where('email', $input['email'])
                ->orderBy('id', 'desc')
                ->get(['id', 'mobile', 'otp', 'email'])
                ->first();
            if (!empty($getUserDetails)) {
                $getUserDetails = json_decode($getUserDetails, true);
            }
            //echo "<pre>";print_r($getUserDetails);die;
            if (isset($getUserDetails['otp']) && trim($getUserDetails['otp']) == $otp) {
                $checkUserActivation = User::where('email', $getUserDetails['email'])
                    ->where('mobile', $getUserDetails['mobile'])
                    ->whereIn('is_deleted', ['0', '1'])
                    ->first();
                if ($checkUserActivation) {
                    $updateArr['is_deleted'] = '0';
                    User::where('email', $getUserDetails['email'])
                        ->where('mobile', $getUserDetails['mobile'])
                        ->where('id', $checkUserActivation->id)
                        ->update($updateArr);
                    $checkDelete = '0';
                    $loginRequest = User::where(['email' => $input['email']])->first();
                    if (!empty($loginRequest)) {
                        Auth::loginUsingId($loginRequest->id);
                        $user = Auth::user();
                        $success['token'] = $user->createToken('MyLaravelApp')->accessToken;
                        $success['userId'] = $user->id;
                        //dd($success);
                        //echo "<pre>";print_r($request->get('fcm_token'));die;
                        if ($request->get('fcm_token') != null) {
                            //dd('test');
                            $updateArr = array();
                            $updateArr['fcm_token'] = $request->get('fcm_token');
                            User::where('email', request('email'))->update($updateArr);
                            $getUserDetails = User::where('email', request('email'))
                                ->where('is_deleted', '0')
                                ->get(['id', 'email', 'name', 'mobile', 'gender', 'photo', 'background', 'dob', 'bio', 'country'])
                                ->toArray();
                            if (count($getUserDetails) > 0) {
                                $getUserSport = UserSport::where('user_id', $getUserDetails[0]['id'])->orderBy('created_at', 'desc')->get('sport_id')->toArray();
                                if (count($getUserSport) > 0) {
                                    $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
                                    for ($i = 0; $i < count($sportInfo); $i++) {
                                        $getTeam = UserTeam::where('user_id', $getUserDetails[0]['id'])->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                                        if (count($getTeam) > 0) {
                                            $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                                            if (!empty($teamDetails)) {
                                                foreach ($teamDetails as $tKey => $teamDetail) {
                                                    $teamDetails[$tKey]['strTeamLogo'] = asset($teamDetail['strTeamLogo']);
                                                }
                                            }
                                            $sportInfo[$i]['team'] = $teamDetails;
                                        } else {
                                            $sportInfo[$i]['team'] = array();
                                        }
                                    }
                                    $success['sport_info'] = $sportInfo;
                                } else {
                                    $success['sport_info'] = array();
                                }
                                /*for($i=0;$i<count($getUserSport);$i++) {
                                    $getUserTeam = UserTeam::with('teamInfo')->where('user_id', $getUserDetails[0]['id'])->where('sport_id', $getUserSport[$i]['sport_id'])->where('sport_api_id', $getUserSport[$i]['sport_api_id'])->orderBy('created_at', 'desc')->get()->toArray();
                                    if(count($getUserTeam) > 0){
                                        $success['sport_info']['team_info'] = $getUserTeam;
                                    }else{
                                        $success['sport_info']['team_info'] = array();
                                    }
                                }*/
                                ///$getUserTeam = UserTeam::with('teamInfo')->where('user_id', $getUserDetails[0]['id'])->where('')->orderBy('created_at', 'desc')->get()->toArray();

                                //echo "<pre>";print_r($success);die;
                                $success['name'] = $getUserDetails[0]['name'];
                                $success['email'] = $getUserDetails[0]['email'];
                                $success['mobile'] = $getUserDetails[0]['mobile'];
                                $success['gender'] = $getUserDetails[0]['gender'];
                                $success['photo'] = $getUserDetails[0]['photo'];
                                $success['background'] = $getUserDetails[0]['background'];
                                $success['dob'] = $getUserDetails[0]['dob'];
                                $success['bio'] = $getUserDetails[0]['bio'];
                                $success['country'] = $getUserDetails[0]['country'];
                            } else {
                                //dd('asd');
                                $statusValue = array('1', '2');
                                $getUserStatus = User::where('email', request('email'))
                                    ->whereIn('is_deleted', $statusValue)
                                    ->orderBy('id', 'desc')
                                    ->get()
                                    ->toArray();
                                if (count($getUserStatus) > 0) {
                                    if ($getUserStatus[0]['is_deleted'] == 1) {
                                        return response()->json(['error' => 'Your account is inactive now!'], $this->successStatus);
                                    } else if ($getUserStatus[0]['is_deleted'] == 2) {
                                        return response()->json(['error' => 'Your account is deleted!'], $this->successStatus);
                                    }
                                }
                            }
                            //echo "<pre>";print_r($success);die;
                            $success['fcm_token'] = $request->get('fcm_token');
                        }
                        return response()->json(['success' => $success], $this->successStatus);
                    } else {
                        return response()->json(['error' => 'Unauthorised account!'], $this->successStatus);
                    }
                } else {
                    return response()->json(['success' => 'Your account is deleted. Contact to support.'], $this->successStatus);
                }
            } else {
                return response()->json(['error' => 'You have entered wrong OTP.'], $this->successStatus);
            }
        } else {
            return response()->json(['error' => 'Please enter required value.'], $this->successStatus);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'mobile' => 'required|digits:10',
            'email' => 'required',
            'password' => 'required|min:6|max:10',
            'cpassword' => 'required|min:6|max:10',
            'password' => 'min:6|max:10|required_with:password|same:cpassword',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $password = $cpassword = $mobile = $email = "";
        if (isset($input['password']) && trim($input['password']) != "") {
            $password = trim($input['password']);
        }
        if (isset($input['cpassword']) && trim($input['cpassword']) != "") {
            $cpassword = trim($input['cpassword']);
        }
        if (isset($input['mobile']) && trim($input['mobile']) != "") {
            $mobile = trim($input['mobile']);
        }
        if (isset($input['email']) && trim($input['email']) != "") {
            $email = trim($input['email']);
        }
        if ($password != "" && $email != "" && $cpassword != "" && $password == $cpassword) {
            $updateArr = array();
            $updateArr['password'] = bcrypt($password);
            User::where('email', $email)->update($updateArr);
            return response()->json(['success' => 'Your password reset now!'], $this->successStatus);
        } else {
            return response()->json(['error' => 'Please enter required value.'], $this->successStatus);
        }
    }

    public function userProfileUpdate(Request $request)
    {
        $user = Auth::user();
        $userUpdate = User::find($user->id);
        $input = $request->all();
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

        if (!$request->hasFile('background')) {
            $userUpdate->background = $user->background;
        } else {
            $background = $request->file('background');
            $extension = $background->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);
            if ($check) {
                $filename = date('YmdHi') . $background->getClientOriginalName();
                $background->move(public_path('image/background'), $filename);
                $userUpdate->background = 'image/background/' . $filename;
            }
        }

        if (isset($input['bio']) && trim($input['bio']) != "") {
            $userUpdate->bio = $input['bio'];
        }

        if (isset($input['dob']) && trim($input['dob']) != "") {
            $userUpdate->dob = $input['dob'];
        }

        if (isset($input['name']) && trim($input['name']) != "") {
            $userUpdate->name = $input['name'];
        }

        if (isset($input['gender']) && trim($input['gender']) != "") {
            $userUpdate->gender = $input['gender'];
        }

        if (isset($input['country']) && trim($input['country']) != "") {
            $userUpdate->country = $input['country'];
        }

        $userUpdate->update();
        return response()->json(['success' => $userUpdate], $this->successStatus);
    }

    public function saveUserPost(Request $request)
    {
        $now = Carbon::now('UTC');
        $validator = Validator::make($request->all(), [
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        //echo "<pre>";print_r($input);die;
        if (isset($input['type']) && trim($input['type']) != "") {
            $BASE_URL = Helper::checkServer()['BASE_URL'];
            $insertArr = array();
            $insertArr['user_id'] = $user->id;
            $insertArr['title'] = isset($input['title']) ? trim($input['title']) : "";
            $insertArr['post'] = isset($input['post']) ? trim($input['post']) : "";
            $insertArr['type'] = $input['type'];    // 1-Post, 2-Story, 3-Reels
            $insertArr['location'] = $input['location'];
            $insertArr['comment'] = $input['comment'];    // 1-On, 2-Off
            // $insertArr['created_at'] = date("Y-m-d H:i:s");

            $insertArr['created_at'] = $now->format('Y-m-d H:i:s') . $now->format('P'); // Appends the UTC offset
            // $insertArr['created_at'] = Carbon::now('UTC')->format('Y-m-d H:i:s');
            //echo "<pre>";print_r($insertArr);die;
            $postId = UserPosts::create($insertArr)->id;
            $shareName = "post";
            if ($input['type'] == 2) {
                $shareName = "story";
            }
            if ($input['type'] == 3) {
                $shareName = "reels";
            }
            $updateArr = array();
            $updateArr['share_url'] = $shareName . "?id=" . base64_encode($postId);
            $insertArr['share_url'] = $shareName . "?id=" . base64_encode($postId);
            UserPosts::where('id', $postId)->update($updateArr);
            if (isset($input['tag_people']) && count($input['tag_people']) > 0) {
                $tagPeople = [];
                foreach ($input['tag_people'] as $tag) {
                    $tagPeople[] = [
                        'user_id' => $tag,
                        'post_id' => $postId,
                        'created_at' => $now->format('Y-m-d H:i:s') . $now->format('P')
                    ];
                }
                UserTag::insert($tagPeople);
            }
            if (isset($input['category']) && count($input['category']) > 0) {
                $category = [];
                foreach ($input['category'] as $idSport) {
                    $category[] = [
                        'post_id' => $postId,
                        'idSport' => $idSport,
                        'created_at' => $now->format('Y-m-d H:i:s') . $now->format('P')
                    ];
                }
                PostCategory::insert($category);
            }

            $allowedfileExtension = ['pdf', 'jpg', 'png', 'JPG', 'PNG', 'jpeg', 'gif', 'GIF'];
            if (!$request->hasFile('photo')) {
                //Not Found
            } else {
                foreach ($request->file('photo') as $im) {
                    $extension = $im->getClientOriginalExtension();
                    $check = in_array($extension, $allowedfileExtension);
                    if ($check) {
                        $filename = date('YmdHi') . $im->getClientOriginalName();
                        $im->move(public_path('image/post'), $filename);
                        $insertFileArr = array();
                        $insertFileArr['user_id'] = $user->id;
                        $insertFileArr['post_id'] = trim($postId);
                        $insertFileArr['created_at'] = $now->format('Y-m-d H:i:s') . $now->format('P');
                        $insertFileArr['file_path'] = 'image/post/' . $filename;
                        $insertFileArr['type'] = "0";
                        //echo "<pre>";print_r($insertFileArr);die;
                        $imageId = UserPostImageVideo::create($insertFileArr)->id;
                        $updateArr = array();
                        $updateArr['share_url'] =  $shareName . "/image?id=" . base64_encode($imageId);
                        UserPostImageVideo::where('id', $imageId)->update($updateArr);
                    }
                }
            }


            if ($request->hasFile('video')) {
                $videos = $request->file('video');

                // Ensure directory exists
                if (!file_exists(public_path('video/post'))) {
                    mkdir(public_path('video/post'), 0755, true);
                }

                // Check if $videos is a single file or an array of files
                if (is_array($videos)) {
                    // Handle multiple file uploads
                    foreach ($videos as $iv) {
                        // Generate a unique filename
                        $filename = date('YmdHi') . '_' . $iv->getClientOriginalName();

                        try {
                            // Move the file to the destination
                            $iv->move(public_path('video/post'), $filename);


                            // Generate a thumbnail from the video (image extracted)
                            $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
                            $thumbnailPath = public_path('video/post/') . $thumbnailFilename;

                            // Initialize FFMpeg to generate the thumbnail
                            $ffmpeg = FFMpeg::create();
                            $video = $ffmpeg->open(public_path('video/post/' . $filename));

                            // Extract a frame (e.g., at 5 seconds into the video)
                            $video->frame(TimeCode::fromSeconds(5))->save($thumbnailPath);

                            // Insert file information into the database
                            $insertFileArr = [
                                'user_id' => $user->id,
                                'post_id' => trim($postId),
                                'created_at' => $now->format('Y-m-d H:i:s') . $now->format('P'),
                                'file_path' => 'video/post/' . $filename,
                                'type' => "1"
                            ];

                            $videoId = UserPostImageVideo::create($insertFileArr)->id;
                            $updateArr = ['share_url' => $shareName . "/video?id=" . base64_encode($videoId)];
                            UserPostImageVideo::where('id', $videoId)->update($updateArr);
                        } catch (\Exception $e) {
                            return response()->json(['error' => 'Failed to upload video'], 500);
                        }
                    }
                } else {
                    // Handle single file upload
                    $filename = date('YmdHi') . '_' . $videos->getClientOriginalName();

                    try {
                        // Move the file to the destination
                        $videos->move(public_path('video/post'), $filename);

                        // Generate a thumbnail from the video (image extracted)
                        $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
                        $thumbnailPath = public_path('video/post/') . $thumbnailFilename;

                        // Initialize FFMpeg to generate the thumbnail
                        $ffmpeg = FFMpeg::create();
                        $video = $ffmpeg->open(public_path('video/post/' . $filename));

                        // Extract a frame (e.g., at 5 seconds into the video)
                        $video->frame(TimeCode::fromSeconds(5))->save($thumbnailPath);
                        $thumb_url = 'video/post/' . $thumbnailFilename;
                        // Insert file information into the database
                        $insertFileArr = [
                            'user_id' => $user->id,
                            'post_id' => trim($postId),
                            'created_at' => $now->format('Y-m-d H:i:s') . $now->format('P'),
                            'file_path' => 'video/post/' . $filename,
                            'type' => "1",
                            'thumb_url' => $thumb_url
                        ];


                        $videoId = UserPostImageVideo::create($insertFileArr)->id;
                        $updateArr = ['share_url' => $shareName . "/video?id=" . base64_encode($videoId)];
                        UserPostImageVideo::where('id', $videoId)->update($updateArr);
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Failed to upload video'], 500);
                    }
                }
            }

            $insertArr['share_url'] = asset($insertArr['share_url']);
            return response()->json(['success' => $insertArr], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function saveUserComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'comment' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        //echo "<pre>";print_r($input);die;
        if (isset($input['post_id']) && trim($input['post_id']) > 0) {
            $insertArr = array();
            $insertArr['user_id'] = $user->id;
            $insertArr['post_id'] = trim($input['post_id']);
            $insertArr['parent_comment_id'] = trim(isset($input['parent_comment_id']) ? $input['parent_comment_id'] : 0);
            $insertArr['comment'] = trim($input['comment']);
            $insertArr['created_at'] = date("Y-m-d H:i:s");
            //echo "<pre>";print_r($insertArr);die;
            UserComments::create($insertArr);

            $postUser = UserPosts::where('id', trim($input['post_id']))->first();

            // check postdetails with block user

            $checkUserBlock = UserBlock::where('user_id', $postUser->user_id)
                ->where('block_user_id', $user->id)
                ->first();
            if ($checkUserBlock) {
                return response()->json(['error' => 'This user is block now!'], 200);
            }

            $newNotification = new Notification();
            $newNotification->user1 = $user->id;
            $newNotification->user2 = $postUser->user_id;
            $newNotification->type_id = trim($input['post_id']);
            $notMessage = $user->name . " comments on your post";
            $newNotification->notification = $notMessage;
            $newNotification->type = 3;
            $newNotification->save();

            $updateArr = array();
            $updateArr['updated_at'] = date("Y-m-d H:i:s");
            UserPosts::where('id', trim($input['post_id']))->update($updateArr);
            return response()->json(['success' => $insertArr], $this->successStatus);
        } else {
            return response()->json(['error' => 'Please select post.'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function getUserPosts(Request $request)
    {
        // Validate that the 'type' field is present in the request
        $validator = Validator::make($request->all(), [
            'type' => 'required'
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // Set default values for pagination (10 posts per page, starting from page 1)
        $limit = 10;
        $page = 1;

        // Get the authenticated user
        $user = Auth::user();
        $user1 = Auth::user();

        // If the user is not deleted (is_deleted == 0), do nothing
        // Otherwise, delete all the user's tokens (logging them out)
        if ($user->is_deleted == 0) {
            // No action taken
        } else {
            Auth::user()->tokens->each(function ($token, $key) {
                $token->delete();
            });
        }

        // Flag to check if the user ID is manually set in the request
        $flagUser = 0;

        // If a user_id is passed in the request, override the authenticated user's ID and set the flag
        if ($request->user_id) {
            $user->id = $request->user_id;
            $flagUser = 1;
        }

        // Check if the 'type' is valid (1, 2, or 3), indicating Post, Story, or Reels
        if (isset($request->type) && ($request->type == 1 || $request->type == 2 || $request->type == 3)) {

            // Get all input data from the request
            $input = $request->all();

            // Fetch the list of users that the authenticated user follows
            $userFollow = UserFollows::where('user_id', $user->id)->get()->toArray();
            $userIdArr = array();
            $userIdArr[] = $user->id; // Add the user's own ID to the list

            // If the 'flag' is set in the request, add all followed users' IDs to the array
            if (isset($request->flag) && $request->flag == 1) {
                for ($f = 0; $f < count($userFollow); $f++) {
                    $userIdArr[] = $userFollow[$f]['user_follow_id'];
                }
            }

            // Fetch the list of users who have blocked the authenticated user
            $blockUserIds = UserBlock::where('block_user_id', $user->id)
                ->pluck('user_id')
                ->toArray();

            // Build the query to fetch posts depending on whether the user_id was passed or not
            if ($flagUser == 1) {
                // Fetch posts for the specified user ID, filtering by type and excluding blocked users
                $getPostQuery = UserPosts::where('user_id', $user->id)
                    ->where('type', $request->type)
                    ->whereNotIn('user_id', $blockUserIds)
                    ->where('is_deleted', '0')
                    ->orderBy('created_at', 'desc'); // Sort by the most recently updated posts
            } else {
                // Fetch posts for all users, filtering by type and excluding blocked users
                $getPostQuery = UserPosts::where('type', $request->type)
                    ->where('is_deleted', '0')
                    ->whereNotIn('user_id', $blockUserIds)
                    ->orderBy('created_at', 'desc'); // Sort by the most recently updated posts
            }

            // Calculate pagination values (total rows, pages, current page, and offset)
            $total_rows = $getPostQuery->count();
            $total_page = isset($input['limit']) ? $input['limit'] : $limit;
            $page_size = ceil($total_rows / $total_page);
            $currentpage = isset($input['page_no']) ? $input['page_no'] : $page;
            $offset = $currentpage * $total_page - $total_page;

            // Apply pagination limits to the query
            $getPostQuery->take($total_page)->offset($offset);

            // Execute the query and fetch the posts as an array
            $getUserPosts = $getPostQuery->get()->toArray();

            // Check if there are more pages
            $more_page = false;
            if ($currentpage < $page_size) {
                $more_page = true;
            }

            // If posts were found, process them
            if (count($getUserPosts) > 0) {
                $userPostIdArr = $userPostCommentsArr = $finalArr = $userPostFilesArr = $userNameArr = $userPhotoArr = $parentCommentIdArr = $childCommentArr = $userLikeArr = array();

                // Process each post (adding metadata like share URL)
                for ($p = 0; $p < count($getUserPosts); $p++) {
                    $getUserPosts[$p]['share_url'] = asset($getUserPosts[$p]['share_url']);
                    $userPostIdArr[] = $getUserPosts[$p]['id'];
                }

                // Fetch user details for posts, depending on whether user_id was passed
                if ($flagUser == 1) {
                    $getUserName = User::where('id', $user->id)->get(['id', 'name', 'photo'])->toArray();
                } else {
                    $getUserName = User::where('id', '>', 0)->get(['id', 'name', 'photo'])->toArray();
                }

                // Store user names and photos in arrays for quick access
                for ($g = 0; $g < count($getUserName); $g++) {
                    $userNameArr[$getUserName[$g]['id']] = $getUserName[$g]['name'];
                    $userPhotoArr[$getUserName[$g]['id']] = $getUserName[$g]['photo'];
                }

                // Fetch likes for each post
                $getUserPostLikes = UserLikes::whereIn('content_id', $userPostIdArr)->where('type', 1)->get()->toArray();
                for ($l = 0; $l < count($getUserPostLikes); $l++) {
                    if (isset($userPostLikeArr[$getUserPostLikes[$l]['content_id']])) {
                        $userPostLikeArr[$getUserPostLikes[$l]['content_id']] += 1;
                    } else {
                        $userPostLikeArr[$getUserPostLikes[$l]['content_id']] = 1;
                    }
                    $userLikeArr[$getUserPostLikes[$l]['content_id']][] = $getUserPostLikes[$l];
                }

                // Fetch comments for each post and order them by creation date
                $getUserPostComments = UserComments::with('users')->whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();

                // Process each comment to add tagged user's username in the comment text
                for ($c = 0; $c < count($getUserPostComments); $c++) {

                    // Detect all occurrences of '@x' pattern in the comment text using regex
                    preg_match_all('/@(\d+)/', $getUserPostComments[$c]['comment'], $matches);

                    // If any user is tagged in the comment
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $userId) {
                            // Fetch the tagged user's username based on the captured user ID
                            $taggedUser = User::where('id', $userId)->first();
                            if ($taggedUser) {
                                // Replace '@x' with the actual username in the comment text
                                $getUserPostComments[$c]['comment'] = str_replace('@' . $userId, '@' . $taggedUser->name, $getUserPostComments[$c]['comment']);
                            }
                        }
                    }
                }

                // Fetch images and videos related to the posts
                $getUserPostImage = UserPostImageVideo::whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
                for ($v = 0; $v < count($getUserPostImage); $v++) {
                    if ($getUserPostImage[$v]['file_path']) {
                        if (isset($getUserPostImage[$v]['file_path'])) {
                            $getUserPostImage[$v]['file_path'] = asset($getUserPostImage[$v]['file_path']);
                        }

                        if (isset($getUserPostImage[$v]['thumb_url'])) {
                            $getUserPostImage[$v]['thumb_url'] = asset($getUserPostImage[$v]['thumb_url']);
                        }
                    }
                    $getUserPostImage[$v]['share_url'] = asset($getUserPostImage[$v]['share_url']);
                    $userPostFilesArr[$getUserPostImage[$v]['post_id']][] = $getUserPostImage[$v];
                }

                // Organize comments into parent and child arrays
                for ($c = 0; $c < count($getUserPostComments); $c++) {
                    if ($getUserPostComments[$c]['parent_comment_id'] > 0) {
                        $childCommentArr[$getUserPostComments[$c]['post_id']][$getUserPostComments[$c]['parent_comment_id']][] = $getUserPostComments[$c];
                    } else {
                        $userPostCommentsArr[$getUserPostComments[$c]['post_id']][] = $getUserPostComments[$c];
                    }
                }

                // Process each post and add additional information like likes, comments, images, etc.
                for ($pa = 0; $pa < count($getUserPosts); $pa++) {
                    $userName = "";
                    $likeCount = 0;
                    $photo = "";

                    // Add user name to the post
                    if (isset($userNameArr[$getUserPosts[$pa]['user_id']])) {
                        $userName = $userNameArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['user_name'] = $userName;

                    // Add sport information to the post
                    $getUserSport = UserSport::where('user_id', $getUserPosts[$pa]['user_id'])->orderBy('created_at', 'desc')->get('sport_id')->toArray();
                    if (count($getUserSport) > 0) {
                        $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
                        for ($i = 0; $i < count($sportInfo); $i++) {
                            $getTeam = UserTeam::where('user_id', $getUserPosts[$pa]['user_id'])->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                            if (count($getTeam) > 0) {
                                $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                                if (!empty($teamDetails)) {
                                    foreach ($teamDetails as $tKey => $teamDetail) {
                                        $teamDetails[$tKey]['strTeamLogo'] = asset($teamDetail['strTeamLogo']);
                                    }
                                }
                                $sportInfo[$i]['team'] = $teamDetails;
                            } else {
                                $sportInfo[$i]['team'] = array();
                            }
                        }
                        $getUserPosts[$pa]['sport_info'] = $sportInfo;
                    } else {
                        $getUserPosts[$pa]['sport_info'] = array();
                    }

                    // Add user photo to the post
                    if (isset($userPhotoArr[$getUserPosts[$pa]['user_id']])) {
                        $photo = $userPhotoArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['photo'] = $photo;

                    // Add like count to the post
                    if (isset($userPostLikeArr[$getUserPosts[$pa]['id']])) {
                        $likeCount = $userPostLikeArr[$getUserPosts[$pa]['id']];
                    }
                    $getUserPosts[$pa]['likes'] = $likeCount;

                    // Add user likes data for the post
                    $userPostLikeDataArr = array();
                    if (isset($userLikeArr[$getUserPosts[$pa]['id']])) {
                        $userPostLikeDataArr = $userLikeArr[$getUserPosts[$pa]['id']];
                    }
                    $getUserPosts[$pa]['user_likes'] = $userPostLikeDataArr;

                    // Check if the post is saved by the authenticated user
                    $postSave = PostSave::where('post_id', $getUserPosts[$pa]['id'])->where('user_id', $user1->id)->count();
                    $getUserPosts[$pa]['save_by_me'] = $postSave > 0 ? 1 : 0;

                    // Check if the post is liked by the authenticated user
                    $saveLikes = UserLikes::where('content_id', $getUserPosts[$pa]['id'])->where('type', 1)->where('user_id', $user1->id)->count();
                    $getUserPosts[$pa]['like_by_me'] = $saveLikes > 0 ? 1 : 0;

                    // Check if the authenticated user follows the post's author
                    $userFollows = UserFollows::where('user_id', $user1->id)->where('user_follow_id', $getUserPosts[$pa]['user_id'])->count();
                    $getUserPosts[$pa]['is_user_follow'] = $userFollows > 0 ? 1 : 0;

                    // Check if the post's author follows the authenticated user
                    $userFollows1 = UserFollows::where('user_follow_id', $user1->id)->where('user_id', $getUserPosts[$pa]['user_id'])->count();
                    $getUserPosts[$pa]['follow_by_me'] = $userFollows1 > 0 ? 1 : 0;

                    // Add comments data to the post (including child comments)
                    if (isset($userPostCommentsArr[$getUserPosts[$pa]['id']])) {
                        $userMainCommentArr = $userPostCommentsArr[$getUserPosts[$pa]['id']];
                        $getUserPosts[$pa]['comments'] = $userMainCommentArr;
                        for ($f = 0; $f < count($userMainCommentArr); $f++) {
                            if (isset($childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']])) {
                                $childCommentArr1 = $childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']];
                                $getUserPosts[$pa]['comments'][$f]['comments'] = $childCommentArr1;
                                for ($xa = 0; $xa < count($childCommentArr1); $xa++) {
                                    if (isset($childCommentArr[$getUserPosts[$pa]['id']][$childCommentArr1[$xa]['id']])) {
                                        $childCommentArr2 = $childCommentArr[$getUserPosts[$pa]['id']][$childCommentArr1[$xa]['id']];
                                        $getUserPosts[$pa]['comments'][$f]['comments'][$xa]['comments'] = $childCommentArr2;
                                        for ($ya = 0; $ya < count($childCommentArr2); $ya++) {
                                            if (isset($childCommentArr[$getUserPosts[$pa]['id']][$childCommentArr2[$ya]['id']])) {
                                                $childCommentArr3 = $childCommentArr[$getUserPosts[$pa]['id']][$childCommentArr2[$ya]['id']];
                                                $getUserPosts[$pa]['comments'][$f]['comments'][$xa]['comments'][$ya]['comments'] = $childCommentArr3;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $getUserPosts[$pa]['comments'] = array();
                    }

                    // Add images and videos to the post
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

                    // Add creation timestamp to the post
                    $getUserPosts[$pa]['created_at'] = $getUserPosts[$pa]['created_at'] . '+00:00';

                    // Add the post to the final array
                    $finalArr[] = $getUserPosts[$pa];
                }

                // Return the final array of posts along with pagination details
                return response()->json(['success' => $finalArr, 'total' => $total_rows, 'size' => $total_page, 'total_page' => $page_size, 'page' => $currentpage, 'more' => $more_page], $this->successStatus);
            } else {
                // If no posts are found, return an error response
                return response()->json(['error' => 'Post data not found'], $this->successStatus);
            }
        } else {
            // If 'type' is not valid, return an error response
            return response()->json(['error' => 'Pass type value 1-Post, 2-Story, 3-Reels'], $this->successStatus);
        }

        // If no other condition matches, return unauthorized error
        return response()->json(['error' => 'Unauthorised'], 401);
    }


    // Update Follow and Unfollow users
    public function setUserFollows(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'follow_id' => 'required',
            'follow' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        if (!isset($request->follow_id)) {
            return response()->json(['error' => 'Pass follow_id'], $this->successStatus);
        }
        if (isset($request->follow) && ($request->follow == 1 || $request->follow == 0)) {
            if ($request->follow == 1) {
                $userFollow = UserFollows::where('user_id', $user->id)->where('user_follow_id', $request->follow_id)->first();
                if (!$userFollow) {
                    $newUserFollow = new UserFollows();
                    $newUserFollow->user_id = $user->id;
                    $newUserFollow->user_follow_id = $request->follow_id;
                    $newUserFollow->save();

                    $newNotification = new Notification();
                    $newNotification->user1 = $user->id;
                    $newNotification->user2 = $request->follow_id;
                    $notMessage = $user->name . " is following you";
                    $newNotification->notification = $notMessage;
                    $newNotification->type = 1;
                    $newNotification->save();
                }
            } else if ($request->follow == 0) {
                $deleted = UserFollows::where('user_id', $user->id)->where('user_follow_id', $request->follow_id)->delete();
                $deleted = Notification::where('user1', $user->id)->where('user2', $request->follow_id)->where('type', 1)->delete();
            }
            $fRecord1 = UserFollows::where('user_id', $user->id)
                ->count();
            $fRecord2 = UserFollows::where('user_follow_id', $user->id)
                ->count();
            $record = [
                'following' => $fRecord1,
                'followers' => $fRecord2
            ];
            return response()->json(['success' => $record], $this->successStatus);
        } else {
            return response()->json(['error' => 'Pass follow 1 or 0'], $this->successStatus);
        }
    }

    // Follow and Unfollow users
    public function getUserFollows(Request $request)
    {
        $user = Auth::user();
        if ($request->user_id) {
            $user->id = $request->user_id;
        }
        $record = $this->getFollowers($user->id);
        return response()->json(['success' => $record], $this->successStatus);
    }

    // Remove following
    public function removeUserFollowing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'following_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        if (!isset($request->following_id)) {
            return response()->json(['error' => 'Pass following id'], $this->successStatus);
        }
        if (isset($request->following_id)) {
            $deleted = UserFollows::where('user_id', $request->following_id)->where('user_follow_id', $user->id)->delete();
            $fRecord1 = UserFollows::where('user_id', $user->id)
                ->count();
            $fRecord2 = UserFollows::where('user_follow_id', $user->id)
                ->count();
            $record = [
                'following' => $fRecord1,
                'followers' => $fRecord2
            ];
            return response()->json(['success' => $record], $this->successStatus);
        } else {
            return response()->json(['error' => 'Pass following id'], $this->successStatus);
        }
    }

    public function searchUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_input' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        if (isset($input['user_input']) && trim($input['user_input']) != "") {
            $userInput = trim($input['user_input']);
            $blockUserIds = UserBlock::where('block_user_id', $user->id)
                ->pluck('user_id')
                ->toArray();
            //dd($blockUserIds);
            $getUserPosts = User::where('id', '!=', $user->id)
                ->where('name', 'like', '%' . $userInput . '%')
                ->where('is_deleted', 0)
                ->whereNotIn('id', $blockUserIds)
                ->orderBy('updated_at', 'desc')->get()->toArray();
            if (count($getUserPosts) > 0) {
                return response()->json(['success' => $getUserPosts], $this->successStatus);
            } else {
                return response()->json(['error' => 'User not found'], $this->successStatus);
            }
        }
        return response()->json(['error' => 'Please enter search critearea.'], $this->successStatus);
    }

    public function setProfileAccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_access' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        if (isset($request->profile_access) && ($request->profile_access == 0 || $request->profile_access == 1)) {
            $access = $request->profile_access;
            $userUpdate = User::find($user->id);
            $userUpdate->profile_access = $access;
            $userUpdate->update();
            return response()->json(['success' => $userUpdate], $this->successStatus);
        } else {
            return response()->json(['error' => 'Invalid value pass in profile_access'], $this->successStatus);
        }
    }

    public function showReportTitle(Request $request)
    {
        $report = Report::all();
        return response()->json(['success' => $report], $this->successStatus);
    }

    public function reportPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $user = Auth::user();
        if (isset($input['title'])) {
            $title = $input['title'];
            $content_id = $input['content_id'];
            $type = $input['type'];

            $f = 0;
            if ($type == 1) {
                $userPost = UserPosts::where('id', $content_id)
                    ->first();
                if ($userPost) {
                    $f = 1;
                }
            } else if ($type == 2) {
                $userComment = UserComments::where('id', $content_id)
                    ->first();
                if ($userComment) {
                    $f = 1;
                }
            }
            if ($f == 1) {
                $userReport = PostReport::where('user_id', $user->id)
                    ->where('content_id', $content_id)
                    ->where('type', $type)
                    ->first();
                if (!$userReport) {
                    $postReport = new PostReport();
                    $postReport->user_id = $user->id;
                    $postReport->content_id = $content_id;
                    $postReport->type = $type;
                    $postReport->title = $title;
                    $postReport->save();
                }
            }

            $fRecord1 = PostReport::where('user_id', $user->id)
                ->where('content_id', $content_id)
                ->where('type', $type)
                ->count();
            $record = [
                'reports' => $fRecord1
            ];
            return response()->json(['success' => $record], $this->successStatus);
            //echo "<pre>";print_r($sports);die;
        } else {
            return response()->json(['error' => 'Please enter report value.'], $this->successStatus);
        }
    }

    public function getFollowers($id)
    {
        $fRecord1 = UserFollows::where('user_id', $id)
            ->count();
        if ($fRecord1 > 0) {
            $fRecord1UserId = UserFollows::where('user_id', $id)->get(['user_follow_id']);
            $followingRecord = User::whereIn('id', $fRecord1UserId)->get();
        } else {
            $followingRecord = [];
        }

        $fRecord2 = UserFollows::where('user_follow_id', $id)
            ->count();
        if ($fRecord2 > 0) {
            $fRecord2UserId = UserFollows::where('user_follow_id', $id)->get(['user_id']);
            $followersRecord = User::whereIn('id', $fRecord2UserId)->get();
        } else {
            $followersRecord = [];
        }

        $record = [
            'following' => $fRecord1,
            'followingUsers' => $followingRecord,
            'followers' => $fRecord2,
            'followersUsers' => $followersRecord,
        ];
        return $record;
    }

    public function getUserPostCount($userId)
    {
        $userPost = UserPosts::where('user_id', $userId)->where('type', '1')->where('is_deleted', '0')->get();
        return array($userPost, count($userPost->toArray()));
        //return $userPost;
    }

    public function getUserFavoritePostCount($userId)
    {
        $userFavoritePost = PostFavorite::where('user_id', $userId)->get('post_id');
        $post = UserPosts::whereIn('id', $userFavoritePost->toArray())->where('is_deleted', '0')->get();
        return array($post, count($userFavoritePost->toArray()));
        //return $userFavoritePost;
    }

    public function getUserSavePostCount($userId)
    {
        $userSavePost = PostSave::where('user_id', $userId)->get('post_id');
        $post = UserPosts::whereIn('id', $userSavePost->toArray())->where('is_deleted', '0')->get();
        return array($post, count($userSavePost->toArray()));
    }

    public function getUserLikePostCount($userId)
    {
        $userLikePost = UserLikes::where('user_id', $userId)->where('type', 1)->get('content_id');
        $post = UserPosts::whereIn('id', $userLikePost->toArray())->where('is_deleted', '0')->get();
        return array($post, count($userLikePost->toArray()));
    }

    public function getUserComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $childCommentArr = $userPostCommentsArr = $userIdArr = array();
        $getUserPostComments = UserComments::with('users')->orderBy('created_at', 'desc')->get()->toArray();
        for ($c = 0; $c < count($getUserPostComments); $c++) {
            if (!in_array($getUserPostComments[$c]['user_id'], $userIdArr)) {
                $userIdArr[] = $getUserPostComments[$c]['user_id'];
            }
            if ($getUserPostComments[$c]['id'] == $request->comment_id) {
                $userPostCommentsArr[] = $getUserPostComments[$c];
            } else {
                $childCommentArr[$getUserPostComments[$c]['parent_comment_id']][] = $getUserPostComments[$c];
            }
        }
        //echo "<pre>";print_r($childCommentArr);die;
        for ($g = 0; $g < count($userPostCommentsArr); $g++) {
            $parentId = $userPostCommentsArr[$g]['id'];
            //echo $parentId;die;
            if (isset($childCommentArr[$parentId])) {
                $thirdLevelComment = $childCommentArr[$parentId];
                $userPostCommentsArr[$g]['comments'] = $thirdLevelComment;
                for ($t = 0; $t < count($thirdLevelComment); $t++) {
                    $thirdId = $thirdLevelComment[$t]['id'];
                    if (isset($childCommentArr[$thirdId])) {
                        $userPostCommentsArr[$g]['comments'][$t]['comments'] = $childCommentArr[$thirdId];
                    }
                }
            }
        }
        return response()->json(['success' => $userPostCommentsArr], $this->successStatus);
    }

    public function editUserPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'post_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        //echo "<pre>";print_r($input);die;
        if (isset($input['post_id']) && trim($input['post_id']) > 0) {
            if (isset($input['type']) && trim($input['type']) != "") {
                $postId = trim($input['post_id']);
                $BASE_URL = Helper::checkServer()['BASE_URL'];
                $updateArr = array();
                $updateArrateArr['user_id'] = $user->id;
                $updateArr['title'] = isset($input['title']) ? trim($input['title']) : "";
                $updateArr['post'] = isset($input['post']) ? trim($input['post']) : "";
                $updateArr['type'] = $input['type'];    // 1-Post, 2-Story, 3-Reels
                $updateArr['location'] = $input['location'];
                $updateArr['comment'] = $input['comment'];    // 1-On, 2-Off
                $updateArr['created_at'] = date("Y-m-d H:i:s");
                //echo "<pre>";print_r($updateArr);die;
                UserPosts::where('id', $postId)->update($updateArr);
                $shareName = "post";
                if ($input['type'] == 2) {
                    $shareName = "story";
                }
                if ($input['type'] == 3) {
                    $shareName = "reels";
                }
                $insertArr = $updateArr;
                $updateArr = array();
                $updateArr['share_url'] = $shareName . "?id=" . base64_encode($postId);
                $insertArr['share_url'] = $shareName . "?id=" . base64_encode($postId);
                UserPosts::where('id', $postId)->update($updateArr);
                $allowedfileExtension = ['pdf', 'jpg', 'png', 'JPG', 'PNG', 'jpeg', 'gif', 'GIF'];
                if (!$request->hasFile('photo')) {
                    //Not Found
                } else {
                    UserPostImageVideo::where('post_id', trim($postId))->where('type', '0')->delete();
                    foreach ($request->file('photo') as $im) {
                        $extension = $im->getClientOriginalExtension();
                        $check = in_array($extension, $allowedfileExtension);
                        if ($check) {
                            $filename = date('YmdHi') . $im->getClientOriginalName();
                            $im->move(public_path('image/post'), $filename);
                            $insertFileArr = array();
                            $insertFileArr['user_id'] = $user->id;
                            $insertFileArr['post_id'] = trim($postId);
                            $insertFileArr['created_at'] = date("Y-m-d H:i:s");
                            $insertFileArr['file_path'] = 'image/post/' . $filename;
                            $insertFileArr['type'] = "0";
                            //echo "<pre>";print_r($insertFileArr);die;
                            $imageId = UserPostImageVideo::create($insertFileArr)->id;
                            $updateArr = array();
                            $updateArr['share_url'] = $shareName . "/image?id=" . base64_encode($imageId);
                            UserPostImageVideo::where('id', $imageId)->update($updateArr);
                        }
                    }
                }

                if (!$request->hasFile('video')) {
                } else {
                    $videos = $request->file('video');
                    UserPostImageVideo::where('post_id', trim($postId))->where('type', '1')->delete();
                    foreach ($request->file('video') as $iv) {
                        $filename = date('YmdHi') . $iv->getClientOriginalName();
                        $iv->move(public_path('video/post'), $filename);
                        $insertFileArr = array();
                        $insertFileArr['user_id'] = $user->id;
                        $insertFileArr['post_id'] = trim($postId);
                        $insertFileArr['created_at'] = date("Y-m-d H:i:s");
                        $insertFileArr['file_path'] = 'video/post/' . $filename;
                        $insertFileArr['type'] = "1";
                        //echo "<pre>";print_r($insertFileArr);die;
                        $videoId = UserPostImageVideo::create($insertFileArr)->id;
                        $updateArr = array();
                        $updateArr['share_url'] = $shareName . "/video?id=" . base64_encode($videoId);
                        UserPostImageVideo::where('id', $videoId)->update($updateArr);
                    }
                }
                return response()->json(['success' => $insertArr], $this->successStatus);
            }
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function deleteUserPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        if (isset($input['post_id']) && trim($input['post_id']) > 0) {
            $postId = trim($input['post_id']);
            //echo "<pre>";print_r($postId);die;
            UserPosts::where('id', $postId)->delete();
            UserPostImageVideo::where('post_id', $postId)->delete();
            UserTag::where('post_id', $postId)->delete();
            PostCategory::where('post_id', $postId)->delete();
            return response()->json(['success' => 'Deleted'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function deleteComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        if (isset($input['comment_id']) && trim($input['comment_id']) > 0) {
            $commentId = trim($input['comment_id']);
            UserComments::where('id', $commentId)->delete();
            return response()->json(['success' => 'Deleted'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function uploadFiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $userUpdate = User::find($user->id);
        $input = $request->all();
        $latest = [];
        $files = UserImageVideo::where('user_id', $user->id)->orderBy('created_at', 'desc')->get()->toArray();
        if (!empty($files)) {
            foreach ($files as $fkey => $file) {
                $files[$fkey]['file_path'] = asset($file['file_path']);
            }
        }
        $BASE_URL = Helper::checkServer()['BASE_URL'];
        $allowedfileExtension = ['pdf', 'jpg', 'png', 'JPG', 'PNG', 'jpeg', 'gif', 'GIF'];
        if (!$request->hasFile('files')) {
            //Not Found
        } else {
            $c = 0;
            foreach ($request->file('files') as $im) {
                $extension = $im->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);
                if ($check || $input['type'] == 1) {
                    $filename = date('YmdHi') . $im->getClientOriginalName();
                    $insertFileArr = array();
                    $insertFileArr['user_id'] = $user->id;
                    if ($input['type'] ==  1) {
                        $im->move(public_path('image/videos'), $filename);
                        $insertFileArr['file_path'] = 'image/videos/' . $filename;

                        // Generate a thumbnail for the video
                        $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.png';
                        $thumbnailPath = public_path('image/videos/') . $thumbnailFilename;

                        try {
                            // Initialize FFmpeg and open the video
                            $ffmpeg = FFMpeg::create();
                            $video = $ffmpeg->open(public_path('image/videos/' . $filename));

                            // Extract a frame (e.g., at 5 seconds into the video) and save it as a thumbnail
                            $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))->save($thumbnailPath);

                            // Add thumb_url to the insert array
                            $insertFileArr['thumb_url'] = 'image/videos/' . $thumbnailFilename;
                        } catch (\Exception $e) {
                        }
                    } else if ($input['type'] == 0) {
                        // Image upload logic
                        try {
                            $im->move(public_path('image/images'), $filename);
                            $insertFileArr['file_path'] = 'image/images/' . $filename;

                            // Since there is no thumbnail for images, ensure thumb_url is null or empty
                            $insertFileArr['thumb_url'] = null;
                        } catch (\Exception $e) {
                            // Catch errors and return a message
                            return response()->json(['error' => 'Image upload failed: ' . $e->getMessage()], 500);
                        }
                    } else {
                        $im->move(public_path('image/images'), $filename);
                        $insertFileArr['file_path'] = 'image/images/' . $filename;
                    }
                    $insertFileArr['type'] = $input['type'];
                    $insertFileArr['created_at'] = date("Y-m-d H:i:s");
                    $insertFileArr['file_path'] = asset($insertFileArr['file_path']);
                    $insertFileArr['thumb_url'] = $input['type'] == 1 ? asset($insertFileArr['thumb_url']) : null;
                    UserImageVideo::create($insertFileArr)->id;
                    $c++;
                }
            }
            $latest = UserImageVideo::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take($c)
                ->get()
                ->toArray();
            if (!empty($latest)) {
                foreach ($latest as $lkey => $lat) {
                    $latest[$lkey]['file_path'] = asset($lat['file_path']);
                }
            }
        }


        return response()->json(['success' => $insertFileArr], $this->successStatus);
    }

    public function deleteUserFiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        if (isset($input['file_id']) && trim($input['file_id']) > 0) {
            $fileId = trim($input['file_id']);
            UserImageVideo::where('id', $fileId)->delete();
            $files = UserImageVideo::where('user_id', $user->id)->get()->toArray();
            if (!empty($files)) {
                foreach ($files as $fkey => $file) {
                    $files[$fkey]['file_path'] = asset($file['file_path']);
                }
            }
            return response()->json(['success' => $files], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function blockUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        if (isset($input['user_id']) && trim($input['user_id']) > 0) {
            $userId = trim($input['user_id']);
            $userCheck1 = UserBlock::where('user_id', $user->id)
                ->where('block_user_id', $userId)
                ->first();
            if ($userCheck1) {
            } else {
                $blockUser = [
                    'user_id' => $user->id,
                    'block_user_id' => $userId,
                    'block_by' => 1
                ];
                UserBlock::create($blockUser);
            }

            $userCheck2 = UserBlock::where('user_id', $userId)
                ->where('block_user_id', $user->id)
                ->first();
            if ($userCheck2) {
            } else {
                $blockUser = [
                    'user_id' => $userId,
                    'block_user_id' => $user->id,
                    'block_by' => 0
                ];
                UserBlock::create($blockUser);
            }

            $deleted = UserFollows::where('user_id', $user->id)->where('user_follow_id', $userId)->delete();
            $deleted = UserFollows::where('user_id', $userId)->where('user_follow_id', $user->id)->delete();
            return response()->json(['success' => 'User block now!'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function unBlockUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        if (isset($input['user_id']) && trim($input['user_id']) > 0) {
            $userId = trim($input['user_id']);
            UserBlock::where('user_id', $user->id)
                ->where('block_user_id', $userId)
                ->delete();
            UserBlock::where('block_user_id', $user->id)
                ->where('user_id', $userId)
                ->delete();
            return response()->json(['success' => 'User unblock now!'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }
    public function blockUserList(Request $request)
    {
        $user = Auth::user();

        $blockUserList = UserBlock::where('user_id', $user->id)
            ->where('block_by', 1)
            ->pluck('block_user_id')
            ->toArray();
        $blockUsers = User::whereIn('id', $blockUserList)
            ->get(['id', 'name', 'photo'])
            ->toArray();
        return response()->json(['success' => $blockUsers], $this->successStatus);

        return response()->json(['error' => 'Unauthorised'], 401);
    }
    public function removeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        if (isset($input['user_id']) && trim($input['user_id']) > 0) {
            $userId = trim($input['user_id']);
            $checkUser = User::where('id', $userId)->where('is_deleted', '2')->first();
            if ($checkUser) {
                return response()->json(['success' => 'User already removed!'], $this->successStatus);
            }

            User::where('id', $userId)
                ->update(['is_deleted' => '2']);
            return response()->json(['success' => 'User remove!'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function sendOtpMail($user_id, $mobile, $email_id, $name, $forgotPassword = '')
    {
        require base_path("vendor/autoload.php");
        $mail = new PHPMailer(true);
        $randOtp = rand(100000, 999999);
        $insertArr = array();
        $insertArr['user_id'] = $user_id;
        $insertArr['mobile'] = $mobile;
        $insertArr['email'] = $email_id;
        $insertArr['otp'] = $randOtp;
        $insertArr['created_at'] = date("Y-m-d H:i:s");
        OtpVerification::create($insertArr);
        if ($forgotPassword == 1) {
            $updateArr['password'] = bcrypt($randOtp);
            User::where('email', $email_id)->update($updateArr);
            $subject = "New Password For Login";
        } else {
            $subject = "One Time Password";
        }

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        // More headers
        $html = "Hello " . $name . ",<br><br>";
        if ($forgotPassword == 1) {
            $html .= "Below is your New password for login account<br><br>";
            $html .= "Password : " . $randOtp . "<br>";
        } else {
            $html .= "Below is your One time password for verify account<br><br>";
            $html .= "OTP : " . $randOtp . "<br>";
        }


        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';             //  smtp host
        $mail->SMTPAuth = true;
        $mail->Username = 'octagonapp3@gmail.com';   //  sender username
        $mail->Password = 'efeevufaapaewpis';       // sender password
        $mail->SMTPSecure = 'tls';                  // encryption - ssl/tls
        $mail->Port = 587;                          // port - 587/465

        $mail->setFrom('octagonapp3@gmail.com', 'Octagon');
        $mail->addAddress($email_id);
        $mail->addReplyTo('octagonapp3@gmail.com', 'Octagon');

        $mail->isHTML(true);                // Set email content format to HTML

        $mail->Subject = $subject;
        $mail->Body    = $html;
        //dd($mail);
        // $mail->AltBody = plain text version of email body;

        if (!$mail->send()) {
            return "Email not sent.";
        } else {
            return 1;
        }
    }

    public function userGroupList(Request $request)
    {
        $user = Auth::user();
        $input = $request->all();
        $all_users = User::where('id','<>', $user->id)->where('user_type','0')->get()->toArray();
        $group_id = 0;
        if (isset($input['group_id']) && trim($input['group_id']) > 0) {
            $group_id = trim($input['group_id']);
        }
        foreach ($all_users as $ukey => $uvalue) {
                $ismember = UserGroupMember::where('user_id', $uvalue['id'])
                    ->where('group_id', $group_id)
                    ->orderBy('created_at', 'desc');
                $all_users[$ukey]['is_member'] = $ismember->count();
            }
        return response()->json(['success' => $all_users], $this->successStatus);
    }
}
