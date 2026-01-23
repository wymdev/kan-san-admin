@extends('layouts.vertical', ['title' => 'New Transaction'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
        .sale-type-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .sale-type-card:hover {
            border-color: rgba(var(--primary-rgb), 0.3);
        }

        .sale-type-card.selected {
            border-color: rgba(var(--primary-rgb), 1);
            background: rgba(var(--primary-rgb), 0.05);
        }

        .customer-section {
            transition: all 0.3s ease;
        }

        .customer-section.hidden {
            opacity: 0.5;
            pointer-events: none;
        }

        .currency-toggle {
            display: flex;
            gap: 0.5rem;
        }

        .currency-btn {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid rgba(var(--primary-rgb), 0.3);
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .currency-btn.active {
            background: rgba(var(--primary-rgb), 0.1);
            border-color: rgba(var(--primary-rgb), 1);
            color: rgb(var(--primary-rgb));
        }

        .customer-search-result {
            background: rgba(var(--success-rgb), 0.1);
            border: 1px solid rgba(var(--success-rgb), 0.3);
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 0.5rem;
        }

        /* Enhanced Select Box Styling */
        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
            transition: all 0.2s ease-in-out;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background-color: #ffffff;
            font-size: 0.875rem;
            color: #374151;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .dark .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ca3af' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
            background-color: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
        }

        .form-select:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dark .form-select:hover {
            border-color: #60a5fa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background-color: #ffffff;
        }

        .dark .form-select:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
            background-color: #374151;
        }

        .form-select:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #f9fafb;
        }

        .dark .form-select:disabled {
            background-color: #1f2937;
        }

        /* Enhanced Input Styling */
        .form-input {
            transition: all 0.2s ease-in-out;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .dark .form-input {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
        }

        .form-input:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dark .form-input:hover {
            border-color: #60a5fa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .dark .form-input:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
        }

        /* Standardize form input small size to match selects */
        .form-input-sm {
            height: 2.5rem;
            padding: 0.625rem 0.875rem 0.625rem 2.25rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        /* Standardize button small size to match form elements */
        .btn-sm {
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        /* Enhanced Choices.js Styling */
        /* Standardized Choices.js Styling to match form-input */
        .choices__inner {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            /* slate-200 */
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            min-height: 42px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .choices__inner:focus-within,
        .choices.is-focused .choices__inner {
            border-color: rgb(var(--primary-rgb));
            box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.2);
        }

        .choices__list--dropdown {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            margin-top: 4px;
            padding: 0;
            z-index: 100 !important;
        }

        .choices__input {
            background-color: #f8fafc;
            border-radius: 0.375rem;
            padding: 8px 12px;
            margin-bottom: 4px;
            font-size: 0.875rem;
        }

        /* Ensure search input is visible in dropdown */
        .choices__list--dropdown .choices__input {
            display: block !important;
            width: calc(100% - 16px);
            margin: 8px;
            border: 1px solid #e2e8f0;
        }

        .choices__inner:focus,
        .choices[data-type*="select-one"].is-open .choices__inner {
            border-color: rgb(var(--primary-rgb));
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .choices__list--dropdown {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            margin-top: 0.25rem;
            z-index: 50;
        }

        .choices__list--dropdown .choices__item--selectable {
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
        }

        .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: rgba(var(--primary-rgb), 0.1);
            color: rgb(var(--primary-rgb));
        }

        .choices__input {
            font-size: 0.875rem;
            padding: 0.25rem 0;
        }

        .choices__placeholder {
            opacity: 0.5;
        }
    </style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Create Transaction'])

    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Transaction Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('secondary-transactions.store') }}" method="POST" id="transactionForm">
                @csrf

                {{-- Sale Type Selection --}}
                <div class="mb-6">
                    <h6 class="text-base font-semibold mb-3 flex items-center gap-2"><i class="size-4"
                            data-lucide="tag"></i> Sale Type</h6>
                    <input type="hidden" name="sale_type" id="saleTypeInput" value="own">

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="sale-type-card selected p-4 rounded-xl bg-white border" data-type="own"
                            onclick="selectSaleType('own')">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-success/10 rounded-full">
                                    <i class="text-success size-6" data-lucide="user-check"></i>
                                </div>
                                <div>
                                    <h6 class="font-semibold">Sell by My Own</h6>
                                    <p class="text-sm text-default-500">With customer info + generate public link</p>
                                </div>
                            </div>
                        </div>

                        <div class="sale-type-card p-4 rounded-xl bg-white border" data-type="other"
                            onclick="selectSaleType('other')">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-warning/10 rounded-full">
                                    <i class="text-warning size-6" data-lucide="users"></i>
                                </div>
                                <div>
                                    <h6 class="font-semibold">Sold by Other</h6>
                                    <p class="text-sm text-default-500">No customer info, mark as sold out</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ticket Selection --}}
                <div class="mb-6">
                    <h6 class="text-base font-semibold mb-3 flex items-center gap-2"><i class="size-4"
                            data-lucide="ticket"></i> Select Ticket(s) <span class="text-xs text-info">(Multiple selection allowed)</span></h6>

                    @if(isset($selectedTicket))
                        <input type="hidden" name="secondary_ticket_ids[]" value="{{ $selectedTicket->id }}">
                        <div class="p-4 bg-primary/5 rounded-lg border border-primary/20">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span
                                        class="font-mono text-2xl font-bold text-primary tracking-widest">{{ $selectedTicket->ticket_number }}</span>
                                    @if($selectedTicket->withdraw_date)
                                        <p class="text-sm text-default-500 mt-1">Draw:
                                            {{ $selectedTicket->withdraw_date->format('M d, Y') }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('secondary-transactions.create') }}"
                                    class="text-sm text-primary hover:underline">Change</a>
                            </div>
                        </div>
                    @else
                        <select name="secondary_ticket_ids[]" id="ticketSelect" multiple
                            class="form-select @error('secondary_ticket_ids') border-danger @enderror" required>
                            @foreach($tickets as $ticket)
                                <option value="{{ $ticket->id }}" {{ in_array($ticket->id, old('secondary_ticket_ids', [])) ? 'selected' : '' }}>
                                    {{ $ticket->ticket_number }}
                                    @if($ticket->withdraw_date) - Draw: {{ $ticket->withdraw_date->format('M d') }} @endif
                                    @if($ticket->batch_number) - Batch: {{ $ticket->batch_number }} @endif
                                    @if($ticket->price) - ฿{{ number_format($ticket->price, 0) }} @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-default-400 mt-2">💡 Hold Ctrl (Cmd on Mac) to select multiple tickets for the same customer</p>
                        @error('secondary_ticket_ids')
                            <span class="text-danger text-sm">{{ $message }}</span>
                        @enderror
                        @error('secondary_ticket_ids.*')
                            <span class="text-danger text-sm">{{ $message }}</span>
                        @enderror
                    @endif
                </div>

                {{-- Customer Section (only for "own" sales) --}}
                <div id="customerSection" class="customer-section mb-6">
                    <h6 class="text-base font-semibold mb-3 flex items-center gap-2"><i class="size-4"
                            data-lucide="user"></i> Customer Information</h6>

                    <div class="mb-4">
                        <label class="form-label">Search Existing Customer</label>
                        <select name="customer_id" id="customerSelect" class="form-select">
                            <option value="">Type to search customers...</option>
                            @foreach($customers ?? [] as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->full_name ?? 'No Name' }} - {{ $customer->phone_number }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-default-400 mt-1">Search by name or phone number</p>
                    </div>

                    <div class="p-3 bg-default-100 rounded-lg mb-4">
                        <p class="text-sm text-default-600 mb-2">Or create new customer:</p>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Phone Number <span class="text-xs text-warning">(or name
                                        below)</span></label>
                                <input type="text" name="customer_phone" id="customerPhone"
                                    value="{{ old('customer_phone') }}" class="form-input" placeholder="e.g., 0923471220">
                            </div>
                            <div>
                                <label class="form-label">Customer Name <span class="text-xs text-warning">(or phone
                                        above)</span></label>
                                <input type="text" name="customer_name" id="customerName" value="{{ old('customer_name') }}"
                                    class="form-input" placeholder="Enter customer name">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="create_customer" value="yes" class="form-checkbox" checked>
                                <span class="text-sm text-default-600">Create customer account if not exists (default
                                    password: password123)</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-3 bg-info/5 rounded-lg border border-info/20">
                        <p class="text-sm text-info flex items-center gap-2">
                            <i class="size-4" data-lucide="link"></i>
                            A unique public link will be generated for the customer to check their lottery results.
                        </p>
                    </div>
                </div>

                {{-- Transaction Details --}}
                <div class="mb-6">
                    <h6 class="text-base font-semibold mb-3 flex items-center gap-2"><i class="size-4"
                            data-lucide="banknote"></i> Payment Details</h6>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Amount (THB)</label>
                            <input type="number" name="amount_thb" value="{{ old('amount_thb') }}" step="0.01" min="0"
                                class="form-input @error('amount_thb') border-danger @enderror" placeholder="0.00">
                            @error('amount_thb')
                                <span class="text-danger text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Amount (MMK) <span
                                    class="text-xs text-default-400">optional</span></label>
                            <input type="number" name="amount_mmk" value="{{ old('amount_mmk') }}" step="0.01" min="0"
                                class="form-input @error('amount_mmk') border-danger @enderror" placeholder="0.00">
                            @error('amount_mmk')
                                <span class="text-danger text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="purchased_at"
                                value="{{ old('purchased_at', now()->format('Y-m-d\TH:i')) }}"
                                class="form-input @error('purchased_at') border-danger @enderror" required>
                            @error('purchased_at')
                                <span class="text-danger text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="paymentMethod" class="form-select">
                                <option value="">Not paid yet</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="PromptPay">PromptPay</option>
                                <option value="KBZPay">KBZPay</option>
                                <option value="WavePay">WavePay</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_paid" id="isPaid" value="1" class="form-checkbox">
                            <span class="text-sm text-default-600">Mark as paid</span>
                        </label>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-6">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="form-input"
                        placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="save"></i> Create Transaction
                    </button>
                    <a href="{{ route('secondary-transactions.index') }}" class="btn bg-default-200">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        // Initialize Choices.js for searchable selects
        document.addEventListener('DOMContentLoaded', function () {
            // Ticket select (multi-select enabled)
            const ticketSelect = document.getElementById('ticketSelect');
            if (ticketSelect) {
                new Choices(ticketSelect, {
                    searchEnabled: true,
                    removeItemButton: true,
                    itemSelectText: 'Press to select',
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Search and select tickets...',
                    searchPlaceholderValue: 'Type ticket number...',
                    noResultsText: 'No tickets found',
                    noChoicesText: 'No tickets available',
                    maxItemCount: -1, // Unlimited
                });
            }

            // Customer select
            const customerSelect = document.getElementById('customerSelect');
            if (customerSelect) {
                const choicesInstance = new Choices(customerSelect, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Search by name or phone...',
                    searchPlaceholderValue: 'Type name or phone...',
                    noResultsText: 'No customers found',
                    noChoicesText: 'No customers available',
                });
                // Store instance for later access
                customerSelect.choicesInstance = choicesInstance;
            }

            // Payment method select
            const paymentMethod = document.getElementById('paymentMethod');
            if (paymentMethod) {
                new Choices(paymentMethod, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Select payment method...',
                    searchPlaceholderValue: 'Search payment methods...',
                    noResultsText: 'No payment methods found',
                });
            }
        });

        function selectSaleType(type) {
            document.getElementById('saleTypeInput').value = type;

            document.querySelectorAll('.sale-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`.sale-type-card[data-type="${type}"]`).classList.add('selected');

            const customerSection = document.getElementById('customerSection');
            if (type === 'other') {
                customerSection.classList.add('hidden');
            } else {
                customerSection.classList.remove('hidden');
            }
        }

        // Payment method auto-check paid
        document.getElementById('paymentMethod').addEventListener('change', function () {
            document.getElementById('isPaid').checked = this.value !== '';
        });
        document.getElementById('isPaid').addEventListener('change', function () {
            if (!this.checked) {
                document.getElementById('paymentMethod').value = '';
            }
        });

        // When selecting existing customer, disable manual fields
        document.getElementById('customerSelect')?.addEventListener('change', function () {
            const phoneInput = document.getElementById('customerPhone');
            const nameInput = document.getElementById('customerName');
            
            if (this.value) {
                // Clear and disable manual fields when existing customer selected
                phoneInput.value = '';
                nameInput.value = '';
                phoneInput.disabled = true;
                nameInput.disabled = true;
                phoneInput.placeholder = 'Customer selected from dropdown';
                nameInput.placeholder = 'Customer selected from dropdown';
            } else {
                // Re-enable manual fields when dropdown is cleared
                phoneInput.disabled = false;
                nameInput.disabled = false;
                phoneInput.placeholder = 'e.g., 0923471220';
                nameInput.placeholder = 'Enter customer name';
            }
        });

        // When typing in manual fields, clear dropdown selection
        function clearCustomerDropdown() {
            const phoneInput = document.getElementById('customerPhone');
            const nameInput = document.getElementById('customerName');
            const customerSelect = document.getElementById('customerSelect');
            
            if ((phoneInput.value || nameInput.value) && customerSelect && customerSelect.value) {
                // Clear dropdown if manual entry is being used
                const choicesInstance = customerSelect.choicesInstance;
                if (choicesInstance) {
                    choicesInstance.setChoiceByValue('');
                }
            }
        }

        document.getElementById('customerPhone')?.addEventListener('input', clearCustomerDropdown);
        document.getElementById('customerName')?.addEventListener('input', clearCustomerDropdown);
    </script>
@endsection