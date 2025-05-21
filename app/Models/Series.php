<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'serieses';

    public static function addSeries($arr) {
        $series = new Series();
        $series->sport_id = $arr['sport_id'];
        $series->series_id = $arr['sid'];
        $series->series_name = $arr['series_name'];
        $series->series_code = $arr['series_code'];
        $series->series_place = $arr['series_place'];
        $series->save();
        return $series;
    }
}
