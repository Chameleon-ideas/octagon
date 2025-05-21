<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model {

    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'location';


    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'state_id',
        'state_code',
        'state_name',
        'country_id',
        'country_code',
        'country_name',
        'latitude',
        'longitude',
        'wikiDataId'
    ];

}
