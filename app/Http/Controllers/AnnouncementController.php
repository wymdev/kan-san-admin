<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AnnouncementController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->middleware('permission:mobile-app-manage', ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]);
        $this->pushService = $pushService;
    }

    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        
        $query = Announcement::with('creator');
        
        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%')
                  ->orWhere('body', 'like', '%' . $search . '%');
        }
        
        if ($status === 'sent') {
            $query->where('is_sent', true);
        } elseif ($status === 'pending') {
            $query->where('is_sent', false)->where('is_published', true);
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        }
        
        $announcements = $query->orderBy('created_at', 'DESC')->paginate(15);
        
        return view('announcements.index', compact('announcements', 'search', 'status'))
            ->with('i', ($request->input('page', 1) - 1) * 15);
    }

    public function create(): View
    {
        return view('announcements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'type' => 'required|in:general,promotion,maintenance,update',
            'is_published' => 'boolean',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
        ]);

        try {
            $validated['created_by'] = auth()->id();
            
            Announcement::create($validated);
            
            return redirect()->route('announcements.index')
                ->with('success', 'Announcement created successfully! ' . 
                      ($validated['is_published'] ?? false ? 'Notification sent.' : 'Saved as draft.'));
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create announcement: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id): View
    {
        $announcement = Announcement::with('creator')->findOrFail($id);
        return view('announcements.show', compact('announcement'));
    }

    public function edit($id): View
    {
        $announcement = Announcement::findOrFail($id);
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        try {
            $announcement = Announcement::findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string|max:1000',
                'type' => 'required|in:general,promotion,maintenance,update',
                'is_published' => 'boolean',
                'scheduled_at' => 'nullable|date|after_or_equal:now',
            ]);

            $announcement->update($validated);

            return redirect()->route('announcements.index')
                ->with('success', 'Announcement updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update announcement: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $announcement = Announcement::findOrFail($id);
            $announcement->delete();

            return redirect()->route('announcements.index')
                ->with('success', 'Announcement deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('announcements.index')
                ->with('error', 'Failed to delete announcement.');
        }
    }
}
