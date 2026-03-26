<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'activity_id',
        'shift',
        'message',
        'product_count',
        'scheduled_runtime',
        'runtime',
        'work_start',
        'work_finish',
        'status',
    ];

    /**
     * Определяет номер смены (1-based) для заданного момента времени
     * на основе конфига config/task.php → shifts.
     */
    public static function resolveShift(Carbon $time): int
    {
        $shifts = config('task.shifts', []);
        $timeMinutes = $time->hour * 60 + $time->minute;

        foreach ($shifts as $shift) {
            [$startH, $startM] = explode(':', $shift['start']);
            [$endH,   $endM]   = explode(':', $shift['end']);
            $start = (int) $startH * 60 + (int) $startM;
            $end   = (int) $endH   * 60 + (int) $endM;

            $inShift = $start < $end
                ? $timeMinutes >= $start && $timeMinutes < $end          // обычная смена
                : $timeMinutes >= $start || $timeMinutes < $end;         // смена через полночь

            if ($inShift) {
                return (int) $shift['shift'];
            }
        }

        return (int) ($shifts[0]['shift'] ?? 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
}
