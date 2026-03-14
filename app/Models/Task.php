<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'activity_id',
        'message',
        'product_count',
        'scheduled_runtime',
        'runtime',
        'work_start',
        'work_finish',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
}
