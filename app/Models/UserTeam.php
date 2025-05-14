<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTeam extends Model {

    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'user_teams';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'team_id',
        'idTeam',
        'sport_id',
        'sport_api_id',
        'created_at',
        'is_deleted'
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($query) {
            $query->created_at = date('Y-m-d H:i:s');
        });
        static::saving(function ($query) {
            $query->created_at = date('Y-m-d H:i:s');
        });
    }
    public function teamInfo() {
        return $this->belongsTo('App\Models\Team', 'team_id','id');
    }
}
