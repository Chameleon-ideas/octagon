<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportLeagues extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport_id',
        'country_id',
        'league_id',
        'league_name',
        'league_year',
        'logo_url',
    ];

    public function scopeIsSoccer($q)
    {
        $q->where('sport_id', SportCountries::SOCCER);
    }

    public function scopeIsBasketball($q)
    {
        $q->where('sport_id', SportCountries::BASKETBALL);
    }

    public function scopeIsCricket($q)
    {
        $q->where('sport_id', SportCountries::CRICKET);
    }

    public function countryDetail()
    {
        return $this->hasOne(SportCountries::class, 'country_id', 'country_id')->select('*');
    }
}
