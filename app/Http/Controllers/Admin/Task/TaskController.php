<?php

namespace App\Http\Controllers\Admin\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskCreateRequest;
use App\Http\Requests\Task\TaskUpdateRequest;
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

class TaskController extends Controller
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:'.Permission::PERMISSION_TASK_LIST,    only: ['index']),
            new Middleware('permission:'.Permission::PERMISSION_TASK_CREATE,  only: ['create', 'store']),
            new Middleware('permission:'.Permission::PERMISSION_TASK_INFO,    only: ['edit']),
            new Middleware('permission:'.Permission::PERMISSION_TASK_UPDATE,  only: ['update']),
            new Middleware('permission:'.Permission::PERMISSION_TASK_DESTROY, only: ['destroy']),
            new Middleware('permission:'.Permission::PERMISSION_TASK_CANCEL_REQUEST, only: ['cancelRequest']),
        ];
    }

    /**
     * Сообщение о том, почему операция закрыта для изменений.
     */
    private function lockedMessage(Task $task): string
    {
        return $task->isCancelled()
            ? __('task.request.cancelled_notice')
            : __('task.request.pending_notice');
    }

    /**
     * Список
     */
    public function index(Request $request): View
    {
        $canViewAll = auth()->user()->can(Permission::PERMISSION_TASK_ALL_USERS);

        $dateFrom = $request->filled('date_from')
            ? Carbon::createFromFormat('d.m.Y', $request->date_from)->startOfDay()
            : null;

        $dateTo = $request->filled('date_to')
            ? Carbon::createFromFormat('d.m.Y', $request->date_to)->endOfDay()
            : null;

        $paginator = Task::query()
            ->with(['user', 'activity'])
            ->when($canViewAll && $request->get('user_id'), fn($q) => $q->where('user_id', $request->input('user_id')))
            ->when(! $canViewAll, fn($q) => $q->where('user_id', auth()->id()))
            ->when($canViewAll && $request->filled('department'), fn($q) => $q->whereIn(
                'user_id',
                User::where('department', $request->input('department'))->pluck('id')
            ))
            ->when($request->get('activity_id'), fn($q) => $q->where('activity_id', $request->input('activity_id')))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('shift'), fn($q) => $q->where('shift', $request->input('shift')))
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('work_day', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]))
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

        return view('admin.task.index', compact('paginator', 'users', 'activities', 'allShifts', 'allDepartments', 'canViewAll', 'dateFrom', 'dateTo'));
    }

    /**
     * Страница создания
     */
    public function create(): View
    {
        $task       = new Task();
        $users      = User::orderBy('name')->get();
        $activities = Activity::orderBy('name')->get();
        $allShifts  = collect(Setting::get(Setting::TYPE_SHIFTS, []))->map(fn($s) => [
            'id'   => (int) $s['shift'],
            'name' => $s['name'],
        ]);

        $shiftData    = Task::resolveShiftData(now());
        $currentShift = $shiftData['shift'];
        $currentWorkDay = $shiftData['work_day'];

        return view('admin.task.create', compact('task', 'users', 'activities', 'allShifts', 'currentShift', 'currentWorkDay'));
    }

    /**
     * Создание записи в базе
     */
    public function store(TaskCreateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] ??= auth()->id();

        if (!empty($data['shift']) && !empty($data['work_day'])) {
            $times = Task::resolveShiftTimes((int) $data['shift'], $data['work_day']);
        } else {
            $shiftData = Task::resolveShiftData(now());
            $data['shift']    = $shiftData['shift'];
            $data['work_day'] = $shiftData['work_day'];
            $times = ['work_start' => $shiftData['work_start'], 'work_finish' => $shiftData['work_finish']];
        }

        $data['work_start']  = $times['work_start'];
        $data['work_finish'] = $times['work_finish'];

        Task::create($data);

        return redirect()
            ->route('admin.task.index')
            ->with(['success' => trans('common.create.success')]);
    }

    /**
     * Страница редактирования
     */
    public function edit(int $id): View
    {
        $task       = Task::findOrFail($id);
        $users      = User::orderBy('name')->get();
        $activities = Activity::orderBy('name')->get();
        $allShifts  = collect(Setting::get(Setting::TYPE_SHIFTS, []))->map(fn($s) => [
            'id'   => (int) $s['shift'],
            'name' => $s['name'],
        ]);

        $histories = TaskHistory::with(['editor:id,name', 'decider:id,name'])
            ->where('task_id', $task->id)
            ->orderByDesc('id')
            ->get();

        $task->loadMissing(['requester:id,name', 'processor:id,name']);

        // Операцию с активным запросом или отменённую изменять нельзя
        $locked = $task->isLocked();

        // Без права task_update форма доступна только для просмотра
        $canUpdate = auth()->user()->can(Permission::PERMISSION_TASK_UPDATE);

        // С правом task_update_request изменения уходят на утверждение вместо сохранения
        $sendsForApproval = auth()->user()->can(Permission::PERMISSION_TASK_UPDATE_REQUEST);

        // Запросить отмену можно для своей операции (или любой — с правом task_all_users)
        $canCancelRequest = auth()->user()->can(Permission::PERMISSION_TASK_CANCEL_REQUEST)
            && $task->isRequestable()
            && (auth()->user()->can(Permission::PERMISSION_TASK_ALL_USERS) || $task->user_id === auth()->id());

        return view('admin.task.edit', compact(
            'task', 'users', 'activities', 'allShifts', 'histories',
            'locked', 'canUpdate', 'sendsForApproval', 'canCancelRequest'
        ));
    }

    /**
     * Обновление данных
     */
    public function update(TaskUpdateRequest $request, int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        if ($task->isLocked()) {
            return redirect()
                ->route('admin.task.edit', $task->id)
                ->with('error', $this->lockedMessage($task));
        }

        $data = $request->validated();

        // Изменения значимых полей (влияют на коэффициент/отчёты)
        $changes = $task->diffEditData($data);

        // С правом task_update_request правка не сохраняется, а уходит на утверждение
        if (auth()->user()->can(Permission::PERMISSION_TASK_UPDATE_REQUEST)) {
            if (empty($changes)) {
                return redirect()
                    ->route('admin.task.edit', $task->id)
                    ->with('error', __('task.request.no_changes'));
            }

            $reason = $request->input('request_reason');

            DB::transaction(function () use ($task, $data, $changes, $reason) {
                $task->update([
                    'request_type'             => Task::REQUEST_TYPE_EDIT,
                    'request_status'           => Task::REQUEST_REQUESTED,
                    'request_reason'           => $reason,
                    'request_changes'          => $changes,
                    'request_payload'          => $data,
                    'request_requested_by'     => auth()->id(),
                    'request_requested_at'     => now(),
                    'request_processed_by'     => null,
                    'request_processed_at'     => null,
                    'request_decision_comment' => null,
                ]);

                TaskHistory::record($task->id, TaskHistory::EVENT_EDIT_REQUESTED, $changes, $reason);
            });

            return redirect()
                ->route('admin.task.edit', $task->id)
                ->with('success', __('task.request.edit_sent'));
        }

        DB::transaction(function () use ($task, $data, $changes) {
            $task->applyEditData($data);
            $task->save();

            if (!empty($changes)) {
                TaskHistory::record($task->id, TaskHistory::EVENT_UPDATED, $changes);
            }
        });

        return redirect()
            ->route('admin.task.edit', $task->id)
            ->with(['success' => trans('common.update.success')]);
    }

    /**
     * Запрос на отмену операции (сотрудник указывает причину).
     * Одобряет/отклоняет запрос другой сотрудник в разделе «Операции — запросы».
     */
    public function cancelRequest(Request $request, int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        // Без права «все пользователи» отменять можно только свои операции
        $canViewAll = auth()->user()->can(Permission::PERMISSION_TASK_ALL_USERS);
        if (!$canViewAll && $task->user_id !== auth()->id()) {
            abort(403);
        }

        // Запросить отмену можно, только если операция не заблокирована другим запросом
        if (!$task->isRequestable()) {
            return back()->with('error', $this->lockedMessage($task));
        }

        $request->validate(
            ['request_reason' => ['required', 'string', 'max:65535']],
            ['request_reason.required' => __('task.cancel.reason_required')]
        );

        $reason = $request->input('request_reason');

        DB::transaction(function () use ($task, $reason) {
            $task->update([
                'request_type'             => Task::REQUEST_TYPE_CANCEL,
                'request_status'           => Task::REQUEST_REQUESTED,
                'request_reason'           => $reason,
                'request_changes'          => null,
                'request_payload'          => null,
                'request_requested_by'     => auth()->id(),
                'request_requested_at'     => now(),
                'request_processed_by'     => null,
                'request_processed_at'     => null,
                'request_decision_comment' => null,
            ]);

            TaskHistory::record($task->id, TaskHistory::EVENT_CANCEL_REQUESTED, null, $reason);
        });

        return redirect()
            ->route('admin.task.index')
            ->with('success', __('task.cancel.request_success'));
    }

    /**
     * Удаление
     */
    public function destroy(int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()
            ->route('admin.task.index')
            ->with(['success' => trans('common.delete.success')]);
    }
}
