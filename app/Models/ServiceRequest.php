<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = ['tracking_code','type','name','phone','description','status','target_system','external_id'];
}
