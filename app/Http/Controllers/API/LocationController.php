<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class LocationController extends Controller {

    public $successStatus = 200;

    public function getLocation() {
        $user = Auth::user();
        $location = Location::where('country_name', ucwords(trim($user->country)))->get();
        return response()->json(['success' => $location], $this->successStatus);
    }

}
