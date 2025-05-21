<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\UserPosts;
use App\Models\UserComments;
use App\Models\Sport;
use App\Models\Team;
use App\Models\OtpVerification;
use App\Models\PostReport;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;

class AdminController extends Controller {

    public function index() {
        return view('admin.login');
    }

    public function checkLogin(Request $request) {
        $input = $request->all();
        //echo "<pre>";print_r($input);die;
        $userData = array(
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'user_type' => '1'
        );
        if (Auth::attempt($userData)) {
            return redirect('/successlogin');
        } else {
            //dd('1');
            return back()->withErrors(['error' => 'Invalid email or password!']);
        }
    }

    public function successLogin() {
        if (Auth::check()) {
            return redirect('/dashboard');
        } else {
            return redirect('/logout');
        }
    }

    public function logout() {
        Auth::logout();
        return redirect('/');
    }

    public function usersList(Request $request) {
        if (Auth::check()) {
            if ($request->ajax()) {
                $getUserList = User::where('user_type', '0')->where('is_deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
                //echo "<pre>";print_r($getUserList);die;
                $userListArr = array();
                for ($u = 0; $u < count($getUserList); $u++) {
                    $userListArr[$u]['responsive_id'] = $getUserList[$u]['id'];
                    $userListArr[$u]['id'] = $getUserList[$u]['id'];
                    $userListArr[$u]['full_name'] = $getUserList[$u]['name'];
                    $userListArr[$u]['email'] = $getUserList[$u]['email'];
                    $userListArr[$u]['mobile'] = $getUserList[$u]['mobile'];
                    $gender = "Male";
                    if ($getUserList[$u]['gender'] == "1") {
                        $gender = "Female";
                    }
                    $userListArr[$u]['gender'] = $gender;
                    $userListArr[$u]['gender_id'] = $getUserList[$u]['gender'];
                    $userListArr[$u]['bio'] = $getUserList[$u]['bio'];
                    if(isset($getUserList[$u]['photo'])) {
                        $userListArr[$u]['photo'] = $getUserList[$u]['photo'];
                        $userListArr[$u]['photoName'] = $getUserList[$u]['name'];
                    } else {
                        $userListArr[$u]['photo'] = '';
                        $userListArr[$u]['photoName'] = '';
                    }

                    $userListArr[$u]['dob'] = date("Y-m-d", strtotime($getUserList[$u]['dob']));
                    $userListArr[$u]['created_at'] = date("Y-m-d H:i:s", strtotime($getUserList[$u]['created_at']));
                }
                //echo "<pre>";print_r($userListArr);die;
                return DataTables()->of($userListArr)->make(true);
            }
            return view("admin.userlist");
        } else {
            return redirect('/logout');
        }
    }

    public function saveUser(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $validator = Validator::make($request->all(), [
                        'fullName' => 'required',
                        'email' => 'required',
                        'dob' => 'required'
            ]);
            if (trim($request->get('userId')) > 0) {
                $userDetail = DB::select("SELECT * FROM users WHERE (email='" . trim($request->get('email')) . "') AND id!=" . trim($request->get('userId')) . " AND is_deleted='0' LIMIT 0,1");
            } else {
                $userDetail = DB::select("SELECT * FROM users WHERE (email='" . trim($request->get('email')) ."') AND is_deleted='0' LIMIT 0,1");
            }
            $status = 0;
            $message = 'fail';
            $error = array();
            if (count($userDetail) > 0) {
                $existEmail = $userDetail[0]->email;
                $existMobile = $userDetail[0]->mobile;
                if (trim($existEmail) == trim($request->get('email'))) {
                    $error['email'][] = "The email has already been taken.";
                }
                /*if (trim($existMobile) == trim($request->get('mobile'))) {
                    $error['mobile'][] = "The mobile has already been taken.";
                }*/
                //echo "<pre>";print_r($error);die;
                $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
                return response()->json($responseData);
            }
            //echo "<pre>";print_r($validator);die;
            if ($validator->fails()) {
                //return response()->json(['error' => $validator->errors()], 401);
                $error = $validator->errors();
            } else {
                $fullName = trim($request->get('fullName'));
                $mobile = trim($request->get('mobile'));
                $email = trim($request->get('email'));
                $dob = trim($request->get('dob'));
                $gender = trim($request->get('gender'));
                $bio = trim($request->get('bio'));

                $userDataArr = array();
                $userDataArr['name'] = $fullName;
                $userDataArr['mobile'] = $mobile;
                $userDataArr['email'] = $email;
                $userDataArr['dob'] = $dob;
                $userDataArr['gender'] = $gender;
                $userDataArr['bio'] = $bio;
                if (isset($input['password']) && trim($input['password']) != "") {
                    $password = trim($request->get('password'));
                    $userDataArr['password'] = bcrypt($password);
                }
                $userDataArr['is_deleted'] = '0';
                $dateTime = date("Y-m-d H:i:s");
                //echo "<pre>";print_r($userDataArr);die;
                if (trim($request->get('userId')) > 0) {
                    $userDataArr['updated_at'] = $dateTime;
                    User::where('id', trim($request->get('userId')))->update($userDataArr);
                } else {
                    $userDataArr['updated_at'] = $dateTime;
                    $userDataArr['created_at'] = $dateTime;
                    User::create($userDataArr);
                }
                $status = 1;
                $message = 'Success';
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function deleteUser(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $status = 0;
            $message = 'fail';
            $error = array();
            if (isset($input['userId']) && trim($input['userId']) > 0) {
                $userDataArr = array();
                $userDataArr['is_deleted'] = '2';
                $userDataArr['updated_at'] = date("Y-m-d H:i:s");
                User::where('id', trim($input['userId']))->update($userDataArr);
                //User::where('id', trim($input['userId']))->delete();
                $status = 1;
                $message = 'Success';
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function deleteRecords(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $status = 0;
            $message = 'fail';
            $error = array();
            if (isset($input['checkboxValues']) && count($input['checkboxValues']) > 0) {
                $recordIdArr = $input['checkboxValues'];
                if(count($recordIdArr) > 0){
                    $deleteTableName = $input['table'];
                    $deleteDataArr = array();
                    if($deleteTableName == "users"){
                        $deleteDataArr['updated_at'] = date("Y-m-d H:i:s");
                        $deleteDataArr['is_deleted'] = '2';
                        User::whereIn('id', $recordIdArr)->update($deleteDataArr);
                    }else if($deleteTableName == "posts"){
                        $deleteDataArr['updated_at'] = date("Y-m-d H:i:s");
                        $deleteDataArr['is_deleted'] = '1';
                        UserPosts::whereIn('id', $recordIdArr)->update($deleteDataArr);
                    }else if($deleteTableName == "postreport"){
                        PostReport::whereIn('id', $recordIdArr)->delete();
                    }else if($deleteTableName == "team"){
                        $deleteDataArr['status'] = '1';
                        Team::whereIn('id', $recordIdArr)->update($deleteDataArr);
                    }
                }
                //User::where('id', trim($input['userId']))->delete();
                $status = 1;
                $message = 'Success';
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function postsList(Request $request) {
        if (Auth::check()) {
            if ($request->ajax()) {
                $getPostList = UserPosts::where('is_deleted', '0')->where('type','1')->orderBy('id', 'DESC')->get()->toArray();
                $userIdArr = $userNameArr = array();
                for ($p = 0; $p < count($getPostList); $p++) {
                    if (!in_array($getPostList[$p]['user_id'], $userIdArr)) {
                        $userIdArr[] = $getPostList[$p]['user_id'];
                    }
                }
                if (count($userIdArr) > 0) {
                    $getUserList = User::whereIn('id', $userIdArr)->get(['id', 'name', 'email', 'gender', 'photo'])->toArray();
                    for ($n = 0; $n < count($getUserList); $n++) {
                        $userNameArr[$getUserList[$n]['id']] = $getUserList[$n];
                    }
                }
                //echo "<pre>";print_r($userIdArr);die;
                $userListArr = array();
                for ($u = 0; $u < count($getPostList); $u++) {
                    $userListArr[$u]['responsive_id'] = $getPostList[$u]['id'];
                    $userListArr[$u]['id'] = $getPostList[$u]['id'];
                    $userGender = "0";
                    $userName = '';
                    $userEmail = '';
                    $userPhoto = '';
                    if (isset($userNameArr[$getPostList[$u]['user_id']])) {
                        $userName = $userNameArr[$getPostList[$u]['user_id']]['name'];
                        $userGender = $userNameArr[$getPostList[$u]['user_id']]['gender'];
                        $userEmail = $userNameArr[$getPostList[$u]['user_id']]['email'];
                        $userPhoto = $userNameArr[$getPostList[$u]['user_id']]['photo'];
                    }
                    $gender = "Male";
                    if ($userGender == "1") {
                        $gender = "Female";
                    }
                    $userListArr[$u]['full_name'] = $userName;
                    $userListArr[$u]['email'] = $userEmail;
                    $userListArr[$u]['photo'] = $userPhoto;
                    $userListArr[$u]['user_id'] = $getPostList[$u]['user_id'];
                    $userListArr[$u]['gender'] = $gender;
                    $userListArr[$u]['post'] = $getPostList[$u]['post'];
                    $userListArr[$u]['created_at'] = date("Y-m-d H:i:s", strtotime($getPostList[$u]['created_at']));
                    $userListArr[$u]['updated_at'] = date("Y-m-d H:i:s", strtotime($getPostList[$u]['updated_at']));
                }
                //echo "<pre>";print_r($userListArr);die;
                return DataTables()->of($userListArr)->make(true);
            }
            $getUserList = User::where('user_type', '0')->where('is_deleted', '0')->get(['id', 'name'])->toArray();
            //echo "<pre>";print_r($getUserList);die;
            return view("admin.postlist")->with('userlist', $getUserList);
        } else {
            return redirect('/logout');
        }
    }

     public function postreportList(Request $request) {
        if (Auth::check()) {
            //dd($request->ajax());
            if ($request->ajax()) {
                $getPostReportList = PostReport::orderBy('id', 'DESC')->get();
                //dd($getPostReportList);
                $userIdArr = $userNameArr = $postNameArr = array();
                for ($p = 0; $p < count($getPostReportList); $p++) {
                    if (!in_array($getPostReportList[$p]['user_id'], $userIdArr)) {
                        $userIdArr[] = $getPostReportList[$p]['user_id'];
                    }
                }
                if (count($userIdArr) > 0) {
                    $getUserList = User::whereIn('id', $userIdArr)->get(['id', 'name', 'gender'])->toArray();
                    for ($n = 0; $n < count($getUserList); $n++) {
                        $userNameArr[$getUserList[$n]['id']] = $getUserList[$n];
                    }
                }
                $getPostList = UserPosts::where('is_deleted', '0')->where('type','1')->get()->toArray();
                if(count($getPostList) > 0){
                    for ($d = 0; $d < count($getPostList); $d++) {
                        $postNameArr[$getPostList[$d]['id']] = $getPostList[$d]['post'];
                    }
                }
                //echo "<pre>";print_r($postNameArr);die;
                $userListArr = array();
                for ($u = 0; $u < count($getPostReportList); $u++) {
                    $userListArr[$u]['responsive_id'] = $getPostReportList[$u]['id'];
                    $userListArr[$u]['id'] = $getPostReportList[$u]['id'];
                    $userGender = "0";
                    $userName = $postName = '';
                    if (isset($userNameArr[$getPostReportList[$u]['user_id']])) {
                        $userName = $userNameArr[$getPostReportList[$u]['user_id']]['name'];
                        $userGender = $userNameArr[$getPostReportList[$u]['user_id']]['gender'];
                    }
                    if (isset($postNameArr[$getPostReportList[$u]['content_id']])) {
                        $postName = $postNameArr[$getPostReportList[$u]['content_id']];
                    }
                    $gender = "Male";
                    if ($userGender == "1") {
                        $gender = "Female";
                    }
                    $userListArr[$u]['full_name'] = $userName;
                    $userListArr[$u]['user_id'] = $getPostReportList[$u]['user_id'];
                    $userListArr[$u]['gender'] = $gender;
                    $userListArr[$u]['post'] = $postName;
                    $userListArr[$u]['report'] = $getPostReportList[$u]['title'];
                    $userListArr[$u]['report_by'] = $userName;
                    $userListArr[$u]['created_at'] = date("Y-m-d H:i:s", strtotime($getPostReportList[$u]['created_at']));
                }
                //echo "<pre>";print_r($userListArr);die;
                return DataTables()->of($userListArr)->make(true);
            }
            $getUserList = PostReport::get()->toArray();
            //echo "<pre>";print_r($getUserList);die;
            return view("admin.postreportlist")->with('userlist', $getUserList);
        } else {
            return redirect('/logout');
        }
    }

    public function savePost(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $validator = Validator::make($request->all(), [
                        'postName' => 'required',
                        'userId' => 'required'
            ]);
            $status = 0;
            $message = 'fail';
            $error = array();
            //echo "<pre>";print_r($validator);die;
            if ($validator->fails()) {
                //return response()->json(['error' => $validator->errors()], 401);
                $error = $validator->errors();
            } else {
                $postName = trim($request->get('postName'));
                $userId = trim($request->get('userId'));
                $postDataArr = array();
                $postDataArr['post'] = $postName;
                $postDataArr['user_id'] = $userId;
                $postDataArr['is_deleted'] = '0';
                //echo "<pre>";print_r($postDataArr);die;
                $dateTime = date("Y-m-d H:i:s");
                if (trim($request->get('postId')) > 0) {
                    $postDataArr['updated_at'] = $dateTime;
                    UserPosts::where('id', trim($request->get('postId')))->update($postDataArr);
                } else {
                    $postDataArr['updated_at'] = $dateTime;
                    $postDataArr['created_at'] = $dateTime;
                    UserPosts::create($postDataArr);
                }
                $status = 1;
                $message = 'Success';
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function deletePost(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $status = 0;
            $message = 'fail';
            $error = array();
            if (isset($input['postId']) && trim($input['postId']) > 0) {
                $postDataArr = array();
                $postDataArr['is_deleted'] = '1';
                $postDataArr['updated_at'] = date("Y-m-d H:i:s");
                UserPosts::where('id', trim($input['postId']))->update($postDataArr);
                //UserPosts::where('id', trim($input['postId']))->delete();
                $status = 1;
                $message = 'Success';
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function deletePostReport(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $status = 0;
            $message = 'fail';
            $error = array();
            if (isset($input['postId']) && trim($input['postId']) > 0) {
                $postDataArr = array();
                $postDataArr['is_deleted'] = '1';
                $postDataArr['updated_at'] = date("Y-m-d H:i:s");
                PostReport::where('id', trim($input['postId']))->delete();
                //UserPosts::where('id', trim($input['postId']))->delete();
                $status = 1;
                $message = 'Success';
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function sportsList(Request $request) {
        if (Auth::check()) {
            if ($request->ajax()) {
                $getSportList = Sport::where('is_deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
                //echo "<pre>";print_r($userIdArr);die;
                $userListArr = array();
                for ($u = 0; $u < count($getSportList); $u++) {
                    $userListArr[$u]['responsive_id'] = $getSportList[$u]['id'];
                    $userListArr[$u]['id'] = $getSportList[$u]['id'];
                    $userListArr[$u]['idSport'] = $getSportList[$u]['idSport'];
                    $userListArr[$u]['strSport'] = $getSportList[$u]['strSport'];
                    $userListArr[$u]['strFormat'] = $getSportList[$u]['strFormat'];
                    $userListArr[$u]['strSportThumb'] = $getSportList[$u]['strSportThumb'];
                    $userListArr[$u]['strSportIconGreen'] = $getSportList[$u]['strSportIconGreen'];
                    $userListArr[$u]['strSportDescription'] = $getSportList[$u]['strSportDescription'];
                    $userListArr[$u]['created_at'] = date("Y-m-d H:i:s", strtotime($getSportList[$u]['created_at']));
                }
                //echo "<pre>";print_r($userListArr);die;
                return DataTables()->of($userListArr)->make(true);
            }
            return view("admin.sportlist");
        } else {
            return redirect('/logout');
        }
    }

    public function saveSport(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $validator = Validator::make($request->all(), [
                        'sportId' => 'required',
                        'sportName' => 'required',
                        'sportFormat' => 'required',
                        'thumbUrl' => 'required',
                        'iconUrl' => 'required'
            ]);
            if (trim($request->get('Id')) > 0) {
                $sportDetail = DB::select("SELECT * FROM sports WHERE idSport='" . trim($request->get('sportId')) . "' AND id!=" . trim($request->get('Id')) . " AND is_deleted='0' LIMIT 0,1");
            } else {
                $sportDetail = DB::select("SELECT * FROM sports WHERE idSport='" . trim($request->get('sportId')) . "' AND is_deleted='0' LIMIT 0,1");
            }
            //echo "<pre>";print_r($sportDetail);die;
            $status = 0;
            $message = 'fail';
            $error = array();
            if (count($sportDetail) > 0) {
                $existSportId = $sportDetail[0]->idSport;
                if (trim($existSportId) == trim($request->get('sportId'))) {
                    $error['sportId'][] = "The sport id has already been taken.";
                    $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
                    return response()->json($responseData);
                }
                //echo "<pre>";print_r($error);die;
            }
            //echo "<pre>";print_r($validator);die;
            if ($validator->fails()) {
                //return response()->json(['error' => $validator->errors()], 401);
                $error = $validator->errors();
            } else {
                $idSport = trim($request->get('sportId'));
                $strSport = trim($request->get('sportName'));
                $strFormat = trim($request->get('sportFormat'));
                $strSportThumb = trim($request->get('thumbUrl'));
                $strSportIconGreen = trim($request->get('iconUrl'));
                $strSportDescription = trim($request->get('description'));
                $sportDataArr = array();
                $sportDataArr['idSport'] = $idSport;
                $sportDataArr['strSport'] = $strSport;
                $sportDataArr['strFormat'] = $strFormat;
                $sportDataArr['strSportThumb'] = $strSportThumb;
                $sportDataArr['strSportIconGreen'] = $strSportIconGreen;
                $sportDataArr['strSportDescription'] = $strSportDescription;
                $sportDataArr['is_deleted'] = '0';
                //echo "<pre>";print_r($sportDataArr);die;
                $dateTime = date("Y-m-d H:i:s");
                if (trim($request->get('Id')) > 0) {
                    Sport::where('id', trim($request->get('Id')))->update($sportDataArr);
                } else {
                    $sportDataArr['created_at'] = $dateTime;
                    Sport::create($sportDataArr);
                }
                $status = 1;
                $message = 'Success';
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function deleteSport(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $status = 0;
            $message = 'fail';
            $error = array();
            if (isset($input['sportId']) && trim($input['sportId']) > 0) {
                $sportDataArr = array();
                $sportDataArr['is_deleted'] = '1';
                Sport::where('id', trim($input['sportId']))->update($sportDataArr);
                //Sport::where('id', trim($input['sportId']))->delete();
                $status = 1;
                $message = 'Success';
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function teamsList(Request $request) {
        if (Auth::check()) {
            if ($request->ajax()) {
                $getTeamList = Team::select('id', 'idTeam', 'strTeam', 'strTeamLogo', 'strSport', 'strLeague','strCountry')->where('status', '0')->orderBy('id', 'DESC')->get()->toArray();
                // echo "<pre>";print_r($getTeamList);die;
                $teamListArr = array();
                for ($u = 0; $u < count($getTeamList); $u++) {
                    $teamListArr[$u]['responsive_id'] = $getTeamList[$u]['id'];
                    $teamListArr[$u]['id'] = $getTeamList[$u]['id'];
                    $teamListArr[$u]['idTeam'] = $getTeamList[$u]['idTeam'];
                    $teamListArr[$u]['name'] = $getTeamList[$u]['strTeam'];
                    if(isset($getTeamList[$u]['strTeamLogo'])) {
                        $teamListArr[$u]['logo'] = $getTeamList[$u]['strTeamLogo'];
                    } else {
                        $teamListArr[$u]['logo'] = '';
                    }

                    $teamListArr[$u]['sports'] = $getTeamList[$u]['strSport'];
                    $teamListArr[$u]['league'] = $getTeamList[$u]['strLeague'];
                    $teamListArr[$u]['country'] = $getTeamList[$u]['strCountry'];
                }
                //echo "<pre>";print_r($teamListArr);die;
                return DataTables()->of($teamListArr)->make(true);
            }
            return view("admin.teamlist");
        } else {
            return redirect('/logout');
        }
    }

    public function deleteTeam(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $status = 0;
            $message = 'fail';
            $error = array();
            if (isset($input['teamId']) && trim($input['teamId']) > 0) {
                $teamDataArr = array();
                $teamDataArr['status'] = '1';
                Team::where('id', trim($input['teamId']))->update($teamDataArr);
                $status = 1;
                $message = 'Success';
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function saveTeam(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $validator = Validator::make($request->all(), [
                'teamId' => 'required',
                'teamName' => 'required'
            ]);
            if (trim($request->get('teamId')) > 0) {
                $teamDetail = DB::select("SELECT * FROM teams WHERE strTeam='" . trim($request->get('teamName')) . "' AND id!=" . trim($request->get('teamId')) . " AND status='0' LIMIT 0,1");
            } else {
                $teamDetail = DB::select("SELECT * FROM teams WHERE strTeam='" . trim($request->get('teamName')) . "' AND status='0' LIMIT 0,1");
            }
            //echo "<pre>";print_r($teamDetail);die;
            $status = 0;
            $message = 'fail';
            $error = array();
            if (count($teamDetail) > 0) {
                $existTeamId = $teamDetail[0]->id;
                if (trim($existTeamId) == trim($request->get('teamId'))) {
                    $error['teamId'][] = "The team name has already been taken.";
                    $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
                    return response()->json($responseData);
                }
                //echo "<pre>";print_r($error);die;
            }
            //echo "<pre>";print_r($validator);die;
            if ($validator->fails()) {
                //return response()->json(['error' => $validator->errors()], 401);
                $error = $validator->errors();
            } else {
                $idSport = trim($request->get('teamId'));
                $strSport = trim($request->get('teamName'));
                $teamDataArr = array();
                $teamDataArr['strTeam'] = $strSport;
                $teamDataArr['status'] = '0';
                //echo "<pre>";print_r($teamDataArr);die;
                $dateTime = date("Y-m-d H:i:s");
                if (trim($request->get('teamId')) > 0) {
                    Team::where('id', trim($request->get('teamId')))->update($teamDataArr);
                } else {
                    $teamDataArr['created_at'] = $dateTime;
                    Team::create($teamDataArr);
                }
                $status = 1;
                $message = 'Success';
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function accountSetting() {
        if (Auth::check()) {

            $user = Auth::user();
            $getUserDetails = User::where('id', trim($user->id))->where('is_deleted', '0')->get()->toArray();
            if (count($getUserDetails) > 0) {
                return view("admin.accountSetting")->with('userdetails', $getUserDetails[0]);
            } else {
                return view("admin.dashboard");
            }
            //echo "<pre>";print_r($getUserList);die;
        } else {
            return redirect('/logout');
        }
    }

    public function changePassword(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            $validator = Validator::make($request->all(), [
                        'currentPassword' => 'required',
                        'newPassword' => 'required|min:6|max:10',
                        'confirmNewPassword' => 'required|min:6|max:10',
                        'newPassword' => 'min:6|max:10|required_with:newPassword|same:confirmNewPassword',
            ]);
            $status = 0;
            $message = 'fail';
            $error = array();
            if ($validator->fails()) {
                //return response()->json(['error' => $validator->errors()], 401);
                $error = $validator->errors();
            } else {
                $userId = trim($request->get('userId'));
                $getUserDetails = User::where('id', $userId)->get()->toArray();
                //echo "<pre>";print_r($getUserDetails);die;
                if (count($getUserDetails) > 0) {
                    $password = $getUserDetails[0]['password'];
                    $currentPassword = trim($request->get('currentPassword'));
                    $newPassword = trim($request->get('newPassword'));
                    $passwordError = 0;
                    if ($currentPassword == $newPassword) {
                        $passwordError = 1;
                        $error['newPassword'][] = "Current password and new password should be different.";
                    } else {
                        if (password_verify($currentPassword, $password)) {
                            $dateTime = date("Y-m-d H:i:s");
                            $updatePassword = array();
                            $updatePassword['password'] = bcrypt($newPassword);
                            $updatePassword['updated_at'] = $dateTime;
                            User::where('id', trim($request->get('userId')))->update($updatePassword);
                            $status = 1;
                            $message = 'Success';
                        } else {
                            $passwordError = 1;
                            $error['currentPassword'][] = "Sorry, You have entered wrong current password.";
                        }
                    }
                    if ($passwordError > 0) {
                        $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
                        return response()->json($responseData);
                    }
                }
            }
            $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
            return response()->json($responseData);
        } else {
            return redirect('/logout');
        }
    }

    public function forgotPassword() {
        return view("admin.forgotpassword");
    }

    public function passwordMail(Request $request) {
        $input = $request->all();
        //echo "<pre>";print_r($input);die;
        $getUserDetails = User::where('email', trim($input['forgot-password-email']))->get(['id', 'email', 'mobile'])->toArray();
        //echo "<pre>";print_r($getUserDetails);die;
        if (count($getUserDetails) > 0) {
            $email_id = $getUserDetails[0]['email'];
            $user_id = $getUserDetails[0]['id'];
            $mobile = $getUserDetails[0]['mobile'];
            $randOtp = rand(0000000, 1111111);
            $subject = "One Time Password";
            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            // More headers
            $headers .= 'From: <jadavhasmukh10@gmail.com>' . "\r\n";
            $html = "Hello,<br><br>";
            $html .= "Below is your One time password<br><br>";
            $html .= "OTP : " . $randOtp . "<br>";
            //echo $html;die;
            $insertArr = array();
            $insertArr['user_id'] = $user_id;
            $insertArr['mobile'] = $mobile;
            $insertArr['email'] = $email_id;
            $insertArr['otp'] = $randOtp;
            $insertArr['created_at'] = date("Y-m-d H:i:s");
            //echo "<pre>";print_r($insertArr);die;
            OtpVerification::create($insertArr);
            //mail($email_id, $subject, $html, $headers);
            //return response()->json(['success' => $insertArr], $this->successStatus);
            return view("admin.resetpassword");
        } else {
            return view("admin.forgotpassword");
        }
    }

    public function resetPassword(Request $request) {
        $input = $request->all();
        //echo "<pre>";print_r($input);die;
        $otp = $mobile = "";
        if (isset($input['one-time-password']) && trim($input['one-time-password']) != "") {
            $otp = trim($input['one-time-password']);
        }
        if (isset($input['forgot-password-email']) && trim($input['forgot-password-email']) != "") {
            $email = trim($input['forgot-password-email']);
        }
        $status = 0;
        $message = 'fail';
        $error = array();
        if ($otp != "" && $email != "") {
            $newPassword = trim($input['new-password']);
            $getUserDetails = OtpVerification::where('email', $email)->orderBy('id', 'desc')->get(['id', 'email', 'otp'])->first();
            if (isset($getUserDetails->otp) && trim($getUserDetails->otp) == $otp) {
                $dateTime = date("Y-m-d H:i:s");
                $updatePassword = array();
                $updatePassword['password'] = bcrypt($newPassword);
                $updatePassword['updated_at'] = $dateTime;
                //echo "<pre>";print_r($updatePassword);die;
                User::where('id', trim($getUserDetails->id))->update($updatePassword);
                return redirect('/login');
            }
        }
        $responseData = ['status' => $status, 'message' => $message, 'error' => $error];
        return response()->json($responseData);
    }

    public function uploadPhoto(Request $request) {
        if (Auth::check()) {
            $input = $request->all();
            //echo "<pre>";print_r($input);die;
            //echo "<pre>";print_r($request->file('photo'));die;
            $uploadImage = $uploadImageFile = 0;
            if (isset($input['user_photo_background']) && trim($input['user_photo_background']) > 0) {
                $uploadImage = 1;
            }
            $message = "Fail! Please choose image  for uplaod.";
            $alertType = "alert-danger";
            $userUpdate = array();
            $allowedfileExtension = ['jpg', 'png', 'JPG', 'PNG', 'jpeg', 'gif', 'GIF'];
            $BASE_URL = Helper::checkServer()['BASE_URL'];
            if (!$request->hasFile('photo')) {
                if ($uploadImage == 0) {
                    $request->session()->flash($alertType, $message);
                    return redirect('/users-list');
                }
                //$userUpdate->photo = $user->photo;
            } else {
                $photo = $request->file('photo');
                $extension = $photo->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);
                if ($check) {
                    $dateTime = date("Y-m-d H:i:s");
                    $filename = date('YmdHi') . $photo->getClientOriginalName();
                    $photo->move(public_path('image/profile'), $filename);
                    $userUpdate['photo'] = 'image/profile/' . $filename;
                    $userUpdate['updated_at'] = $dateTime;
                    $uploadImageFile = 1;
                    if ($uploadImage == 0) {
                        //echo "<pre>";print_r($userUpdate);die;
                        User::where('id', trim($request->get('user_id_photo')))->update($userUpdate);
                        $request->session()->flash('alert-success', 'Profile photo uploaded successfully.');
                        return redirect('/account-setting');
                    }
                }
            }
            if ($uploadImage > 0) {
                if (!$request->hasFile('background')) {
                    //$userUpdate->photo = $user->photo;
                } else {
                    $background = $request->file('background');
                    $extension = $background->getClientOriginalExtension();
                    $check = in_array($extension, $allowedfileExtension);
                    if ($check) {
                        $uploadImageFile = 1;
                        $dateTime = date("Y-m-d H:i:s");
                        $filename = date('YmdHi') . $background->getClientOriginalName();
                        $background->move(public_path('image/background'), $filename);
                        $userUpdate['background'] = 'image/background/' . $filename;
                    }
                }
            }
            if ($uploadImageFile > 0) {
                $alertType = "alert-success";
                $userUpdate['updated_at'] = $dateTime;
                //echo "<pre>";print_r($userUpdate);die;
                User::where('id', trim($request->get('user_id_photo')))->update($userUpdate);
                $message = "Success! Images uploaded successfully.";
            }
            $request->session()->flash($alertType, $message);
            return redirect('/users-list');
        } else {
            return redirect('/logout');
        }
    }

    public function errorLogin(Request $req) {
        return response()->json(['error' => 'Unauthorised'], 401);
    }

}
