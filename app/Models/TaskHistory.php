<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskHistory extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'task_id',
        'editor_id',
        'changes',
    ];

    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
    ];

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
