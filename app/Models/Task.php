<?php

namespace App\Models;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    // Тип активного запроса по операции
    const REQUEST_TYPE_CANCEL = 'cancel';
    const REQUEST_TYPE_EDIT   = 'edit';

    // Статусы запроса (null — запроса нет, операция обычная и учитывается в отчётах)
    const REQUEST_REQUESTED = 'requested';
    const REQUEST_APPROVED  = 'approved';
    const REQUEST_REJECTED  = 'rejected';

    // Поля, изменения которых фиксируются в истории и в запросах на правку
    const TRACKED_FIELDS = ['activity_id', 'product_count', 'runtime', 'shift', 'work_day', 'message'];

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
        'request_type',
        'request_status',
        'request_reason',
        'request_changes',
        'request_payload',
        'request_requested_by',
        'request_requested_at',
        'request_processed_by',
        'request_processed_at',
        'request_decision_comment',
    ];

    protected $casts = [
        'request_changes'      => 'array',
        'request_payload'      => 'array',
        'request_requested_at' => 'datetime',
        'request_processed_at' => 'datetime',
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
     * Запросы на правку и незакрытые/отклонённые отмены на отчёты не влияют.
     */
    public function scopeReportable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('request_type')
                ->orWhere('request_type', '!=', self::REQUEST_TYPE_CANCEL)
                ->orWhere('request_status', '!=', self::REQUEST_APPROVED);
        });
    }

    /**
     * Операции с одобренной отменой.
     */
    public function scopeCancelled($query)
    {
        return $query->where('request_type', self::REQUEST_TYPE_CANCEL)
            ->where('request_status', self::REQUEST_APPROVED);
    }

    /**
     * Есть незакрытый запрос (на отмену или на правку).
     */
    public function hasPendingRequest(): bool
    {
        return $this->request_status === self::REQUEST_REQUESTED;
    }

    /**
     * Операция отменена — одобренный запрос на отмену.
     */
    public function isCancelled(): bool
    {
        return $this->request_type === self::REQUEST_TYPE_CANCEL
            && $this->request_status === self::REQUEST_APPROVED;
    }

    /**
     * Операцию нельзя изменять: висит запрос на утверждении либо она отменена.
     */
    public function isLocked(): bool
    {
        return $this->hasPendingRequest() || $this->isCancelled();
    }

    /**
     * Можно подать новый запрос: операция не заблокирована.
     */
    public function isRequestable(): bool
    {
        return ! $this->isLocked();
    }

    /**
     * Заполняет операцию данными правки, пересчитывая время смены.
     */
    public function applyEditData(array $data): void
    {
        if (!empty($data['shift']) && !empty($data['work_day'])) {
            $times = self::resolveShiftTimes((int) $data['shift'], $data['work_day']);
            $data['work_start']  = $times['work_start'];
            $data['work_finish'] = $times['work_finish'];
        }

        $this->fill($data);
    }

    /**
     * Изменения значимых полей относительно текущих данных операции.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function diffEditData(array $data): array
    {
        $draft = clone $this;
        $draft->fill($data);

        $changes = [];
        foreach (self::TRACKED_FIELDS as $field) {
            $old = $this->getOriginal($field);
            $new = $draft->getAttribute($field);

            if (self::valuesAreEqual($field, $old, $new)) {
                continue;
            }

            $changes[$field] = ['old' => $old, 'new' => $new];
        }

        return $changes;
    }

    /**
     * Сравнение значений поля: «10.00» и 10 — одно и то же, даты сравниваются по дню.
     */
    private static function valuesAreEqual(string $field, $old, $new): bool
    {
        $old = ($old === '' ? null : $old);
        $new = ($new === '' ? null : $new);

        if ($old === null || $new === null) {
            return $old === $new;
        }

        if ($field === 'work_day') {
            return Carbon::parse($old)->format('Y-m-d') === Carbon::parse($new)->format('Y-m-d');
        }

        if (is_numeric($old) && is_numeric($new)) {
            return (float) $old === (float) $new;
        }

        return (string) $old === (string) $new;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'request_requested_by');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'request_processed_by');
    }

    public function histories()
    {
        return $this->hasMany(TaskHistory::class, 'task_id');
    }
}
