<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Sport;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Validator;

class TeamController extends Controller {
	
		// Fetch record from 3-party api and store in DB
		public function storeTeamDB(Request $request) {
			$teamRemove = Team::where('strSport', $request->sport)
								->delete();
			$sports = Sport::find(1);
			// Counties list url
			$sportURL = 'https://www.thesportsdb.com/api/v1/json/2/all_countries.php';
			$ch = curl_init();   
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   
			curl_setopt($ch, CURLOPT_URL, $sportURL);   
			$res = curl_exec($ch);   
			$data = json_decode($res, true);
			$teamRecordDb = [];
			$i=0;
			/*foreach($sports as $s) {*/
				foreach($data as $counties) {
					foreach($counties as $country) {
						// URL
						$apiURL = 'https://www.thesportsdb.com/api/v1/json/2/search_all_teams.php?s='.$request->sport.'&c='.$country['name_en'];
						$ch1 = curl_init();   
						curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);   
						curl_setopt($ch1, CURLOPT_URL, $apiURL);   
						$res1 = curl_exec($ch1);   
						$data1 = json_decode($res1, true);
						if(isset($data1) && count($data1) > 0) {
							foreach($data1 as $teams) {
								if(isset($teams) && count($teams) > 0) {
									foreach($teams as $team) {
										//print_r($team);
										$teamRecordDb = [
									        'idTeam' => $team['idTeam'],
									        'idSoccerXML' => $team['idSoccerXML'],
									        'idAPIfootball' => $team['idAPIfootball'],
											'intLoved' => $team['intLoved'],
											'strTeam' => $team['strTeam'],
											'strTeamShort' => $team['strTeamShort'],
									        'strAlternate' => $team['strAlternate'],
									        'intFormedYear' => $team['intFormedYear'],
									        'strSport' => $team['strSport'],
									        'strLeague' => $team['strLeague'],
									        'idLeague' => $team['idLeague'],
									        'strLeague2' => $team['strLeague2'],
									        'idLeague2' => $team['idLeague2'],
									        'strLeague3' => $team['strLeague3'],
									        'idLeague3' => $team['idLeague3'],
									        'strLeague4' => $team['strLeague4'],
									        'idLeague4' => $team['idLeague4'],
									        'strLeague5' => $team['strLeague5'],
									        'idLeague5' => $team['idLeague5'],
									        'strLeague6' => $team['strLeague6'],
									        'idLeague6'=> $team['idLeague6'],
									        'strLeague7' => $team['strLeague7'],
									        'idLeague7' => $team['idLeague7'],
									        'strDivision' => $team['strDivision'],
									        'strManager' => $team['strManager'],
									        'strStadium' => $team['strStadium'],
									        'strKeywords' => $team['strKeywords'],
									        'strRSS' => $team['strRSS'],
									        'strStadiumThumb' => $team['strStadiumThumb'],
									        'strStadiumDescription' => $team['strStadiumDescription'],
									        'strStadiumLocation' => $team['strStadiumLocation'],
									        'intStadiumCapacity' => $team['intStadiumCapacity'],
									        'strWebsite' => $team['strWebsite'],
									        'strFacebook' => $team['strFacebook'],
									        'strTwitter' => $team['strTwitter'],
									        'strInstagram' => $team['strInstagram'],
									        'strDescriptionEN' => $team['strDescriptionEN'],
									        'strDescriptionDE' => $team['strDescriptionDE'],
									        'strDescriptionFR' => $team['strDescriptionFR'],
									        'strDescriptionCN' => $team['strDescriptionCN'],
									        'strDescriptionIT' => $team['strDescriptionIT'],
									        'strDescriptionJP' => $team['strDescriptionJP'],
									        'strDescriptionRU' => $team['strDescriptionRU'],
									        'strDescriptionES' => $team['strDescriptionES'],
									        'strDescriptionPT' => $team['strDescriptionPT'],
									        'strDescriptionSE' => $team['strDescriptionSE'],
									        'strDescriptionNL' => $team['strDescriptionNL'],
									        'strDescriptionHU' => $team['strDescriptionHU'],
									        'strDescriptionNO' => $team['strDescriptionNO'],
									        'strDescriptionIL' => $team['strDescriptionIL'],
									        'strDescriptionPL' => $team['strDescriptionPL'],
									        'strKitColour1' => $team['strKitColour1'],
									        'strKitColour2' => $team['strKitColour2'],
									        'strKitColour3' => $team['strKitColour3'],
									        'strGender' => $team['strGender'],
									        'strCountry' => $team['strCountry'],
									        'strTeamBadge' => $team['strTeamBadge'],
									        'strTeamJersey' => $team['strTeamJersey'],
									        'strTeamLogo' => $team['strTeamLogo'],
									        'strTeamFanart1' => $team['strTeamFanart1'],
									        'strTeamFanart2' => $team['strTeamFanart2'],
									        'strTeamFanart3' => $team['strTeamFanart3'],
									        'strTeamFanart4' => $team['strTeamFanart4'],
									        'strTeamBanner' => $team['strTeamBanner'],
									        'strYoutube' => $team['strYoutube'],
									        'strLocked' => $team['strLocked'],
									        'status' => '1',
											'created_at' => date("Y-m-d H:i:s")
										];
										Team::create($teamRecordDb);
									}
								}
							}
						}

					}
				}
				//dd('1 Sport');
			//}
			//foreach (array_chunk($teamRecordDb,1000) as $t) {
			     //DB::table('table_name')->insert($t);	
			//     Team::insert($t); 
			//}
			
		}
}
