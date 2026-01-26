<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class RoutingController extends Controller
{

    public function index(Request $request)
    {
        return view('dashboards.index');
    }

    public function root(Request $request, $first)
    {
        // Security check: simple validation
        if (str_contains($first, '.') || str_contains($first, '/')) {
            abort(404);
        }

        if (!View::exists($first)) {
            abort(404);
        }
        return view($first);
    }

    public function secondLevel(Request $request, $first, $second)
    {
        // Security check: simple validation
        if (str_contains($first, '.') || str_contains($second, '.')) {
            abort(404);
        }

        $viewPath = $first . '.' . $second;

        if (!View::exists($viewPath)) {
            abort(404);
        }
        return view($viewPath);
    }

    public function thirdLevel(Request $request, $first, $second, $third)
    {
        if (str_contains($first, '.') || str_contains($second, '.') || str_contains($third, '.')) {
            abort(404);
        }

        $viewPath = $first . '.' . $second . '.' . $third;

        if (!View::exists($viewPath)) {
            abort(404);
        }
        return view($viewPath);
    }
}
