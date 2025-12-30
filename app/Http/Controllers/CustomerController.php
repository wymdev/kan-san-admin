<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Rules\ValidPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class CustomerController extends Controller
{
    /**
     * Constructor - Apply middleware for role-based access control
     */
    public function __construct()
    {
        $this->middleware('permission:customer-list|customer-create|customer-edit|customer-delete', ['only' => ['index','show']]);
        $this->middleware('permission:customer-create', ['only' => ['create','store']]);
        $this->middleware('permission:customer-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:customer-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of customers with pagination
     */
    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        
        $query = Customer::query();
        
        if (!empty($search)) {
            $query->where('phone_number', 'like', '%' . $search . '%')
                  ->orWhere('full_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
        }
        
        $customers = $query->orderBy('id', 'DESC')->paginate(5);
        
        return view('customers.index', compact('customers', 'search'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new customer
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in the database
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validate($request, [
            'phone_number' => ['required', 'unique:customers,phone_number', new ValidPhoneNumber()],
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'phone_number.required' => 'The phone number field is required.',
            'phone_number.unique' => 'This phone number is already registered.',
            'full_name.required' => 'The full name field is required.',
            'email.unique' => 'This email already exists.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        try {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);

            Customer::create($input);

            return redirect()->route('customers.index')
                ->with('success', 'Customer created successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while creating the customer.')
                ->withInput();
        }
    }

    /**
     * Display the specified customer
     */
    public function show($id): View
    {
        $customer = Customer::findOrFail($id);
        
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit($id): View
    {
        $customer = Customer::findOrFail($id);

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in the database
     */
    public function update(Request $request, $id): RedirectResponse
    {
        try {
            $customer = Customer::findOrFail($id);

            // Remove empty password fields before validation to prevent false validation errors
            $requestData = $request->all();
            if (empty($requestData['password'])) {
                unset($requestData['password']);
                unset($requestData['password_confirmation']);
            }
            
            // Merge back the cleaned data
            $request->merge($requestData);

            // Build validation rules dynamically
            $rules = [
                'phone_number' => ['required', "unique:customers,phone_number,{$id}", new ValidPhoneNumber()],
                'full_name' => 'required|string|max:255',
                'email' => "nullable|email|unique:customers,email,{$id}",
                'gender' => 'nullable|in:M,F,Other',
                'dob' => 'nullable|date',
                'thai_pin' => 'nullable|string|unique:customers,thai_pin,' . $id,
                'address' => 'nullable|string|max:500',
            ];
            
            // Only add password validation if password is being changed
            if ($request->has('password') && !empty($request->password)) {
                $rules['password'] = 'required|string|min:8|confirmed';
            }

            $validated = $request->validate($rules, [
                'phone_number.required' => 'Phone number is required.',
                'phone_number.unique' => 'This phone number is already registered.',
                'full_name.required' => 'Full name is required.',
                'email.unique' => 'This email already exists.',
                'thai_pin.unique' => 'This PIN is already registered.',
                'password.required' => 'Password is required when changing password.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
            ]);

            $input = Arr::except($request->all(), ['_token', '_method', 'password', 'password_confirmation']);

            // Only update password if a new one was provided
            if ($request->has('password') && !empty($request->password)) {
                $input['password'] = Hash::make($request->password);
            }

            $customer->update($input);

            return redirect()->route('customers.index')
                ->with('success', 'Customer updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified customer from the database
     */
    public function destroy($id): RedirectResponse
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->delete();

            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')
                ->with('error', 'An error occurred while deleting the customer.');
        }
    }
}
