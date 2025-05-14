<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchTeam extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'match_teams';

    public function matchScore() {
        return $this->hasMany(MatchScore::class, 'match_team_id', 'id');
    }

    public static function addTeam($arr) {
        $match_id = $arr['match_id'];
        $team_name = $arr['team_name'];
        $match_team = MatchTeam::where(['match_id' => $match_id, 'team_name' => $arr['team_name']])->firstOrNew();
        $match_team->match_id = $match_id;
        $match_team->team_name = $team_name;
        $match_team->save();
        return $match_team;
    }
}
