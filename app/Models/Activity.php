<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'plan_quantity',
        'plan_time',
    ];
}
