<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Team extends Model {

    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'teams';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'idTeam',
        'idSoccerXML',
        'idAPIfootball',
		'intLoved',
		'strTeam',
		'strTeamShort',
        'strAlternate',
        'intFormedYear',
        'idSport',
        'strSport',
        'strLeague',
        'idLeague',
        'strLeague2',
        'idLeague2',
        'strLeague3',
        'idLeague3',
        'strLeague4',
        'idLeague4',
        'strLeague5',
        'idLeague5',
        'strLeague6',
        'idLeague6',
        'strLeague7',
        'idLeague7',
        'strDivision',
        'strManager',
        'strStadium',
        'strKeywords',
        'strRSS',
        'strStadiumThumb',
        'strStadiumDescription',
        'strStadiumLocation',
        'intStadiumCapacity',
        'strWebsite',
        'strFacebook',
        'strTwitter',
        'strInstagram',
        'strDescriptionEN',
        'strDescriptionDE',
        'strDescriptionFR',
        'strDescriptionCN',
        'strDescriptionIT',
        'strDescriptionJP',
        'strDescriptionRU',
        'strDescriptionES',
        'strDescriptionPT',
        'strDescriptionSE',
        'strDescriptionNL',
        'strDescriptionHU',
        'strDescriptionNO',
        'strDescriptionIL',
        'strDescriptionPL',
        'strKitColour1',
        'strKitColour2',
        'strKitColour3',
        'strGender',
        'idCountry',
        'strCountry',
        'strTeamBadge',
        'strTeamJersey',
        'strTeamLogo',
        'strTeamFanart1',
        'strTeamFanart2',
        'strTeamFanart3',
        'strTeamFanart4',
        'strTeamBanner',
        'strYoutube',
        'strLocked',
        'status',
        'created_at'
    ];

    public function countryDetail()
    {
        return $this->hasOne(SportCountries::class, 'country_id', 'idCountry')->select('*');
    }

    public static function addTeamFromAPI($team) {
        $teamId = $team['ID'];
        $football_api_url = 'https://team-api.livescore.com/v1/api/app/team/'.$teamId.'/details';

        $football_data = Http::withOptions([
            'verify' => false,
            'allow_redirects' => true
        ])->get($football_api_url);
		if($football_data->status() == 200) {
			$football_json = $football_data->body();
			if(!empty($football_json)) {
				$football_json = json_decode($football_json, true);
                $team_name = $football_json['Nm'];
                $team_image = '';
                if(!empty($football_json['Img'])) {
                    $team_image = 'https://lsm-static-prod.livescore.com/medium/'.$football_json['Img'];
                }
                $country_name = $football_json['CoNm'];

                $checkTeam = Team::where(['strTeam' => $team_name, 'status' => '0'])->first();
                if(empty($checkTeam)) {
                    $strTeamLogo = '';
                    if($team_image != '') {
                        $info = pathinfo($team_image);
                        $contents = file_get_contents($team_image);
                        $image_file_name = str_replace('/', '-', $team_name).'-'.$info['basename'];
                        Log::info("team_image: ".$team_image);
                        Log::info("image_file_name: ".$image_file_name);
                        $file = public_path('/uploads/teams/football/') . $image_file_name;
                        file_put_contents($file, $contents);
                        $strTeamLogo = 'uploads/teams/football/'.$image_file_name;
                    }

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

    protected static function boot() {
        parent::boot();
        static::creating(function ($query) {
            $query->created_at = date('Y-m-d H:i:s');
        });
        static::saving(function ($query) {
            $query->created_at = date('Y-m-d H:i:s');
        });
    }

}
