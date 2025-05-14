<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportMatch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'matches';

    public function matchTeams() {
        return $this->hasMany(MatchTeam::class, 'match_id', 'id');
    }

    public static function addMatch($arr) {
        $match = new SportMatch();
        $match->series_id = $arr['series_id'];
        $match->event_id = $arr['event_id'];
        $match->match_vs = $arr['team1_name'].' VS '.$arr['team2_name'];
        $match->start_date = $arr['start_date'];
        $match->end_date = $arr['end_date'];
        $match->save();
        return $match;
    }
}
