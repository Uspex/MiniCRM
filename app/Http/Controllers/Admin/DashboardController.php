<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Permission;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:'.Permission::PERMISSION_ANALYTICS_DASHBOARD, only: ['dashboard']),
        ];
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function dashboard(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::createFromFormat('d.m.Y', $request->date_from)->startOfDay()
            : now()->subDays(29)->startOfDay();

        $dateTo = $request->filled('date_to')
            ? Carbon::createFromFormat('d.m.Y', $request->date_to)->endOfDay()
            : now()->endOfDay();

        // Все даты за период
        $days = $dateFrom->diffInDays($dateTo) + 1;
        $dates = collect();
        for ($i = 0; $i < $days; $i++) {
            $dates->push($dateFrom->copy()->addDays($i)->format('Y-m-d'));
        }

        $isRoot = auth()->user()->hasRole(\App\Models\Role::ROLE_ROOT);

        $selectedUserIds = $isRoot
            ? array_filter((array) $request->input('user_id', []))
            : [auth()->id()];

        $selectedActivityIds = array_filter((array) $request->input('activity_id', []));
        $selectedShifts      = array_filter((array) $request->input('shift', []));

        // Граница рабочего дня берётся из первой смены в конфиге
        $dayStartHour = (int) explode(':', config('task.shifts.0.start', '00:00'))[0];

        // Сдвигаем диапазон фильтра, чтобы «день» начинался со старта первой смены
        $filterFrom = $dateFrom->copy()->addHours($dayStartHour);
        $filterTo   = $dateTo->copy()->addHours($dayStartHour);

        $query = Task::select(
            'activity_id',
            DB::raw('DATE(created_at - INTERVAL ' . $dayStartHour . ' HOUR) as date'),
            DB::raw('SUM(product_count) as total')
        )
            ->whereBetween('created_at', [$filterFrom, $filterTo])
            ->groupBy('activity_id', 'date');

        if (!empty($selectedUserIds)) {
            $query->whereIn('user_id', $selectedUserIds);
        }

        if (!empty($selectedActivityIds)) {
            $query->whereIn('activity_id', $selectedActivityIds);
        }

        if (!empty($selectedShifts)) {
            $query->whereIn('shift', $selectedShifts);
        }

        $rows = $query->get();

        // Пользователи для фильтра (только для root)
        $allUsers = $isRoot ? User::orderBy('name')->get() : collect();

        // Все типы работ для фильтра
        $allActivities = Activity::orderBy('name')->get();

        // Типы работ, по которым есть задачи за период
        $activityIds = $rows->pluck('activity_id')->unique();
        $activities = Activity::whereIn('id', $activityIds)->get()->keyBy('id');

        $datasets = [];
        foreach ($activities as $activity) {
            $data = $dates->map(function ($date) use ($rows, $activity) {
                $row = $rows->where('activity_id', $activity->id)->where('date', $date)->first();
                return $row ? (int) $row->total : 0;
            })->values()->toArray();

            $datasets[] = [
                'label' => $activity->name,
                'data'  => $data,
            ];
        }

        $chartData = [
            'labels'   => $dates->map(fn($d) => Carbon::parse($d)->format('d.m'))->values()->toArray(),
            'datasets' => $datasets,
        ];

        $allShifts = collect(config('task.shifts'))->map(fn($s, $i) => [
            'id'   => $i + 1,
            'name' => $s['name'],
        ]);

        return view('admin.dashboard', compact(
            'chartData', 'allUsers', 'selectedUserIds',
            'allActivities', 'selectedActivityIds',
            'allShifts', 'selectedShifts',
            'dateFrom', 'dateTo', 'isRoot'
        ));
    }
}
