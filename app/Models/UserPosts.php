<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPosts extends Model {

    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'user_posts';

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
        'title',
        'post',
        'type',
        'comment',
        'location',
        'share_url',
        'created_at',
        'updated_at',
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
