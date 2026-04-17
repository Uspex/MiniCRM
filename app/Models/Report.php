<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    const STATUS_PENDING    = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_FAILED     = 'failed';

    const TYPE_COEFFICIENT   = 'coefficient';
    const TYPE_PRODUCTIVITY  = 'productivity';
    const TYPE_OPERATIONS    = 'operations';

    protected $fillable = [
        'user_id',
        'type',
        'date_from',
        'date_to',
        'filters',
        'file_path',
        'status',
    ];

    public static function getTypes(): array
    {
        return [
            self::TYPE_COEFFICIENT,
            self::TYPE_PRODUCTIVITY,
            self::TYPE_OPERATIONS,
        ];
    }

    protected $casts = [
        'date_from' => 'date',
        'date_to'   => 'date',
        'filters'   => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
