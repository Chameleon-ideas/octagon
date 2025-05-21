<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SportCountries;
use App\Models\SportLeagues;
use App\Services\AllSportsService;
use Illuminate\Http\Request;
use App\Models\Sport;
use App\Models\UserSport;
use App\Models\UserTeam;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Validator;

class SportController extends Controller
{

    public $successStatus = 200;

    protected $allSportsService;

    public function __construct(AllSportsService $allSportsService)
    {
        $this->allSportsService = $allSportsService;
    }

    public function sportDetails()
    {
        $sport = Sport::get();
        return response()->json(['success' => $sport], $this->successStatus);
    }

    public function saveUserSports(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();
        if (isset($input['sports'])) {
            $userSports = (!is_array($input['sports'])) ? json_decode($input['sports'], true) : $input['sports'];
            for ($s = 0; $s < count($userSports); $s++) {
                $checkUserSport = UserSport::where('user_id', $user->id)->where('sport_id', $userSports[$s]['sport_id'])->where('sport_api_id', $userSports[$s]['sport_api_id'])->orderBy('id', 'desc')->get(['id'])->toArray();
                if (count($checkUserSport) > 0) {
                    //Already Exist User Sport
                } else {
                    $insertArr = array();
                    $insertArr['user_id'] = $user->id;
                    $insertArr['sport_id'] = $userSports[$s]['sport_id'];
                    $insertArr['sport_api_id'] = $userSports[$s]['sport_api_id'];
                    $insertArr['created_at'] = date("Y-m-d H:i:s");
                    //echo "<pre>";print_r($insertArr);die;
                    UserSport::create($insertArr);
                }
            }
            $sport = UserSport::where('user_id', $user->id)->get();
            $success = array();
            $success['sport'] = $sport;
            $getUserSport = UserSport::where('user_id', $user->id)->orderBy('created_at', 'desc')->get('sport_id')->toArray();
            if (count($getUserSport) > 0) {
                $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
                for ($i = 0; $i < count($sportInfo); $i++) {
                    $getTeam = UserTeam::where('user_id', $user->id)->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                    if (count($getTeam) > 0) {
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
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function syncSoccerCountries(Request $request)
    {
        try {
            $this->syncSoccerCountriesData();
            return response()->json(['status' => true, 'message' => 'Soccer countries retrieved successfully!', 'data' => []], 200);
        } catch (\Exception $ex) {
            Log::alert('Error in syncSportCountries method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncSoccerCountriesData()
    {
        $countries = $this->allSportsService->getFootballCountries();
        if (!empty($countries['result'])) {
            foreach ($countries['result'] as $country) {
                SportCountries::updateOrCreate([
                    'sport_id' => SportCountries::SOCCER,
                    'country_id' => $country['country_key'],
                ], [
                    'sport_id' => SportCountries::SOCCER,
                    'country_id' => $country['country_key'],
                    'country_name' => $country['country_name'],
                    'country_iso_name' => $country['country_iso2'],
                    'logo_url' => $country['country_logo'],
                ]);
            }
        }
        return true;
    }

    public function syncSoccerLeagues(Request $request)
    {
        try {
            $skip = $request->skip ?? 0;
            $totalCountries = $this->syncSoccerLeaguesData($skip);
            return response()->json(['status' => true, 'message' => 'Soccer leagues retrieved successfully!', 'data' => ['total_countries' => $totalCountries]], 200);
        } catch (\Exception $ex) {
            Log::alert('Error in syncSportLeagues method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncSoccerLeaguesData($skip = 0)
    {
        $localCountries = SportCountries::IsSoccer()->skip($skip)->limit(50)->get();
        foreach ($localCountries as $localCountry) {
            $leagues = $this->allSportsService->getFootballLeagues(['countryId' => $localCountry->country_id]);
            if (!empty($leagues['result'])) {
                foreach ($leagues['result'] as $league) {
                    SportLeagues::updateOrCreate([
                        'sport_id' => SportCountries::SOCCER,
                        'country_id' => $league['country_key'],
                        'league_id' => $league['league_key'],
                    ], [
                        'sport_id' => SportCountries::SOCCER,
                        'country_id' => $league['country_key'],
                        'league_id' => $league['league_key'],
                        'league_name' => $league['league_name'],
                        'logo_url' => $league['league_logo'],
                    ]);
                }
            } else {
                Log::alert('Data not retrieved from syncSoccerLeagues method.');
            }
        }
        return SportCountries::IsSoccer()->count();
    }

    public function syncSoccerTeams(Request $request)
    {
        try {
            $localLeagues = SportLeagues::IsSoccer()->skip($request->skip ?? 0)->limit(50)->get();
            $totalLeagues = SportLeagues::IsSoccer()->count();
            foreach ($localLeagues as $localLeague) {
                $teams = $this->allSportsService->getFootballTeams(['leagueId' => $localLeague->league_id]);
                if (!empty($teams['result'])) {
                    foreach ($teams['result'] as $team) {
                        Team::updateOrCreate([
                            'idSport' => 1,
                            'idCountry' => $localLeague->country_id,
                            'idLeague' => $localLeague->league_id,
                            'idTeam' => $team['team_key'],
                        ], [
                            // Sport Data
                            'idSport' => 1,
                            'strSport' => 'Soccer',
                            // Country Data
                            'idCountry' => $localLeague->country_id,
                            'strCountry' => $localLeague->countryDetail->country_name,
                            // League Data
                            'idLeague' => $localLeague->league_id,
                            'strLeague' => $localLeague->league_name,
                            // Team Data
                            'idTeam' => $team['team_key'],
                            'strTeam' => $team['team_name'],
                            'strTeamLogo' => $team['team_logo'],
                        ]);
                    }
                } else {
                    Log::alert('Data not retrieved from syncSoccerTeams method.');
                }
            }
            return response()->json(['status' => true, 'message' => 'Soccer teams retrieved successfully!', 'data' => ['total_leagues' => $totalLeagues]], 200);
        } catch (\Exception $ex) {
            Log::alert('Error in syncSoccerTeams method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncBasketballCountries(Request $request)
    {
        try {
            $this->syncBasketballCountriesData();
            return response()->json(['status' => true, 'message' => 'Basketball countries retrieved successfully!', 'data' => []], 200);
        } catch (\Exception $ex) {
            Log::alert('Error in syncBasketballCountries method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncBasketballCountriesData()
    {
        $countries = $this->allSportsService->getBasketballCountries();
        if (!empty($countries['result'])) {
            foreach ($countries['result'] as $country) {
                $existingData = SportCountries::where('country_name', 'LIKE', "$country[country_name]%")->whereNotNull('logo_url')->first();
                SportCountries::updateOrCreate([
                    'sport_id' => SportCountries::BASKETBALL,
                    'country_id' => $country['country_key'],
                ], [
                    'sport_id' => SportCountries::BASKETBALL,
                    'country_id' => $country['country_key'],
                    'country_name' => $country['country_name'],
                    'country_iso_name' => $existingData->country_iso_name ?? null,
                    'logo_url' => $existingData->logo_url ?? null,
                ]);
            }
        }
        return true;
    }

    public function syncBasketballLeagues(Request $request)
    {
        try {
            $this->syncBasketballLeaguesData();
            return response()->json(['status' => true, 'message' => 'Basketball leagues retrieved successfully!', 'data' => []], 200);
        } catch (\Exception $ex) {
            Log::alert('Error in syncBasketballLeagues method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncBasketballLeaguesData()
    {
        //$leagues = $this->allSportsService->getBasketballLeagues(['countryId' => $localCountry->country_id]);
        $leagues = $this->allSportsService->getBasketballLeagues();
        if (!empty($leagues['result'])) {
            foreach ($leagues['result'] as $league) {
                SportLeagues::updateOrCreate([
                    'sport_id' => SportCountries::BASKETBALL,
                    'country_id' => $league['country_key'],
                    'league_id' => $league['league_key'],
                ], [
                    'sport_id' => SportCountries::BASKETBALL,
                    'country_id' => $league['country_key'],
                    'league_id' => $league['league_key'],
                    'league_name' => $league['league_name'],
                    'logo_url' => $league['league_logo'] ?? null,
                ]);
            }
        } else {
            Log::alert('Data not retrieved from syncBasketballLeaguesData method.');
        }
        return true;
    }

    public function syncBasketballTeams(Request $request)
    {
        try {
            $localLeagues = SportLeagues::IsBasketball()->skip($request->skip ?? 0)->limit(50)->get();
            $totalLeagues = SportLeagues::IsBasketball()->count();
            foreach ($localLeagues as $localLeague) {
                $teams = $this->allSportsService->getBasketballTeams(['leagueId' => $localLeague->league_id]);
                if (!empty($teams['result'])) {
                    foreach ($teams['result'] as $team) {
                        Team::updateOrCreate([
                            'idSport' => SportCountries::BASKETBALL,
                            'idCountry' => $localLeague->country_id,
                            'idLeague' => $localLeague->league_id,
                            'idTeam' => $team['team_key'],
                        ], [
                            // Sport Data
                            'idSport' => SportCountries::BASKETBALL,
                            'strSport' => 'Basketball',
                            // Country Data
                            'idCountry' => $localLeague->country_id ?? null,
                            'strCountry' => $localLeague->countryDetail->country_name ?? null,
                            // League Data
                            'idLeague' => $localLeague->league_id ?? null,
                            'strLeague' => $localLeague->league_name ?? null,
                            // Team Data
                            'idTeam' => $team['team_key'],
                            'strTeam' => $team['team_name'],
                            'strTeamLogo' => $team['team_logo'],
                        ]);
                    }
                } else {
                    Log::alert('Data not retrieved from syncBasketballTeams method.');
                }
            }
            return response()->json(['status' => true, 'message' => 'Basketball teams retrieved successfully!', 'data' => ['total_leagues' => $totalLeagues]], 200);
        } catch (\Exception $ex) {
            Log::alert('Error in syncBasketballTeams method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncCricketLeagues(Request $request)
    {
        try {
            $this->syncCricketLeaguesData();
            return response()->json(['status' => true, 'message' => 'Cricket leagues retrieved successfully!', 'data' => []], 200);
        } catch (\Exception $ex) {
            Log::alert('Error in syncCricketLeagues method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncCricketLeaguesData()
    {
        $leagues = $this->allSportsService->getCricketLeagues();
        if (!empty($leagues['result'])) {
            foreach ($leagues['result'] as $league) {
                SportLeagues::updateOrCreate([
                    'sport_id' => SportCountries::CRICKET,
                    'league_id' => $league['league_key'],
                ], [
                    'sport_id' => SportCountries::CRICKET,
                    'league_id' => $league['league_key'],
                    'league_name' => $league['league_name'],
                    'logo_url' => $league['league_logo'] ?? null,
                    'league_year' => $league['league_year'] ?? null,
                ]);
            }
        } else {
            Log::alert('Data not retrieved from syncCricketLeaguesData method.');
        }
        return true;
    }

    public function syncCricketTeams(Request $request)
    {
        try {
            $localLeagues = SportLeagues::IsCricket()->skip($request->skip ?? 0)->limit(50)->get();
            $totalLeagues = SportLeagues::IsCricket()->count();
            foreach ($localLeagues as $localLeague) {
                $teams = $this->allSportsService->getCricketTeams(['leagueId' => $localLeague->league_id]);
                if (!empty($teams['result'])) {
                    foreach ($teams['result'] as $team) {
                        Team::updateOrCreate([
                            'idSport' => SportCountries::CRICKET,
                            'idLeague' => $localLeague->league_id,
                            'idTeam' => $team['team_key'],
                        ], [
                            // Sport Data
                            'idSport' => SportCountries::CRICKET,
                            'strSport' => 'Cricket',
                            // Country Data
                            'idCountry' => $localLeague->country_id ?? null,
                            'strCountry' => $localLeague->countryDetail->country_name ?? null,
                            // League Data
                            'idLeague' => $localLeague->league_id ?? null,
                            'strLeague' => $localLeague->league_name ?? null,
                            // Team Data
                            'idTeam' => $team['team_key'],
                            'strTeam' => $team['team_name'],
                            'strTeamLogo' => $team['team_logo'],
                        ]);
                    }
                } else {
                    Log::alert('Data not retrieved from syncCricketTeams method.');
                }
            }
            return response()->json(['status' => true, 'message' => 'Cricket teams retrieved successfully!', 'data' => ['total_leagues' => $totalLeagues]], 200);
        } catch (\Exception $ex) {
            Log::alert('Error in syncCricketTeams method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncBaseballTeams(Request $request)
    {
        try {
            foreach (config('sportdata.baseball') as $key => $team) {
                Team::updateOrCreate([
                    'idSport' => SportCountries::BASEBALL,
                    'idTeam' => $key,
                    'strTeam' => $team['team_name'],
                ], [
                    // Sport Data
                    'idSport' => SportCountries::BASEBALL,
                    'strSport' => 'Baseball',
                    // Country Data
                    'idCountry' => null,
                    'strCountry' => null,
                    // League Data
                    'idLeague' => null,
                    'strLeague' => null,
                    // Team Data
                    'idTeam' => $key,
                    'strTeam' => $team['team_name'],
                    'strTeamLogo' => $team['team_logo'],
                ]);
            }
            return response()->json(['status' => true, 'message' => 'Baseball teams retrieved successfully!', 'data' => ['total_teams' => count(config('sportdata.baseball'))]], 200);
        } catch (\Exception $ex) {
            Log::alert('Error in syncBaseballTeams method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncIceHockeyTeams(Request $request)
    {
        try {
            foreach (config('sportdata.ice_hockey') as $key => $team) {
                Team::updateOrCreate([
                    'idSport' => SportCountries::ICE_HOCKEY,
                    'idTeam' => $key,
                    'strTeam' => $team['team_name'],
                ], [
                    // Sport Data
                    'idSport' => SportCountries::ICE_HOCKEY,
                    'strSport' => 'Ice Hockey',
                    // Country Data
                    'idCountry' => null,
                    'strCountry' => null,
                    // League Data
                    'idLeague' => null,
                    'strLeague' => null,
                    // Team Data
                    'idTeam' => $key,
                    'strTeam' => $team['team_name'],
                    'strTeamLogo' => $team['team_logo'],
                ]);
            }
            return response()->json(['status' => true, 'message' => 'Ice Hockey teams retrieved successfully!', 'data' => ['total_teams' => count(config('sportdata.ice_hockey'))]], 200);
        } catch (\Exception $ex) {
            Log::alert('Error in syncIceHockeyTeams method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncCricketHockeyLogos()
    {
        try{
            $dirPath = storage_path('app/public/teams/');
            File::makeDirectory($dirPath . 'cricket', $mode = 0777, true, true);
            File::makeDirectory($dirPath . 'ice_hockey', $mode = 0777, true, true);
            $cricketTeams = Team::where('strSport', 'Cricket')->where('status', '0')->whereNotNull('strTeamLogo')->get();
            foreach($cricketTeams as $cricketTeam) {
                $name = last(explode('/', $cricketTeam->strTeamLogo));
                //$path = storage_path('app/public/teams/cricket/' . $name);
                $path = "public/teams/cricket/$name";
                Storage::disk('local')->put($path, file_get_contents($cricketTeam->strTeamLogo));
                $cricketTeam->strTeamLogo = asset("storage/teams/cricket/$name");
                $cricketTeam->save();
            }
            $iceHockeyTeams = Team::where('strSport', 'Ice Hockey')->where('status', '0')->whereNotNull('strTeamLogo')->get();
            foreach($iceHockeyTeams as $iceHockeyTeam) {
                $name = last(explode('/', $iceHockeyTeam->strTeamLogo));
                //$path = storage_path('app/public/teams/cricket/' . $name);
                $path = "public/teams/ice_hockey/$name";
                Storage::disk('local')->put($path, file_get_contents($iceHockeyTeam->strTeamLogo));
                $iceHockeyTeam->strTeamLogo = asset("storage/teams/ice_hockey/$name");
                $iceHockeyTeam->save();
            }
            return response()->json(['status' => true, 'message' => 'Logos moved successfully!', 'data' => ['cricket' => count($cricketTeams), 'ice_hockey' => count($iceHockeyTeams)]], 200);
        } catch(\Exception $ex){
            Log::alert('Error in syncCricketHockeyLogos method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }

    public function syncNflTeams()
    {
        try{
            $dirPath = storage_path('app/public/teams/');
            File::makeDirectory($dirPath . 'football', $mode = 0777, true, true);

            $path = "public/teams/football/nfl_logo.png";
            Storage::disk('local')->put($path, file_get_contents('https://loodibee.com/wp-content/uploads/nfl-league-logo-350x350.png'));
            $leagueLogoUrl = asset("storage/teams/football/nfl_logo.png");

            $league = SportLeagues::create([
                'sport_id' => 2,
                'country_id' => 114,
                'league_id' => 0,
                'league_name' => 'NFL',
                'league_year' => null,
                'logo_url' => $leagueLogoUrl,
            ]);

            $nflTeams = config('sportdata.nfl_teams');

            foreach ($nflTeams as $nflTeam) {
                $name = Str::of($nflTeam['name'])->lower()->replace(' ', '_');
                $path = "public/teams/football/$name.png";
                Storage::disk('local')->put($path, file_get_contents($nflTeam['logo']));
                $teamLogoUrl = asset("storage/teams/football/$name.png");

                $insData = [];
                $insData['idTeam'] = 0;
                $insData['strTeam'] = $nflTeam['name'];
                $insData['strTeamLogo'] = $teamLogoUrl;
                $insData['idSport'] = 2;
                $insData['strSport'] = 'Football';
                $insData['strLeague'] = $league->league_name;
                $insData['idLeague'] = $league->id;
                $insData['idCountry'] = $league->country_id;
                $insData['strCountry'] = 'USA';
                $insData['strCountryLogo'] = 'https://apiv2.allsportsapi.com/logo/logo_country/114_usa.png';
                $insData['strLeagueLogo'] = $leagueLogoUrl;
                Team::create($insData);
            }
            return response()->json(['status' => true, 'message' => 'NFL Data Stored successfully!', 'data' => ['total' => count($nflTeams)]], 200);
        } catch(\Exception $ex){
            Log::alert('Error in syncNflTeams method | Message: ' . $ex->getMessage() . ' | Line: ' . $ex->getLine());
            return response()->json(['status' => false, 'message' => 'Internal server error.', 'data' => [$ex->getMessage(), $ex->getLine()]], 500);
        }
    }
}
