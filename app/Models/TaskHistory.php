<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskHistory extends Model
{
    const UPDATED_AT = null;

    // Все взаимодействия с операцией пишутся в историю
    const EVENT_UPDATED          = 'updated';
    const EVENT_EDIT_REQUESTED   = 'edit_requested';
    const EVENT_EDIT_APPROVED    = 'edit_approved';
    const EVENT_EDIT_REJECTED    = 'edit_rejected';
    const EVENT_CANCEL_REQUESTED = 'cancel_requested';
    const EVENT_CANCEL_APPROVED  = 'cancel_approved';
    const EVENT_CANCEL_REJECTED  = 'cancel_rejected';

    protected $fillable = [
        'task_id',
        'editor_id',
        'event',
        'changes',
        'comment',
        'decided_by',
        'decided_at',
        'decision_comment',
    ];

    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    /**
     * Записать событие истории.
     *
     * @param array<string, array{old: mixed, new: mixed}>|null $changes
     */
    public static function record(int $taskId, string $event, ?array $changes = null, ?string $comment = null, ?int $editorId = null): self
    {
        return self::create([
            'task_id'   => $taskId,
            'editor_id' => $editorId ?? auth()->id(),
            'event'     => $event,
            'changes'   => $changes ?: null,
            'comment'   => $comment ?: null,
        ]);
    }

    /**
     * Запись запроса, ожидающая решения (запрос правки или отмены).
     */
    public static function pendingRequestFor(int $taskId): ?self
    {
        return self::where('task_id', $taskId)
            ->whereNull('decided_at')
            ->whereIn('event', [self::EVENT_EDIT_REQUESTED, self::EVENT_CANCEL_REQUESTED])
            ->latest('id')
            ->first();
    }

    /**
     * Записать решение в ту же запись, что и запрос: событие меняется, решение дописывается.
     *
     * @param array<string, array{old: mixed, new: mixed}>|null $changes фактически применённый дифф
     */
    public function decide(string $event, ?string $comment = null, ?array $changes = null): void
    {
        $this->update([
            'event'            => $event,
            'changes'          => $changes ?: $this->changes,
            'decided_by'       => auth()->id(),
            'decided_at'       => now(),
            'decision_comment' => $comment ?: null,
        ]);
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
