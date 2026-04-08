<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'date_from',
        'date_to',
        'file_path',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
