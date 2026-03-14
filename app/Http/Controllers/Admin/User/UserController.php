<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{

    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Role::ROLE_ROOT, only: ['index', 'create', 'store', 'edit', 'update', 'destroy']),
        ];
    }

    /**
     * dashboard
     */
    public function index(Request $request): View
    {
        $paginator = User::query()
            ->when($request->get('name'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            })
            ->when($request->get('email'), function ($query) use ($request) {
                $query->where('email', 'like', '%' . $request->input('email') . '%');
            })
            ->paginate($this->perPage);

        return view('admin.user.index', compact('paginator'));
    }


    /**
     * Страница создания
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $user = new User();

        return view('admin.user.create',compact('user'));
    }

    /**
     * Создание записи в базе
     *
     * @param UserCreateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserCreateRequest $request)
    {

        $data = $request->all();

        $user = UserService::create($data);

        return redirect()
            ->route('admin.user.index')
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
        $user = User::findOrFail($id);

        return view('admin.user.edit', compact('user'));
    }

    /**
     * Обновление данных
     *
     * @param UserUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserUpdateRequest $request, $id)
    {
        $data = $request->all();

        if($data['password']){
            $data['password'] = Hash::make($data['password']);
        }else {
            unset($data['password']);
        }

        $user = User::findOrFail($id);
        $user->update($data);

        $user->syncRoles($data['role']);

        return redirect()
            ->route('admin.user.edit', $user->id)
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
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()
            ->route('admin.user.index')
            ->with(['success' => trans('common.delete.success')]);
    }

    /**
     * Пользовательские настройки
     * @param Request $request
     * @return RedirectResponse
     */
    public function setSetting(Request $request)
    {
        if($request->get('lang')){
            Auth::user()->update(['lang' => $request->get('lang')]);
            Auth::user()->setLocale();
        }

        return redirect()
            ->back()
            ->with(['success' => trans('common.update.success')]);
    }

}
