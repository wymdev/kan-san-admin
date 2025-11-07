<?php

namespace App\Http\Controllers;

use App\Models\AppBanner;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class AppBannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:mobile-app-manage', ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]);
    }

    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $type = $request->input('type', '');
        
        $query = AppBanner::query();
        
        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
        }
        
        if (!empty($type)) {
            $query->where('banner_type', $type);
        }
        
        $banners = $query->orderBy('display_order')->orderBy('id', 'DESC')->paginate(10);
        
        return view('app-banners.index', compact('banners', 'search', 'type'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    public function create(): View
    {
        return view('app-banners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max, image types only
            'banner_type' => 'required|in:news,promotion,announcement',
            'action_url' => 'nullable|url',
            'action_type' => 'nullable|in:internal,external,deeplink',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'display_order' => 'integer|min:0',
        ]);

        try {
            // Handle file upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('banners', 'public');
                $validated['image_path'] = $imagePath; // Store relative path
            }

            AppBanner::create($validated);
            
            return redirect()->route('app-banners.index')
                ->with('success', 'Banner created successfully!');
        } catch (\Exception $e) {
            // Clean up uploaded file on error
            if (isset($validated['image_path'])) {
                Storage::disk('public')->delete($validated['image_path']);
            }
            return back()
                ->with('error', 'An error occurred while creating the banner.'.$e->getMessage())
                ->withInput();
        }
    }

    public function show($id): View
    {
        $banner = AppBanner::findOrFail($id);
        return view('app-banners.show', compact('banner'));
    }

    public function edit($id): View
    {
        $banner = AppBanner::findOrFail($id);
        return view('app-banners.edit', compact('banner'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        try {
            $banner = AppBanner::findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional for updates
                'banner_type' => 'required|in:news,promotion,announcement',
                'action_url' => 'nullable|url',
                'action_type' => 'nullable|in:internal,external,deeplink',
                'is_active' => 'boolean',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'display_order' => 'integer|min:0',
            ]);

            // Handle file upload if new image provided
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($banner->image_path) {
                    Storage::disk('public')->delete($banner->image_path);
                }
                $imagePath = $request->file('image')->store('banners', 'public');
                $validated['image_path'] = $imagePath;
            }

            $banner->update($validated);

            return redirect()->route('app-banners.index')
                ->with('success', 'Banner updated successfully!');
        } catch (\Exception $e) {
            // Clean up new upload on error
            if (isset($validated['image_path'])) {
                Storage::disk('public')->delete($validated['image_path']);
            }
            return back()
                ->with('error', 'An error occurred while updating the banner.')
                ->withInput();
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $banner = AppBanner::findOrFail($id);
            
            // Delete associated image
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            
            $banner->delete();

            return redirect()->route('app-banners.index')
                ->with('success', 'Banner deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('app-banners.index')
                ->with('error', 'An error occurred while deleting the banner.');
        }
    }
}
