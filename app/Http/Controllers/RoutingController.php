<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class RoutingController extends Controller
{

    public function index(Request $request)
    {
        return view('dashboards/index');
    }

    public function root(Request $request, $first)
    {
        // Check if view exists, otherwise return 404
        if (!View::exists($first)) {
            abort(404);
        }
        return view($first);
    }

    public function secondLevel(Request $request, $first, $second)
    {
        $viewPath = $first . '.' . $second;
        
        // Check if view exists, otherwise return 404
        if (!View::exists($viewPath)) {
            abort(404);
        }
        return view($viewPath);
    }

    public function thirdLevel(Request $request, $first, $second, $third)
    {
        $viewPath = $first . '.' . $second . '.' . $third;
        
        // Check if view exists, otherwise return 404
        if (!View::exists($viewPath)) {
            abort(404);
        }
        return view($viewPath);
    }
}
