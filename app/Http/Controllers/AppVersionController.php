<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AppVersionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:mobile-app-manage', ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]);
    }

    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $platform = $request->input('platform', '');
        
        $query = AppVersion::query();
        
        if (!empty($search)) {
            $query->where('version', 'like', '%' . $search . '%')
                  ->orWhere('release_notes', 'like', '%' . $search . '%');
        }
        
        if (!empty($platform)) {
            $query->where('platform', $platform);
        }
        
        $versions = $query->orderBy('version_code', 'DESC')
                         ->orderBy('display_order')
                         ->paginate(10);
        
        return view('app-versions.index', compact('versions', 'search', 'platform'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    public function create(): View
    {
        return view('app-versions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'version' => 'required|string|max:20',
            'version_code' => 'required|integer|min:1',
            'platform' => 'required|in:android,ios,both',
            'minimum_version' => 'nullable|string|max:20',
            'minimum_version_code' => 'nullable|integer|min:1',
            'force_update' => 'boolean',
            'release_notes' => 'nullable|string',
            'download_url' => 'nullable|url',
            'is_active' => 'boolean',
            'is_latest' => 'boolean',
            'release_date' => 'nullable|date',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'bug_fixes' => 'nullable|array',
            'bug_fixes.*' => 'string',
            'display_order' => 'integer|min:0',
        ]);

        try {
            // Check for duplicate version_code and platform
            $exists = AppVersion::where('version_code', $validated['version_code'])
                ->where('platform', $validated['platform'])
                ->exists();
            
            if ($exists) {
                return back()
                    ->with('error', 'Version code already exists for this platform.')
                    ->withInput();
            }

            AppVersion::create($validated);
            
            return redirect()->route('app-versions.index')
                ->with('success', 'App version created successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while creating the version: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id): View
    {
        $version = AppVersion::findOrFail($id);
        return view('app-versions.show', compact('version'));
    }

    public function edit($id): View
    {
        $version = AppVersion::findOrFail($id);
        return view('app-versions.edit', compact('version'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        try {
            $version = AppVersion::findOrFail($id);

            $validated = $request->validate([
                'version' => 'required|string|max:20',
                'version_code' => 'required|integer|min:1',
                'platform' => 'required|in:android,ios,both',
                'minimum_version' => 'nullable|string|max:20',
                'minimum_version_code' => 'nullable|integer|min:1',
                'force_update' => 'boolean',
                'release_notes' => 'nullable|string',
                'download_url' => 'nullable|url',
                'is_active' => 'boolean',
                'is_latest' => 'boolean',
                'release_date' => 'nullable|date',
                'features' => 'nullable|array',
                'features.*' => 'string',
                'bug_fixes' => 'nullable|array',
                'bug_fixes.*' => 'string',
                'display_order' => 'integer|min:0',
            ]);

            // Check for duplicate version_code and platform (excluding current record)
            $exists = AppVersion::where('version_code', $validated['version_code'])
                ->where('platform', $validated['platform'])
                ->where('id', '!=', $id)
                ->exists();
            
            if ($exists) {
                return back()
                    ->with('error', 'Version code already exists for this platform.')
                    ->withInput();
            }

            $version->update($validated);

            return redirect()->route('app-versions.index')
                ->with('success', 'App version updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while updating the version: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $version = AppVersion::findOrFail($id);
            $version->delete();

            return redirect()->route('app-versions.index')
                ->with('success', 'App version deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('app-versions.index')
                ->with('error', 'An error occurred while deleting the version.');
        }
    }
}
