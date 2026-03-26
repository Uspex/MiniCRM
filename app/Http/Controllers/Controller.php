<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;

abstract class Controller implements HasMiddleware
{
    public $perPage = 50;

    public static function middleware(): array
    {
        return [];
    }
}
