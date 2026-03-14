<?php

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\ActivityCreateRequest;
use App\Http\Requests\Activity\ActivityUpdateRequest;
use App\Models\Activity;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ActivityController extends Controller
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:'.Permission::PERMISSION_ACTIVITY_LIST,   only: ['index']),
            new Middleware('permission:'.Permission::PERMISSION_ACTIVITY_CREATE, only: ['create', 'store']),
            new Middleware('permission:'.Permission::PERMISSION_ACTIVITY_INFO,   only: ['edit']),
            new Middleware('permission:'.Permission::PERMISSION_ACTIVITY_UPDATE, only: ['update']),
            new Middleware('permission:'.Permission::PERMISSION_ACTIVITY_DESTROY, only: ['destroy']),
        ];
    }

    /**
     * Список
     */
    public function index(Request $request): View
    {
        $paginator = Activity::query()
            ->when($request->get('name'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            })
            ->paginate($this->perPage);

        return view('admin.activity.index', compact('paginator'));
    }

    /**
     * Страница создания
     */
    public function create(): View
    {
        $activity = new Activity();

        return view('admin.activity.create', compact('activity'));
    }

    /**
     * Создание записи в базе
     */
    public function store(ActivityCreateRequest $request): RedirectResponse
    {
        Activity::create($request->all());

        return redirect()
            ->route('admin.activity.index')
            ->with(['success' => trans('common.create.success')]);
    }

    /**
     * Страница редактирования
     */
    public function edit(int $id): View
    {
        $activity = Activity::findOrFail($id);

        return view('admin.activity.edit', compact('activity'));
    }

    /**
     * Обновление данных
     */
    public function update(ActivityUpdateRequest $request, int $id): RedirectResponse
    {
        $activity = Activity::findOrFail($id);
        $activity->update($request->all());

        return redirect()
            ->route('admin.activity.edit', $activity->id)
            ->with(['success' => trans('common.update.success')]);
    }

    /**
     * Удаление
     */
    public function destroy(int $id): RedirectResponse
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();

        return redirect()
            ->route('admin.activity.index')
            ->with(['success' => trans('common.delete.success')]);
    }
}
