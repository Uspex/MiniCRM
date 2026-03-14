<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
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

        $query = Task::select(
            'user_id',
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(product_count) as total')
        )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('user_id', 'date');

        if (!empty($selectedUserIds)) {
            $query->whereIn('user_id', $selectedUserIds);
        }

        $rows = $query->get();

        // Пользователи для фильтра (только для root)
        $allUsers = $isRoot ? User::orderBy('name')->get() : collect();

        // Пользователи, у которых есть задачи за период
        $userIds = $rows->pluck('user_id')->unique();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $colors = ['#798bff', '#e85347', '#1ee0ac', '#f4bd0e', '#09c2de', '#6576ff', '#ff63a5', '#364a63'];

        $datasets = [];
        foreach ($users as $user) {
            $color = $colors[($user->id - 1) % count($colors)];

            $data = $dates->map(function ($date) use ($rows, $user) {
                $row = $rows->where('user_id', $user->id)->where('date', $date)->first();
                return $row ? (int) $row->total : 0;
            })->values()->toArray();

            $datasets[] = [
                'label' => $user->name,
                'color' => $color,
                'data'  => $data,
            ];
        }

        $chartData = [
            'labels'   => $dates->map(fn($d) => Carbon::parse($d)->format('d.m'))->values()->toArray(),
            'datasets' => $datasets,
        ];

        return view('admin.dashboard', compact('chartData', 'allUsers', 'selectedUserIds', 'dateFrom', 'dateTo', 'isRoot'));
    }
}
