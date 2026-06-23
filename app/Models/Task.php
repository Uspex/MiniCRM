<?php

namespace App\Models;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    // Статусы отмены операции (null/active — обычная операция, учитывается в отчётах)
    const CANCEL_REQUESTED = 'requested';
    const CANCEL_CANCELLED = 'cancelled';
    const CANCEL_REJECTED  = 'rejected';

    protected $fillable = [
        'user_id',
        'activity_id',
        'shift',
        'message',
        'product_count',
        'scheduled_runtime',
        'runtime',
        'work_day',
        'work_start',
        'work_finish',
        'status',
        'cancel_status',
        'cancel_reason',
        'cancel_requested_by',
        'cancel_requested_at',
        'cancel_processed_by',
        'cancel_processed_at',
        'cancel_decision_comment',
    ];

    protected $casts = [
        'cancel_requested_at' => 'datetime',
        'cancel_processed_at' => 'datetime',
    ];

    /**
     * Определяет номер смены (1-based) для заданного момента времени
     * на основе настроек Setting::TYPE_SHIFTS.
     */
    public static function resolveShift(Carbon $time): int
    {
        return self::resolveShiftData($time)['shift'];
    }

    /**
     * Определяет номер смены, время начала/окончания и рабочий день для заданного момента.
     *
     * @return array{shift: int, work_day: string, work_start: Carbon, work_finish: Carbon}
     */
    public static function resolveShiftData(Carbon $time): array
    {
        $shifts = Setting::get(Setting::TYPE_SHIFTS, []);
        $timeMinutes = $time->hour * 60 + $time->minute;

        foreach ($shifts as $shift) {
            [$startH, $startM] = explode(':', $shift['start']);
            [$endH,   $endM]   = explode(':', $shift['end']);
            $start = (int) $startH * 60 + (int) $startM;
            $end   = (int) $endH   * 60 + (int) $endM;

            $inShift = $start < $end
                ? $timeMinutes >= $start && $timeMinutes < $end
                : $timeMinutes >= $start || $timeMinutes < $end;

            if ($inShift) {
                $workStart = $time->copy()->startOfDay()->addMinutes($start);
                $workFinish = $time->copy()->startOfDay()->addMinutes($end);

                // Смена через полночь
                if ($start >= $end) {
                    if ($timeMinutes >= $start) {
                        $workFinish->addDay();
                    } else {
                        $workStart->subDay();
                    }
                }

                return [
                    'shift'       => (int) $shift['shift'],
                    'work_day'    => $workStart->format('Y-m-d'),
                    'work_start'  => $workStart,
                    'work_finish' => $workFinish,
                ];
            }
        }

        return [
            'shift'       => (int) ($shifts[0]['shift'] ?? 1),
            'work_day'    => $time->format('Y-m-d'),
            'work_start'  => $time->copy(),
            'work_finish' => $time->copy(),
        ];
    }

    /**
     * Вычисляет work_start/work_finish по номеру смены и рабочему дню.
     *
     * @return array{work_start: Carbon, work_finish: Carbon}
     */
    public static function resolveShiftTimes(int $shiftNumber, string $workDay): array
    {
        $shifts = Setting::get(Setting::TYPE_SHIFTS, []);
        $day = Carbon::parse($workDay)->startOfDay();

        foreach ($shifts as $shift) {
            if ((int) $shift['shift'] !== $shiftNumber) {
                continue;
            }

            [$startH, $startM] = explode(':', $shift['start']);
            [$endH,   $endM]   = explode(':', $shift['end']);
            $start = (int) $startH * 60 + (int) $startM;
            $end   = (int) $endH   * 60 + (int) $endM;

            $workStart  = $day->copy()->addMinutes($start);
            $workFinish = $day->copy()->addMinutes($end);

            if ($start >= $end) {
                $workFinish->addDay();
            }

            return [
                'work_start'  => $workStart,
                'work_finish' => $workFinish,
            ];
        }

        return [
            'work_start'  => $day->copy(),
            'work_finish' => $day->copy(),
        ];
    }

    /**
     * Только операции, идущие в основные отчёты: исключаются одобренные отмены.
     * Статусы requested/rejected по-прежнему учитываются.
     */
    public function scopeReportable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('cancel_status')
                ->orWhere('cancel_status', '!=', self::CANCEL_CANCELLED);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function cancelRequester()
    {
        return $this->belongsTo(User::class, 'cancel_requested_by');
    }

    public function cancelProcessor()
    {
        return $this->belongsTo(User::class, 'cancel_processed_by');
    }
}
