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
     * Заблокирована ли операция для редактирования (на отмене или отменена).
     */
    private function isLocked(Task $task): bool
    {
        return in_array($task->cancel_status, [Task::CANCEL_REQUESTED, Task::CANCEL_CANCELLED], true);
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

        $histories = TaskHistory::with('editor:id,name')
            ->where('task_id', $task->id)
            ->orderByDesc('id')
            ->get();

        $task->loadMissing(['cancelRequester:id,name', 'cancelProcessor:id,name']);
        $locked = $this->isLocked($task);

        return view('admin.task.edit', compact('task', 'users', 'activities', 'allShifts', 'histories', 'locked'));
    }

    /**
     * Обновление данных
     */
    public function update(TaskUpdateRequest $request, int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        // Операцию на отмене / отменённую редактировать нельзя
        if ($this->isLocked($task)) {
            return redirect()
                ->route('admin.task.edit', $task->id)
                ->with('error', __('task.cancel.locked_notice'));
        }

        $data = $request->validated();

        if (!empty($data['shift']) && !empty($data['work_day'])) {
            $times = Task::resolveShiftTimes((int) $data['shift'], $data['work_day']);
            $data['work_start']  = $times['work_start'];
            $data['work_finish'] = $times['work_finish'];
        }

        $task->fill($data);

        // Фиксируем изменения значимых полей (влияют на коэффициент/отчёты)
        $tracked = ['activity_id', 'product_count', 'runtime', 'shift', 'work_day', 'message'];
        $changes = [];
        foreach ($tracked as $field) {
            if ($task->isDirty($field)) {
                $changes[$field] = [
                    'old' => $task->getOriginal($field),
                    'new' => $task->getAttribute($field),
                ];
            }
        }

        $task->save();

        if (!empty($changes)) {
            TaskHistory::create([
                'task_id'   => $task->id,
                'editor_id' => auth()->id(),
                'changes'   => $changes,
            ]);
        }

        return redirect()
            ->route('admin.task.edit', $task->id)
            ->with(['success' => trans('common.update.success')]);
    }

    /**
     * Запрос на отмену операции (сотрудник указывает причину).
     * Одобряет/отклоняет запрос другой сотрудник в разделе «Операции — отмена».
     */
    public function cancelRequest(Request $request, int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        // Без права «все пользователи» отменять можно только свои операции
        $canViewAll = auth()->user()->can(Permission::PERMISSION_TASK_ALL_USERS);
        if (!$canViewAll && $task->user_id !== auth()->id()) {
            abort(403);
        }

        // Запросить отмену можно только для активной или ранее отклонённой операции
        if (!in_array($task->cancel_status, [null, Task::CANCEL_REJECTED], true)) {
            return back()->with('error', __('task.cancel.not_requestable'));
        }

        $request->validate(
            ['cancel_reason' => ['required', 'string', 'max:65535']],
            ['cancel_reason.required' => __('task.cancel.reason_required')]
        );

        $task->update([
            'cancel_status'           => Task::CANCEL_REQUESTED,
            'cancel_reason'           => $request->input('cancel_reason'),
            'cancel_requested_by'     => auth()->id(),
            'cancel_requested_at'     => now(),
            // сбрасываем прошлое решение, если операция переотправляется после отклонения
            'cancel_processed_by'     => null,
            'cancel_processed_at'     => null,
            'cancel_decision_comment' => null,
        ]);

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
