<?php

namespace App\Http\Controllers\Admin\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskCreateRequest;
use App\Http\Requests\Task\TaskUpdateRequest;
use App\Models\Activity;
use App\Models\Permission;
use App\Models\Task;
use App\Models\User;
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
        ];
    }

    /**
     * Список
     */
    public function index(Request $request): View
    {
        $isRoot = auth()->user()->hasRole(\App\Models\Role::ROLE_ROOT);

        $paginator = Task::query()
            ->with(['user', 'activity'])
            ->when($isRoot && $request->get('user_id'), fn($q) => $q->where('user_id', $request->input('user_id')))
            ->when(! $isRoot, fn($q) => $q->where('user_id', auth()->id()))
            ->when($request->get('activity_id'), fn($q) => $q->where('activity_id', $request->input('activity_id')))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $users      = User::orderBy('name')->get();
        $activities = Activity::orderBy('name')->get();

        return view('admin.task.index', compact('paginator', 'users', 'activities'));
    }

    /**
     * Страница создания
     */
    public function create(): View
    {
        $task       = new Task();
        $users      = User::orderBy('name')->get();
        $activities = Activity::orderBy('name')->get();

        return view('admin.task.create', compact('task', 'users', 'activities'));
    }

    /**
     * Создание записи в базе
     */
    public function store(TaskCreateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] ??= auth()->id();

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

        return view('admin.task.edit', compact('task', 'users', 'activities'));
    }

    /**
     * Обновление данных
     */
    public function update(TaskUpdateRequest $request, int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        $data = $request->validated();
        $data['user_id'] ??= auth()->id();

        $task->update($data);

        return redirect()
            ->route('admin.task.edit', $task->id)
            ->with(['success' => trans('common.update.success')]);
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
