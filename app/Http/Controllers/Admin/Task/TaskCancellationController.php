<?php

namespace App\Http\Controllers\Admin\Task;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class TaskCancellationController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:'.Permission::PERMISSION_TASK_CANCEL_APPROVE),
        ];
    }

    /**
     * Список запросов на отмену (по умолчанию — ожидающие обработки).
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->get('cancel_status', Task::CANCEL_REQUESTED);

        $dateFrom = $request->filled('date_from')
            ? Carbon::createFromFormat('d.m.Y', $request->date_from)->startOfDay()
            : null;

        $dateTo = $request->filled('date_to')
            ? Carbon::createFromFormat('d.m.Y', $request->date_to)->endOfDay()
            : null;

        $paginator = Task::query()
            ->with(['user', 'activity', 'cancelRequester:id,name', 'cancelProcessor:id,name'])
            ->whereNotNull('cancel_status')
            ->when($statusFilter, fn($q) => $q->where('cancel_status', $statusFilter))
            ->when($request->get('user_id'), fn($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->filled('department'), fn($q) => $q->whereIn(
                'user_id',
                User::where('department', $request->input('department'))->pluck('id')
            ))
            ->when($request->get('activity_id'), fn($q) => $q->where('activity_id', $request->input('activity_id')))
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('work_day', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]))
            ->orderByDesc('cancel_requested_at')
            ->orderByDesc('id')
            ->paginate($this->perPage)
            ->withQueryString();

        $users      = User::orderBy('name')->get();
        $activities = Activity::orderBy('name')->get();
        $allDepartments = collect(Setting::get(Setting::TYPE_DEPARTMENTS, []))
            ->pluck('name')
            ->filter()
            ->values();

        $statuses = [
            Task::CANCEL_REQUESTED,
            Task::CANCEL_CANCELLED,
            Task::CANCEL_REJECTED,
        ];

        return view('admin.task.cancellation.index', compact(
            'paginator', 'users', 'activities', 'allDepartments',
            'statuses', 'statusFilter', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Одобрить отмену — операция исключается из основных отчётов.
     * Одобрить может только другой сотрудник (не автор запроса).
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        if ($task->cancel_status !== Task::CANCEL_REQUESTED) {
            return back()->with('error', __('task.cancel.already_processed'));
        }

        if ((int) $task->cancel_requested_by === auth()->id()) {
            return back()->with('error', __('task.cancel.cannot_self'));
        }

        $task->update([
            'cancel_status'           => Task::CANCEL_CANCELLED,
            'cancel_processed_by'     => auth()->id(),
            'cancel_processed_at'     => now(),
            'cancel_decision_comment' => $request->input('cancel_decision_comment'),
        ]);

        return redirect()
            ->route('admin.task.cancellation.index')
            ->with('success', __('task.cancel.approve_success'));
    }

    /**
     * Отклонить запрос на отмену — операция снова считается активной.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        if ($task->cancel_status !== Task::CANCEL_REQUESTED) {
            return back()->with('error', __('task.cancel.already_processed'));
        }

        $task->update([
            'cancel_status'           => Task::CANCEL_REJECTED,
            'cancel_processed_by'     => auth()->id(),
            'cancel_processed_at'     => now(),
            'cancel_decision_comment' => $request->input('cancel_decision_comment'),
        ]);

        return redirect()
            ->route('admin.task.cancellation.index')
            ->with('success', __('task.cancel.reject_success'));
    }
}
