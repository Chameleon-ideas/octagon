<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model {

    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'sports';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'idSport',
        'strSport',
        'strFormat',
        'strSportThumb',
        'strSportIconGreen',
        'strSportDescription',
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

}
