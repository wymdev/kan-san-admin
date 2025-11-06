<?php

namespace App\Http\Controllers;

use App\Models\DrawInfo;
use Illuminate\Http\Request;

class DrawInfoController extends Controller
{
    // List all (admin)
    public function index()
    {
        $draws = DrawInfo::orderByDesc('draw_date')->paginate(10);
        return view('misc.drawinfo.index', compact('draws'));
    }

    // Show one (admin)
    public function show($draw)
    {
        $draw = DrawInfo::findOrFail($draw);
        return view('misc.drawinfo.show', compact('draw'));
    }

    // Show create form (admin)
    public function create()
    {
        return view('misc.drawinfo.create');
    }

    // Store new
    public function store(Request $request)
    {
        $request->validate([
            'draw_date' => 'required|date',
            'result_announce_date' => 'required|date',
            'period' => 'nullable|string',
            'is_estimated' => 'sometimes|boolean',
            'note' => 'nullable|string',
        ]);

        DrawInfo::create([
            'draw_date' => $request->draw_date,
            'result_announce_date' => $request->result_announce_date,
            'period' => $request->period,
            'is_estimated' => $request->has('is_estimated'),
            'note' => $request->note,
        ]);

        return redirect()->route('drawinfos.index')->with('success', 'Draw info created!');
    }

    // Show edit form
    public function edit($draw)
    {
        $draw = DrawInfo::findOrFail($draw);
        return view('misc.drawinfo.edit', compact('draw'));
    }

    // Update existing
    public function update(Request $request,$draw)
    {
        $draw = DrawInfo::findOrFail($draw);

        $request->validate([
            'draw_date' => 'required|date',
            'result_announce_date' => 'required|date',
            'period' => 'nullable|string',
            'is_estimated' => 'sometimes|boolean',
            'note' => 'nullable|string',
        ]);

        $draw->update([
            'draw_date' => $request->draw_date,
            'result_announce_date' => $request->result_announce_date,
            'period' => $request->period,
            'is_estimated' => $request->has('is_estimated'),
            'note' => $request->note,
        ]);

        return redirect()->route('drawinfos.index')->with('success', 'Draw info updated!');
    }

    // Delete
    public function destroy($draw)
    {
        $draw = DrawInfo::findOrFail($draw);
        $draw->delete();
        return redirect()->route('drawinfos.index')->with('success', 'Draw info deleted!');
    }
}
