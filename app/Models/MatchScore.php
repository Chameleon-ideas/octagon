<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchScore extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'match_scores';

    public static function addScore($arr) {
        $match_team_id = $arr['match_team_id'];
        $score = $arr['score'];
        $match_teamScore = MatchScore::where('match_team_id', $match_team_id)->firstOrNew();
        $match_teamScore->match_team_id = $match_team_id;
        $match_teamScore->score = $score;
        $match_teamScore->save();
        return $match_teamScore;
    }
}
