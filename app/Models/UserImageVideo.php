<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserImageVideo extends Model {

    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'user_image_video';

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
        'file_path',
        'type',
        'created_at',
        'thumb_url'
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
