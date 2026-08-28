<?php

namespace App\Http\Controllers\Admin\Task;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Общий список запросов по операциям: отмены и правки.
 * Запрос хранится в самой операции (поля request_*), поэтому список — обычная выборка задач.
 */
class TaskRequestController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:'.Permission::PERMISSION_TASK_CANCEL_APPROVE),
        ];
    }

    /**
     * Список запросов (по умолчанию — ожидающие решения).
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->has('status') ? $request->get('status') : Task::REQUEST_REQUESTED;
        $typeFilter   = $request->get('type');

        $dateFrom = $request->filled('date_from')
            ? Carbon::createFromFormat('d.m.Y', $request->date_from)->startOfDay()
            : null;

        $dateTo = $request->filled('date_to')
            ? Carbon::createFromFormat('d.m.Y', $request->date_to)->endOfDay()
            : null;

        $paginator = Task::query()
            ->with(['user', 'activity', 'requester:id,name', 'processor:id,name'])
            ->whereNotNull('request_type')
            ->when($typeFilter, fn($q) => $q->where('request_type', $typeFilter))
            ->when($statusFilter, fn($q) => $q->where('request_status', $statusFilter))
            ->when($request->get('user_id'), fn($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->filled('department'), fn($q) => $q->whereIn(
                'user_id',
                User::where('department', $request->input('department'))->pluck('id')
            ))
            ->when($request->get('activity_id'), fn($q) => $q->where('activity_id', $request->input('activity_id')))
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('work_day', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]))
            ->orderByDesc('request_requested_at')
            ->orderByDesc('id')
            ->paginate($this->perPage)
            ->withQueryString();

        $users      = User::orderBy('name')->get();
        $activities = Activity::orderBy('name')->get();
        $allShifts  = collect(Setting::get(Setting::TYPE_SHIFTS, []))->map(fn($s) => [
            'id'   => (int) $s['shift'],
            'name' => $s['name'],
        ]);
        $allDepartments = collect(Setting::get(Setting::TYPE_DEPARTMENTS, []))
            ->pluck('name')
            ->filter()
            ->values();

        $types = [Task::REQUEST_TYPE_CANCEL, Task::REQUEST_TYPE_EDIT];
        $statuses = [Task::REQUEST_REQUESTED, Task::REQUEST_APPROVED, Task::REQUEST_REJECTED];

        return view('admin.task.request.index', compact(
            'paginator', 'users', 'activities', 'allShifts', 'allDepartments',
            'types', 'statuses', 'typeFilter', 'statusFilter', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Одобрить запрос: отмена исключает операцию из отчётов, правка применяет изменения.
     * Одобрить может только другой сотрудник (не автор запроса).
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        if (!$task->hasPendingRequest()) {
            return back()->with('error', __('task.request.already_processed'));
        }

        if ((int) $task->request_requested_by === auth()->id()) {
            return back()->with('error', __('task.request.cannot_self'));
        }

        $comment = $request->input('decision_comment');

        DB::transaction(function () use ($task, $comment) {
            $isEdit  = $task->request_type === Task::REQUEST_TYPE_EDIT;
            $changes = null;

            if ($isEdit) {
                $payload = $task->request_payload ?? [];

                // Дифф пересчитывается на момент применения — данные могли измениться
                $changes = $task->diffEditData($payload);
                $task->applyEditData($payload);
            }

            $task->fill([
                'request_status'           => Task::REQUEST_APPROVED,
                'request_processed_by'     => auth()->id(),
                'request_processed_at'     => now(),
                'request_decision_comment' => $comment,
            ])->save();

            $event = $isEdit ? TaskHistory::EVENT_EDIT_APPROVED : TaskHistory::EVENT_CANCEL_APPROVED;

            // Решение дописывается в запись запроса, отдельного события не создаём
            if ($history = TaskHistory::pendingRequestFor($task->id)) {
                $history->decide($event, $comment, $changes);
            } else {
                TaskHistory::record($task->id, $event, $changes, $comment);
            }
        });

        return redirect()
            ->route('admin.task.request.index')
            ->with('success', __('task.request.approve_success'));
    }

    /**
     * Отклонить запрос — операция остаётся без изменений и снова доступна для правок.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        if (!$task->hasPendingRequest()) {
            return back()->with('error', __('task.request.already_processed'));
        }

        $comment = $request->input('decision_comment');

        DB::transaction(function () use ($task, $comment) {
            $isEdit = $task->request_type === Task::REQUEST_TYPE_EDIT;

            $task->update([
                'request_status'           => Task::REQUEST_REJECTED,
                'request_processed_by'     => auth()->id(),
                'request_processed_at'     => now(),
                'request_decision_comment' => $comment,
            ]);

            $event = $isEdit ? TaskHistory::EVENT_EDIT_REJECTED : TaskHistory::EVENT_CANCEL_REJECTED;

            if ($history = TaskHistory::pendingRequestFor($task->id)) {
                $history->decide($event, $comment);
            } else {
                TaskHistory::record($task->id, $event, $isEdit ? $task->request_changes : null, $comment);
            }
        });

        return redirect()
            ->route('admin.task.request.index')
            ->with('success', __('task.request.reject_success'));
    }
}
