<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class CustomerUser extends Authenticatable
{
    protected $table = 'customer_users';
    protected $fillable = ['mobile'];
    protected $hidden = ['remember_token'];
}
