<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model {

    use HasFactory;

    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user1',
        'user2',
        'type_id',
        'notification',
        'type',
        'status',
        'created_at',
        'updated_at'
    ];

    
}
