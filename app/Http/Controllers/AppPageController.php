<?php

namespace App\Http\Controllers;

use App\Models\AppPage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AppPageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:mobile-app-manage', ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]);
    }

    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $type = $request->input('type', '');
        
        $query = AppPage::query();
        
        if (!empty($search)) {
            $query->where('page_name', 'like', '%' . $search . '%')
                  ->orWhere('page_key', 'like', '%' . $search . '%');
        }
        
        if (!empty($type)) {
            $query->where('page_type', $type);
        }
        
        $pages = $query->orderBy('page_type')->orderBy('id', 'DESC')->paginate(10);
        
        return view('app-pages.index', compact('pages', 'search', 'type'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    public function create(): View
    {
        return view('app-pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'page_key' => 'required|string|unique:app_pages,page_key',
            'page_name' => 'required|string|max:255',
            'content' => 'required|string',
            'page_type' => 'required|in:privacy,terms,about,faq',
            'is_published' => 'boolean',
        ]);

        try {
            $validated['public_slug'] = Str::slug($validated['page_name']) . '-' . time();
            AppPage::create($validated);
            
            return redirect()->route('app-pages.index')
                ->with('success', 'Page created successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while creating the page.')
                ->withInput();
        }
    }

    public function edit($id): View
    {
        $page = AppPage::findOrFail($id);
        
        return view('app-pages.edit', compact('page'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        try {
            $page = AppPage::findOrFail($id);

            $validated = $request->validate([
                'page_name' => 'required|string|max:255',
                'content' => 'required|string',
                'page_type' => 'required|in:privacy,terms,about,faq',
                'is_published' => 'boolean',
            ]);

            $page->update($validated);

            return redirect()->route('app-pages.index')
                ->with('success', 'Page updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while updating the page.')
                ->withInput();
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $page = AppPage::findOrFail($id);
            $page->delete();

            return redirect()->route('app-pages.index')
                ->with('success', 'Page deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('app-pages.index')
                ->with('error', 'An error occurred while deleting the page.');
        }
    }
}
