<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
        $this->middleware('permission:role-create', ['only' => ['create','store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::query();

        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        }

        $roles = $query->paginate(12)->appends(['search' => $search ?? null]); 
        $permissions = Permission::all();

        return view('misc.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $permission = Permission::all();
        return view('misc.roles.create', compact('permission'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permission' => 'required|array'
        ]);

        $role = Role::create(['name' => $request->name]);
        

        // Get permission names from IDs
        $permissions = Permission::whereIn('id', $request->permission)->pluck('name');

        // Attach permissions to role
        $role->givePermissionTo($permissions);

        return redirect()->route('roles.index')
                        ->with('success','Role created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $role = Role::with('permissions')->findOrFail($id);
        $rolePermissions = $role->permissions;
        return view('misc.roles.show', compact('role','rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")
            ->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();

        return view('misc.roles.edit', compact('role','permission','rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $role = Role::findOrFail($id);
    
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permission' => 'required|array'
        ]);

        $role->update(['name' => $request->name]);

        // Get permission names from IDs
        $permissions = Permission::whereIn('id', $request->permission)->pluck('name');
        
        // Sync permissions (replace all with new ones)
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')
                        ->with('success','Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $role = Role::findOrFail($id);
        $role->syncPermissions([]); // Remove permissions first (optional)
        $role->delete();
        return redirect()->route('roles.index')
                        ->with('success','Role deleted successfully');
    }
}
