<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserWelcomeMail;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * Constructor - Apply middleware for role-based access control
     */
    public function __construct()
    {
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index','show']]);
        $this->middleware('permission:user-create', ['only' => ['create','store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of users with pagination
     */
    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        
        $query = User::query();
        
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
        }
        
        $users = $query->orderBy('id', 'DESC')->paginate(5);
        
        return view('misc.admin.index', compact('users', 'search'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new user
     */
    public function create(): View
    {
        $roles = Role::pluck('name','name')->all();
        
        return view('misc.admin.create', compact('roles'));
    }

    /**
     * Store a newly created user in the database
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|same:confirm-password',
            'roles' => 'required|array'
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.unique' => 'This email already exists.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.same' => 'The password and confirm password must match.',
            'roles.required' => 'Please select at least one role.'
        ]);

        try {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->assignRole($request->input('roles'));

            Mail::to($user->email)->send(
                new NewUserWelcomeMail($user->name, $user->email, $request->input('password'), route('login'))
            );

            return redirect()->route('users.index')
                ->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while creating the user.')
                ->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show($id): View
    {
        $user = User::findOrFail($id);
        
        return view('misc.admin.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id): View
    {
        $user = User::findOrFail($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();

        return view('misc.admin.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Update the specified user in the database
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'password' => 'nullable|string|min:8|same:confirm-password',
            'roles' => 'required|array'
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.unique' => 'This email already exists.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.same' => 'The password and confirm password must match.',
            'roles.required' => 'Please select at least one role.'
        ]);

        try {
            $input = $request->all();

            // Only update password if provided
            if (!empty($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            } else {
                $input = Arr::except($input, ['password', 'confirm-password']);
            }

            $user->update($input);
            $user->syncRoles($request->input('roles'));

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while updating the user.')
                ->withInput();
        }
    }

    /**
     * Remove the specified user from the database
     */
    public function destroy($id): RedirectResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'An error occurred while deleting the user.');
        }
    }
}
