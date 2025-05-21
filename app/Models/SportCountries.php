<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportCountries extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport_id',
        'country_id',
        'country_name',
        'country_iso_name',
        'logo_url',
    ];

    const SOCCER = 1;
    const BASKETBALL = 5;
    const CRICKET = 11;
    const BASEBALL = 4;
    const ICE_HOCKEY = 7;

    public function scopeIsSoccer($q)
    {
        $q->where('sport_id', 1);
    }

    public function scopeIsBasketball($q)
    {
        $q->where('sport_id', 5);
    }
}
