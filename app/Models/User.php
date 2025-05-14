<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable {

    use HasApiTokens,
        HasFactory,
        Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'gender',
        'user_type',
        'profile_access',
        'mobile',
        'photo',
        'background',
        'dob',
        'bio',
        'country',
        'password',
        'fcm_token',
        'social_id',
        'created_at',
        'updated_at',
        'password',
        'is_deleted'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getPhotoAttribute($photo) {
        if($photo != '') {
            $photo = asset($photo);
        }
        return $photo;
    }

    public function likes() {
        return $this->hasMany('App\Models\UserLikes');
    }

    public function comments() {
        return $this->hasMany('App\Models\UserComments');
    }
}
