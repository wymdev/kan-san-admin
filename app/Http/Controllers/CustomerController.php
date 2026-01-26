<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Rules\ValidPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\LogsActivity;

class CustomerController extends Controller
{
    use LogsActivity;
    /**
     * Constructor - Apply middleware for role-based access control
     */
    public function __construct()
    {
        $this->middleware('permission:customer-list|customer-create|customer-edit|customer-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:customer-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:customer-edit', ['only' => ['edit', 'update']]);
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

        $customers = $query->latest()->paginate(5);

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
     * Display the specified customer with analytics
     */
    public function show($id): View
    {
        $customer = Customer::findOrFail($id);

        $analytics = [
            'total_purchases' => $customer->totalPurchases(),
            'total_spent' => number_format($customer->totalSpent(), 2),
            'win_count' => $customer->winCount(),
            'not_won_count' => $customer->notWonCount(),
            'pending_count' => $customer->pendingCount(),
            'approved_count' => $customer->approvedCount(),
            'rejected_count' => $customer->rejectedCount(),
            'win_rate' => $customer->winRate(),
            'total_prize_won' => number_format($customer->totalPrizeWon(), 2),
            'biggest_win' => $customer->biggestWin(),
            'avg_prize_per_win' => number_format($customer->averagePrizePerWin(), 2),
            'purchase_frequency' => $customer->purchaseFrequency(),
        ];

        $monthlyData = $customer->monthlyPurchases(6);
        $monthlyLabels = $monthlyData->pluck('month')->toArray();
        $monthlyWins = $monthlyData->pluck('wins')->toArray();
        $monthlySpent = $monthlyData->pluck('total_spent')->map(fn($v) => (float) $v)->toArray();

        $winLossTrend = $customer->winLossTrend();

        $recentPurchases = $customer->recentPurchases(10);

        return view('customers.show', compact('customer', 'analytics', 'monthlyLabels', 'monthlyWins', 'monthlySpent', 'winLossTrend', 'recentPurchases'));
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

    /**
     * Block a customer account
     */
    public function block(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'block_reason' => 'required|string|max:500'
        ]);

        try {
            $customer = Customer::findOrFail($id);

            if ($customer->is_blocked) {
                return back()->with('warning', 'Customer is already blocked.');
            }

            $oldValues = $customer->getAttributes();
            $customer->update([
                'is_blocked' => true,
                'blocked_at' => now(),
                'blocked_by' => auth()->id(),
                'block_reason' => $request->block_reason,
            ]);

            self::logAccountBlock(Customer::class, $id, $request->block_reason);

            return redirect()->back()
                ->with('success', 'Customer account has been blocked successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while blocking the customer.');
        }
    }

    /**
     * Unblock a customer account
     */
    public function unblock($id): RedirectResponse
    {
        try {
            $customer = Customer::findOrFail($id);

            if (!$customer->is_blocked) {
                return back()->with('warning', 'Customer is not blocked.');
            }

            $customer->update([
                'is_blocked' => false,
                'blocked_at' => null,
                'blocked_by' => null,
                'block_reason' => null,
            ]);

            self::logAccountUnblock(Customer::class, $id);

            return redirect()->back()
                ->with('success', 'Customer account has been unblocked successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while unblocking the customer.');
        }
    }

    /**
     * Export customer data (GDPR compliance)
     */
    public function exportGdpr(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        self::logDataExport('GDPR', $customer->id, null);

        $data = $this->getGdprData($customer);

        $filename = 'customer-gdpr-export-' . $customer->id . '-' . now()->format('Y-m-d-His') . '.json';

        if ($request->get('format') === 'json') {
            return response()->json($data)
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        }

        $html = view('customers.gdpr-export', compact('customer', 'data'))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"gdpr-export-{$customer->id}-" . now()->format('Y-m-d') . ".html\"");
    }

    private function getGdprData(Customer $customer): array
    {
        return [
            'export_info' => [
                'generated_at' => now()->toIso8601String(),
                'export_type' => 'GDPR Data Export',
                'version' => '1.0',
            ],
            'personal_information' => [
                'id' => $customer->id,
                'phone_number' => $customer->phone_number,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'gender' => $customer->gender,
                'date_of_birth' => $customer->dob?->toIso8601String(),
                'thai_pin' => $customer->thai_pin ? '***REDACTED***' : null,
                'address' => $customer->address,
                'created_at' => $customer->created_at->toIso8601String(),
                'updated_at' => $customer->updated_at->toIso8601String(),
            ],
            'account_status' => [
                'is_blocked' => $customer->is_blocked,
                'blocked_at' => $customer->blocked_at?->toIso8601String(),
                'block_reason' => $customer->block_reason,
            ],
            'purchases' => $customer->purchases()->with(['lotteryTicket', 'drawResult'])->get()->map(function ($purchase) {
                return [
                    'order_number' => $purchase->order_number,
                    'ticket_number' => $purchase->lotteryTicket->ticket_number ?? null,
                    'quantity' => $purchase->quantity,
                    'total_price' => $purchase->total_price,
                    'status' => $purchase->status,
                    'prize_won' => $purchase->prize_won,
                    'created_at' => $purchase->created_at->toIso8601String(),
                    'draw_date' => $purchase->lotteryTicket->draw_date ?? null,
                ];
            }),
            'push_tokens' => $customer->pushTokens->map(function ($token) {
                return [
                    'platform' => $token->platform,
                    'device_name' => $token->device_name,
                    'is_active' => $token->is_active,
                    'created_at' => $token->created_at->toIso8601String(),
                    'last_seen_at' => $token->last_seen_at?->toIso8601String(),
                ];
            }),
            'login_activities' => \App\Models\LoginActivity::where('user_type', Customer::class)
                ->where('user_id', $customer->id)
                ->orderBy('login_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($activity) {
                    return [
                        'ip_address' => $activity->ip_address,
                        'location' => $activity->location,
                        'device' => $activity->device_type,
                        'browser' => $activity->browser,
                        'status' => $activity->status,
                        'login_at' => $activity->login_at->toIso8601String(),
                    ];
                }),
            'activity_logs' => \App\Models\ActivityLog::where('actor_type', Customer::class)
                ->where('actor_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($log) {
                    return [
                        'action' => $log->action,
                        'description' => $log->description,
                        'context' => $log->context,
                        'ip_address' => $log->ip_address,
                        'created_at' => $log->created_at->toIso8601String(),
                    ];
                }),
        ];
    }

    /**
     * Export customers to Excel
     */
    public function export(Request $request)
    {
        $search = $request->input('search', '');
        return Excel::download(new CustomersExport($search), 'customers_' . date('Y-m-d_His') . '.xlsx');
    }
}
