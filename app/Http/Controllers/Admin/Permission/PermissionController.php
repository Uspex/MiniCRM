<?php

namespace App\Http\Controllers\Admin\Permission;


use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\PermissionCreateRequest;
use App\Http\Requests\Permission\PermissionUpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Routing\Controllers\Middleware;


/**
 * Управление Разрешениями
 * Class PermissionController
 * @package App\Http\Controllers\Admin\Permission
 */
class PermissionController extends Controller
{

    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Role::ROLE_ROOT, only: ['index', 'create', 'store', 'edit', 'update', 'destroy']),
        ];
    }

    /**
     * Список
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {

        $paginator = Permission::paginate(20);

        return view('admin.permission.index', compact('paginator'));
    }

    /**
     * Страница создания
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $permission = new Permission();

        return view('admin.permission.create', compact('permission'));
    }

    /**
     * Создание записи в базе
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function store(PermissionCreateRequest $request)
    {

        $data = $request->all();

        $permission = new Permission($data);
        $permission->save();

        return redirect()
            ->route('admin.permission.edit', $permission->id)
            ->with(['success' => trans('common.create.success')]);
    }


    /**
     * Детальная информация
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);

        return view('admin.permission.edit', compact('permission'));
    }

    /**
     * Обновление данных
     *
     * @param PermissionUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PermissionUpdateRequest $request, $id)
    {
        $data = $request->all();

        $permission = Permission::findOrFail($id);
        $permission->update($data);


        return redirect()
            ->route('admin.permission.index')
            ->with(['success' => trans('common.update.success')]);

    }

    /**
     * Удаление
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        Permission::destroy($id);

        return redirect()
            ->route('admin.permission.index')
            ->with(['success' => trans('common.delete.success')]);
    }
}
