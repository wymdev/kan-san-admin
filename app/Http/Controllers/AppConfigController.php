<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AppConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:mobile-app-manage', ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]);
    }

    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        
        $query = AppConfig::query();
        
        if (!empty($search)) {
            $query->where('config_key', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
        }
        
        $configs = $query->orderBy('config_key')->paginate(10);
        
        return view('app-configs.index', compact('configs', 'search'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    public function create(): View
    {
        return view('app-configs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'config_key' => 'required|string|unique:app_configs,config_key|regex:/^[a-z_]+$/',
            'config_value' => 'required',
            'value_type' => 'required|in:string,json,integer,boolean',
            'description' => 'nullable|string|max:500',
        ], [
            'config_key.regex' => 'Config key must contain only lowercase letters and underscores.',
            'config_key.unique' => 'This config key already exists.',
        ]);

        try {
            AppConfig::create($validated);
            
            return redirect()->route('app-configs.index')
                ->with('success', 'Configuration created successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while creating the configuration.')
                ->withInput();
        }
    }

    public function edit($id): View
    {
        $config = AppConfig::findOrFail($id);
        
        return view('app-configs.edit', compact('config'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        try {
            $config = AppConfig::findOrFail($id);

            $validated = $request->validate([
                'config_value' => 'required',
                'value_type' => 'required|in:string,json,integer,boolean',
                'description' => 'nullable|string|max:500',
            ]);

            $config->update($validated);

            return redirect()->route('app-configs.index')
                ->with('success', 'Configuration updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while updating the configuration.')
                ->withInput();
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $config = AppConfig::findOrFail($id);
            $config->delete();

            return redirect()->route('app-configs.index')
                ->with('success', 'Configuration deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('app-configs.index')
                ->with('error', 'An error occurred while deleting the configuration.');
        }
    }
}
