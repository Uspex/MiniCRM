<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ServerController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:' . Role::ROLE_ROOT),
        ];
    }

    public function index(): View
    {
        return view('admin.server.index');
    }

    public function update(): RedirectResponse
    {
        $script = base_path('deploy.sh');

        if (!file_exists($script)) {
            return redirect()->route('admin.server.index')
                ->with('error', __('server.deploy_script_not_found'));
        }

        $output = [];
        $exitCode = 0;

        exec("bash {$script} 2>&1", $output, $exitCode);

        $outputText = implode("\n", $output);

        if ($exitCode !== 0) {
            return redirect()->route('admin.server.index')
                ->with('error', __('server.update_error'))
                ->with('deploy_output', $outputText);
        }

        return redirect()->route('admin.server.index')
            ->with('success', __('server.update_success'))
            ->with('deploy_output', $outputText);
    }
}
