<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\UserPosts;
use App\Models\UserComments;
use App\Models\Sport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {

    public function index() {
        if (Auth::check()) {
            $getUserCount = User::where('user_type', '0')->where('is_deleted', '0')->get()->toArray();
            $getPostCount = UserPosts::where('is_deleted', '0')->get()->toArray();
            $getSportCount = Sport::where('is_deleted', '0')->get()->toArray();
            return view('admin.dashboard')->with('users', count($getUserCount))->with('posts', count($getPostCount))->with('sports', count($getSportCount));
        } else {
            return redirect('/logout');
        }
    }

}
