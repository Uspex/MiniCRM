<?php

namespace App\Http\Controllers\Admin\Role;


use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleCreateRequest;
use App\Http\Requests\Role\RoleUpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Routing\Controllers\Middleware;


/**
 * Управление Ролями
 * Class RoleController
 * @package App\Http\Controllers\Admin\Role
 */
class RoleController extends Controller
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
        $paginator = Role::paginate(20);

        return view('admin.role.index', compact('paginator'));
    }

    /**
     * Страница создание
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {

        $role = new Role();

        $permissionList =  Permission::get()->groupBy('group');

        return view('admin.role.create' ,compact('role', 'permissionList'));
    }

    /**
     * Создание записи в базе
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function store(RoleCreateRequest $request)
    {

        $data = $request->all();

        $role = new Role($data);
        $role->save();

        $role->syncPermissions($data['role_permission']?? []);

        return redirect()
            ->route('admin.role.edit', $role->id)
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
        $role = Role::findOrFail($id);

        $permissionList =  Permission::get()->groupBy('group');

        return view('admin.role.edit', compact('role', 'permissionList'));
    }

    /**
     * Обновление данных
     *
     * @param RoleUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function update(RoleUpdateRequest $request, $id)
    {
        $data = $request->all();

        $role = Role::findOrFail($id);
        $role->update($data);

        $role->syncPermissions($data['role_permission']?? []);

        return redirect()
            ->route('admin.role.edit', $role->id)
            ->with(['success' => trans('common.update.success')]);

    }

    /**
     * Удаление
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Role::destroy($id);

        return redirect()
            ->route('admin.role.index')
            ->with(['success' => trans('common.delete.success')]);
    }
}
