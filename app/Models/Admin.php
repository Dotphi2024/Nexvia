<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasPermissions;
    protected $table = 'admins';
    protected $primaryKey = 'id';
    protected $guard = 'admin'; 

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'profile_pic',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
