<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $fillable = [
        'id',
        'last_name',
        'first_name',
        'mail',
        'password',
        'role'
    ];
    public $timestamps = true;
}