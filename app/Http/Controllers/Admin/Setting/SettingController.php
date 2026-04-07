<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SettingController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:' . Permission::PERMISSION_SETTING_EDIT),
        ];
    }

    /**
     * Список типов настроек
     */
    public function index(): View
    {
        $types = Setting::getTypeList();

        return view('admin.setting.index', compact('types'));
    }

    /**
     * Форма редактирования настройки
     */
    public function edit(string $type): View
    {
        abort_unless(in_array($type, Setting::getTypeList()), 404);

        $setting = Setting::where('type', $type)->first()
            ?? new Setting(['type' => $type, 'value' => []]);

        return view("admin.setting.{$type}.edit", compact('setting'));
    }

    /**
     * Сохранение настройки
     */
    public function update(Request $request, string $type): RedirectResponse
    {
        abort_unless(in_array($type, Setting::getTypeList()), 404);

        $value = match ($type) {
            Setting::TYPE_SHIFTS      => $this->validateShifts($request),
            Setting::TYPE_DEPARTMENTS => $this->validateDepartments($request),
        };

        Setting::set($type, $value);

        return redirect()
            ->route('admin.setting.edit', $type)
            ->with(['success' => trans('common.update.success')]);
    }

    private function validateShifts(Request $request): array
    {
        $data = $request->validate([
            'shifts'          => ['required', 'array', 'min:1'],
            'shifts.*.shift'  => ['required', 'integer', 'min:1'],
            'shifts.*.name'   => ['required', 'string', 'max:191'],
            'shifts.*.start'  => ['required', 'date_format:H:i'],
            'shifts.*.end'    => ['required', 'date_format:H:i'],
        ]);

        return array_values($data['shifts']);
    }

    private function validateDepartments(Request $request): array
    {
        $data = $request->validate([
            'departments'        => ['nullable', 'array'],
            'departments.*.name' => ['required', 'string', 'max:191'],
        ]);

        return array_values($data['departments'] ?? []);
    }
}
