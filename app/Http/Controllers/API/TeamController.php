<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\UserSport;
use App\Models\Sport;
use App\Models\UserTeam;
use Illuminate\Support\Facades\Auth;
use Validator;
use Exception;
use DB;

class TeamController extends Controller {

    public $successStatus = 200;

    public function teamDetails(Request $request) {
        $limit = 10;
        $page = 1;
        $input = $request->all();
        if (isset($input['sports'])) {
            $userSports = (!is_array($input['sports'])) ? json_decode($input['sports'], true) : $input['sports'];
            $searchTeam = isset($input['searchTeam']) ? $input['searchTeam'] : "";
            $team = Team::select('teams.*', 'sport_countries.logo_url as strCountryLogo', 'sport_leagues.logo_url as strLeagueLogo', 'sport_leagues.league_year as league_year')
                ->leftJoin('sport_countries', 'sport_countries.country_id', '=', 'teams.idCountry')
                ->leftJoin('sport_leagues', 'sport_leagues.league_id', '=', 'teams.idLeague')
                ->whereIn('strSport', $userSports)
                ->where('teams.status', '0')
                ->where('strTeam', 'like', '%' . $searchTeam . '%')
                ->whereNotNull('strTeamLogo');
            $total_rows = $team->count();
            $total_page = isset($input['limit']) ? $input['limit'] : $limit;
            $page_size = ceil($total_rows / $total_page);
            $currentpage = isset($input['page_no']) ? $input['page_no'] : $page;
            $offset = $currentpage * $total_page - $total_page;
            $team->take($total_page)->offset($offset);
            $teamData = $team->get()->toArray();
            if(!empty($teamData)) {
                foreach($teamData as $tkey => $team) {
                    $team['strTeamLogo'] = asset($team['strTeamLogo']);
                    $teamData[$tkey] = $team;
                }
            }
            $more_page = false;
            if ($currentpage < $page_size) {
                $more_page = true;
            }
            return response()->json(['success' => $teamData, 'total' => $total_rows, 'size' => $total_page, 'total_page' => $page_size, 'page' => $currentpage, 'more' => $more_page], $this->successStatus);
        } else {
            return response()->json(['error' => 'Enter sports'], $this->successStatus);
        }
    }
    public function saveUserTeams(Request $request) {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
        try {
            $input = $request->all();
            $user = Auth::user();
            if (isset($input['teams'])) {
                UserTeam::where('user_id', $user->id)
                        ->delete();
                $userTeams = (!is_array($input['teams'])) ? json_decode($input['teams'], true) : $input['teams'];
                $teamRecordDb = [];
                for ($s = 0; $s < count($userTeams); $s++) {
                        $fetchTeam = Team::where('id', $userTeams[$s]['id'])
                            ->where('idTeam', $userTeams[$s]['idTeam'])
                            ->where('status', '0')
                            ->first();
                        if($fetchTeam) {
                            $teamRecordDb[] = [
                                'user_id' => $user->id,
                                'team_id' => $userTeams[$s]['id'],
                                'idTeam' => $userTeams[$s]['idTeam'],
                                'sport_id' => $userTeams[$s]['sport_id'],
                                'sport_api_id' => $userTeams[$s]['sport_api_id'],
                                'created_at' => date("Y-m-d H:i:s")
                            ];
                        }
                }
                UserTeam::insert($teamRecordDb);
                $team = UserTeam::where('user_id', $user->id)->get();
                $success = array();
                $success['team'] = $team;
                $getUserSport = UserSport::where('user_id', $user->id)->orderBy('created_at', 'desc')->get('sport_id')->toArray();
                if(count($getUserSport) > 0) {
                    $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
                    for($i=0;$i<count($sportInfo);$i++) {
                        $getTeam = UserTeam::where('user_id', $user->id)->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                        if(count($getTeam) > 0) {
                            $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                            if(!empty($teamDetails)) {
                                foreach($teamDetails as $tKey => $teamDetail) {
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
                return response()->json(['success' => $success], $this->successStatus);
                //echo "<pre>";print_r($sports);die;
            } else {
                return response()->json(['error' => 'Enter teams'], $this->successStatus);
            }
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

    }
    public function fetchTeams(Request $request) {
        try {
            $user = Auth::user();
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthorised'], 401);
            }
            //echo $request->idTeam;
            //DB::enableQueryLog();
            $fetchTeam = Team::where('idTeam', $request->idTeam)
                ->where('status', '0')
                ->first();
            //dd(DB::getQueryLog());
            if($fetchTeam) {
                $fetchTeam->strTeamLogo = asset($fetchTeam->strTeamLogo);
                return response()->json(['success' => $fetchTeam], $this->successStatus);
            } else {
                return response()->json(['error' => "Record not found"], $this->successStatus);
            }
            dd($fetchTeam);
        } catch(Exception $e) {
            return response()->json(['error' => 'Unauthorised'], 401);
        }

    }
}
