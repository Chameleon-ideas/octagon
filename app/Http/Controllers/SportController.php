<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MatchScore;
use App\Models\MatchTeam;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Sport;
use App\Models\SportMatch;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Validator;

class SportController extends Controller {

		// Fetch record from 3-party api and store in DB
		public function storeSportDB() {
			// URL
			$apiURL = 'https://www.thesportsdb.com/api/v1/json/2/all_sports.php';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $apiURL);
			$res = curl_exec($ch);
			$data = json_decode($res, true);
			$sportRecordDb = [];
			$i=0;
			foreach($data as $sports) {
				foreach($sports as $sport) {
					//echo $sport['strSport']."<br>";
					$sportData = Sport::where('strSport', $sport['strSport'])->first();
					if($sportData === null) {
						echo $sport['strFormat']."<br>";
						$sportRecordDb[] = [
							'idSport'=>$sport['idSport'],
							'strSport'=>$sport['strSport'],
							'strFormat'=>$sport['strFormat'],
							'strSportThumb'=>$sport['strSportThumb'],
							'strSportIconGreen'=>$sport['strSportIconGreen'],
							'strSportDescription'=>$sport['strSportDescription'],
							'created_at' => date("Y-m-d H:i:s")
						];
					} else {
						$sportUpdate = Sport::find($sportData->id);
						$sportUpdate->idSport = $sport['idSport'];
						$sportUpdate->strSport = $sport['strSport'];
						$sportUpdate->strFormat = $sport['strFormat'];
						$sportUpdate->strSportThumb = $sport['strSportThumb'];
						$sportUpdate->strSportIconGreen = $sport['strSportIconGreen'];
						$sportUpdate->strSportDescription = $sport['strSportDescription'];
						$sportUpdate->update();
					}
				}
			}
			print_r($sportRecordDb);
			Sport::insert($sportRecordDb);

		}

    public function update_sport_score_data() {
        $football_api_url = 'https://prod-public-api.livescore.com/v1/api/app/date/soccer/'.date("Ymd").'/-7?locale=ca&MD=1';
        $basketball_api_url = 'https://prod-public-api.livescore.com/v1/api/app/date/basketball/'.date("Ymd").'/-7?locale=ca&MD=1';

        $football_data = Http::withOptions([
            'verify' => false,
            'allow_redirects' => true
        ])->get($football_api_url);
		if($football_data->status() == 200) {
			$football_json = $football_data->body();
			if(!empty($football_json)) {
				$football_json = json_decode($football_json, true);
				$Stages = $football_json['Stages'];
				if(!empty($Stages)) {
					foreach($Stages as $stage) {
						$sid = $stage['Sid'];
						$series_name = $stage['Snm'];
						$series_code = $stage['Scd'];
						$series_place = $stage['Cnm'];

                        $series = Series::where('series_id', $sid)->first();
                        if(empty($series)) {
                            $series = Series::addSeries(['sport_id' => 1, 'sid' => $sid, 'series_name' => $series_name, 'series_code' => $series_code, 'series_place' => $series_place]);
                        }

                        $series_id = $series->id;
						$events = $stage['Events'];
						if(!empty($events)) {
							foreach($events as $event) {
								$event_id = $event['Eid'];
								$Tr1 = (!empty($event['Tr1'])) ? $event['Tr1'] : '0';
								$Tr2 = (!empty($event['Tr2'])) ? $event['Tr2'] : '0';
								$Trh1 = (!empty($event['Trh1'])) ? $event['Trh1'] : '0';
								$Trh2 = (!empty($event['Trh2'])) ? $event['Trh2'] : '0';
								$Tr1OR = (!empty($event['Tr1OR'])) ? $event['Tr1OR'] : '0';
								$Tr2OR = (!empty($event['Tr2OR'])) ? $event['Tr2OR'] : '0';
								$T1 = (!empty($event['T1'])) ? $event['T1'] : [];
								$T2 = (!empty($event['T2'])) ? $event['T2'] : [];
								$Eps = (!empty($event['Eps'])) ? $event['Eps'] : [];
                                $match_start_date = $event['Esd'];
                                $match_end_date = (!empty($event['Ese'])) ? $event['Ese'] : '';
                                $match_start_date_Y = substr($match_start_date, 0, 4);
                                $match_start_date_M = substr($match_start_date, 4, 2);
                                $match_start_date_D = substr($match_start_date, 6, 2);
                                $match_start_date_Hr = substr($match_start_date, 8, 2);
                                $match_start_date_Mn = substr($match_start_date, 10, 2);
                                $match_start_date = $match_start_date_Y.'-'.$match_start_date_M.'-'.$match_start_date_D.' '.$match_start_date_Hr.':'.$match_start_date_Mn.':00';

                                if($match_end_date == '') {
                                    $match_end_date = date('Y-m-d 23:59:59', strtotime($match_start_date));
                                }
                                else {
                                    $match_end_date_Y = substr($match_end_date, 0, 4);
                                    $match_end_date_M = substr($match_end_date, 4, 2);
                                    $match_end_date_D = substr($match_end_date, 6, 2);
                                    $match_end_date_Hr = substr($match_end_date, 8, 2);
                                    $match_end_date_Mn = substr($match_end_date, 10, 2);
                                    $match_end_date = $match_end_date_Y.'-'.$match_end_date_M.'-'.$match_end_date_D.' '.$match_end_date_Hr.':'.$match_end_date_Mn.':00';
                                }

                                if(!empty($T1) && !empty($T2)) {
                                    $team1_name = $T1[0]['Nm'];
                                    $team2_name = $T2[0]['Nm'];

                                    $match_id = '';
                                    $checkMatch = SportMatch::where('event_id', $event_id)->first();
                                    if(!empty($checkMatch)) {
                                        $match_id = $checkMatch->id;
                                    }
                                    else {
                                        $checkTeam1 = Team::where(['strTeam' => $team1_name, 'status' => '0'])->first();
                                        $checkTeam2 = Team::where(['strTeam' => $team2_name, 'status' => '0'])->first();

                                        if(!empty($checkTeam1) && !empty($checkTeam2)) {
                                            $match = SportMatch::addMatch(['series_id' => $series_id, 'event_id' => $event_id, 'team1_name' => $team1_name, 'team2_name' => $team2_name, 'start_date' => $match_start_date, 'end_date' => $match_end_date]);
                                            $match_id = $match->id;
                                        }
                                        else {
                                            Log::info("---------------------");
                                            Log::info("Empty Team");
                                            Log::info("Empty Team 1: ".json_encode($T1));
                                            Log::info("Empty Team 2: ".json_encode($T2));
                                            Log::info("---------------------");
                                        }
                                    }
                                    if($match_id != '') {
										$match_team1 = MatchTeam::addTeam(['match_id' => $match_id, 'team_name' => $team1_name]);
										$match_team1_id = $match_team1->id;

										$match_team2 = MatchTeam::addTeam(['match_id' => $match_id, 'team_name' => $team2_name]);
										$match_team2_id = $match_team2->id;

										if($Tr1 != 0 || $Trh1 != 0 || $Tr1OR != 0) {
											MatchScore::addScore(['match_team_id' => $match_team1_id, 'score' => $Tr1]);
										}

										if($Tr2 != 0 || $Trh2 != 0 || $Tr2OR != 0) {
											MatchScore::addScore(['match_team_id' => $match_team2_id, 'score' => $Tr2]);
										}
                                    }
                                }
                                else {
                                    Log::info("---------------------");
                                    Log::info("Empty Team");
                                    Log::info("Empty Team 1: ".json_encode($T1));
                                    Log::info("Empty Team 2: ".json_encode($T2));
                                    Log::info("---------------------");
                                }
							}
						}
					}
				}
			}
		}

        $basketball_data = Http::withOptions([
            'verify' => false,
            'allow_redirects' => true
        ])->get($basketball_api_url);
		if($basketball_data->status() == 200) {
			$basketball_json = $basketball_data->body();
			if(!empty($basketball_json)) {
				$basketball_json = json_decode($basketball_json, true);
				$Stages = $basketball_json['Stages'];
				if(!empty($Stages)) {
					foreach($Stages as $stage) {
						$sid = $stage['Sid'];
						$series_name = $stage['Snm'];
						$series_code = $stage['Scd'];
						$series_place = $stage['Cnm'];

                        $series = Series::where('series_id', $sid)->first();
                        if(empty($series)) {
                            $series = Series::addSeries(['sport_id' => 5, 'sid' => $sid, 'series_name' => $series_name, 'series_code' => $series_code, 'series_place' => $series_place]);
                        }

                        $series_id = $series->id;
						$events = $stage['Events'];
						if(!empty($events)) {
							foreach($events as $event) {
								$event_id = $event['Eid'];
								$Tr1 = (!empty($event['Tr1'])) ? $event['Tr1'] : '0';
								$Tr2 = (!empty($event['Tr2'])) ? $event['Tr2'] : '0';
								$Tr1OR = (!empty($event['Tr1OR'])) ? $event['Tr1OR'] : '0';
								$Tr2OR = (!empty($event['Tr2OR'])) ? $event['Tr2OR'] : '0';
								$T1 = (!empty($event['T1'])) ? $event['T1'] : [];
								$T2 = (!empty($event['T2'])) ? $event['T2'] : [];
                                $match_start_date = $event['Esd'];
                                $match_end_date = (!empty($event['Ese'])) ? $event['Ese'] : '';
                                $match_start_date_Y = substr($match_start_date, 0, 4);
                                $match_start_date_M = substr($match_start_date, 4, 2);
                                $match_start_date_D = substr($match_start_date, 6, 2);
                                $match_start_date_Hr = substr($match_start_date, 8, 2);
                                $match_start_date_Mn = substr($match_start_date, 10, 2);
                                $match_start_date = $match_start_date_Y.'-'.$match_start_date_M.'-'.$match_start_date_D.' '.$match_start_date_Hr.':'.$match_start_date_Mn.':00';

                                if($match_end_date == '') {
                                    $match_end_date = date('Y-m-d 23:59:59', strtotime($match_start_date));
                                }
                                else {
                                    $match_end_date_Y = substr($match_end_date, 0, 4);
                                    $match_end_date_M = substr($match_end_date, 4, 2);
                                    $match_end_date_D = substr($match_end_date, 6, 2);
                                    $match_end_date_Hr = substr($match_end_date, 8, 2);
                                    $match_end_date_Mn = substr($match_end_date, 10, 2);
                                    $match_end_date = $match_end_date_Y.'-'.$match_end_date_M.'-'.$match_end_date_D.' '.$match_end_date_Hr.':'.$match_end_date_Mn.':00';
                                }
                                if(!empty($T1) && !empty($T2)) {
                                    $team1_name = $T1[0]['Nm'];
                                    $team2_name = $T2[0]['Nm'];

                                    $match_id = '';
                                    $checkMatch = SportMatch::where('event_id', $event_id)->first();
                                    if(!empty($checkMatch)) {
                                        $match_id = $checkMatch->id;
                                    }
                                    else {
                                        $checkTeam1 = Team::where(['strTeam' => $team1_name, 'status' => '0'])->first();
                                        $checkTeam2 = Team::where(['strTeam' => $team2_name, 'status' => '0'])->first();

                                        if(!empty($checkTeam1) && !empty($checkTeam2)) {
                                            $match = SportMatch::addMatch(['series_id' => $series_id, 'event_id' => $event_id, 'team1_name' => $team1_name, 'team2_name' => $team2_name, 'start_date' => $match_start_date, 'end_date' => $match_end_date]);
                                            $match_id = $match->id;
                                        }
                                        else {
                                            Log::info("---------------------");
                                            Log::info("Empty Team");
                                            Log::info("Empty Team 1: ".json_encode($T1));
                                            Log::info("Empty Team 2: ".json_encode($T2));
                                            Log::info("---------------------");
                                        }
                                    }
                                    if($match_id != '') {
										$match_team1 = MatchTeam::addTeam(['match_id' => $match_id, 'team_name' => $team1_name]);
										$match_team1_id = $match_team1->id;

										$match_team2 = MatchTeam::addTeam(['match_id' => $match_id, 'team_name' => $team2_name]);
										$match_team2_id = $match_team2->id;

										if($Tr1 != 0 || $Tr1OR != 0) {
											MatchScore::addScore(['match_team_id' => $match_team1_id, 'score' => $Tr1]);
										}

										if($Tr2 != 0 || $Tr2OR != 0) {
											MatchScore::addScore(['match_team_id' => $match_team2_id, 'score' => $Tr2]);
										}
                                    }
                                }
                                else {
                                    Log::info("---------------------");
                                    Log::info("Empty Team");
                                    Log::info("Empty Team 1: ".json_encode($T1));
                                    Log::info("Empty Team 2: ".json_encode($T2));
                                    Log::info("---------------------");
                                }
							}
						}
					}
				}
			}
		}
    }

    public function add_update_sport_teams() {
       /* $football_api_url = 'https://search-api.livescore.com/api/v2/search/soccer/team?limit=200&locale=ca&countryCode=IN&sCategories=false&sStages=false';

        $football_data = Http::withOptions([
            'verify' => false,
            'allow_redirects' => true
        ])->get($football_api_url);
		if($football_data->status() == 200) {
			$football_json = $football_data->body();
			if(!empty($football_json)) {
				$football_json = json_decode($football_json, true);
                $football_teams = $football_json['Teams'];
                if(!empty($football_teams)) {
                    foreach($football_teams as $team) {
                        $team_name = $team['Nm'];
                        $team_image = 'https://lsm-static-prod.livescore.com/medium/'.$team['Img'];
                        $country_name = $team['CoNm'];

                        $checkTeam = Team::where(['strTeam' => $team_name, 'status' => '0'])->first();
                        if(empty($checkTeam)) {
                            $info = pathinfo($team_image);
                            $contents = file_get_contents($team_image);
                            $image_file_name = $team_name.'-'.$info['basename'];
                            $file = public_path('/uploads/teams/football/') . $image_file_name;
                            file_put_contents($file, $contents);
                            $strTeamLogo = 'uploads/teams/football/'.$image_file_name;

                            $team = new Team();
                            $team->strTeam = $team_name;
                            $team->idSport = 1;
                            $team->strSport = 'Football';
                            $team->strCountry = $country_name;
                            $team->strTeamLogo = $strTeamLogo;
                            $team->save();

                            $team->idTeam = $team->id;
                            $team->save();
                        }

                    }
                }
            }
        } */

        $football_api_url = 'https://prod-public-api.livescore.com/v1/api/app/date/soccer/'.date("Ymd").'/-7?locale=ca&MD=1';
        $football_data = Http::withOptions([
            'verify' => false,
            'allow_redirects' => true
        ])->get($football_api_url);
		if($football_data->status() == 200) {
			$football_json = $football_data->body();
			if(!empty($football_json)) {
				$football_json = json_decode($football_json, true);
				$Stages = $football_json['Stages'];
				if(!empty($Stages)) {
					foreach($Stages as $stage) {
						$events = $stage['Events'];
						if(!empty($events)) {
							foreach($events as $event) {
								$T1 = (!empty($event['T1'])) ? $event['T1'] : [];
								$T2 = (!empty($event['T2'])) ? $event['T2'] : [];

                                if(!empty($T1)) {
                                    $T1 = $T1[0];
                                    $team1_name = $T1['Nm'];
                                    $checkTeam1 = Team::where(['strTeam' => $team1_name, 'status' => '0'])->first();
                                    if(empty($checkTeam1)) {
                                        Team::addTeamFromAPI($T1);
                                    }
                                }

                                if(!empty($T2)) {
                                    $T2 = $T2[0];
                                    $team2_name = $T2['Nm'];
                                    $checkTeam2 = Team::where(['strTeam' => $team2_name, 'status' => '0'])->first();
                                    if(empty($checkTeam2)) {
                                        Team::addTeamFromAPI($T2);
                                    }
                                }
							}
						}
					}
				}
			}
		}
    }
}
