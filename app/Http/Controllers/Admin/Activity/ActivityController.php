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
use Illuminate\Support\Str;
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
        Activity::create([
            'name'          => $request->input('name'),
            'slug'          => $this->generateUniqueSlug($request->input('name')),
            'plan_quantity' => $request->input('plan_quantity'),
            'plan_time'     => $request->input('plan_time'),
        ]);

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

        $data = [
            'name'          => $request->input('name'),
            'plan_quantity' => $request->input('plan_quantity'),
            'plan_time'     => $request->input('plan_time'),
        ];

        if ($activity->name !== $request->input('name')) {
            $data['slug'] = $this->generateUniqueSlug($request->input('name'), $id);
        }

        $activity->update($data);

        return redirect()
            ->route('admin.activity.edit', $activity->id)
            ->with(['success' => trans('common.update.success')]);
    }

    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Activity::where('slug', $slug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
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
