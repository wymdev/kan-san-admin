@extends('layouts.base', ['title' => 'Two Steps'])

@section('content')
    <div class="bg-cover bg-no-repeat bg-[url(/images/auth-bg.jpg)] dark:bg-[url('/images/auth-bg-dark.jpg')] min-h-screen flex justify-center items-center">
        <div class="relative">
            <div class="bg-card/70 rounded-lg w-2/3 mx-auto">
                <div class="grid lg:grid-cols-12 grid-cols-1 items-center gap-0">
                    <div class="lg:col-span-5">
                        <div class="text-center px-10 py-12">
                            <div class="mt-8">
                                <h4 class="mb-2 text-primary text-xl font-semibold">Verify Email</h4>
                                <p class="text-base mb-8 text-default-500">Please enter the 4 digit code sent to {{ auth()->user()->email }}</p>
                            </div>

                            @if ($errors->any())
                                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            @if (session('message'))
                                <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                                    {{ session('message') }}
                                </div>
                            @endif

                            <form action="{{ route('verify.otp') }}" method="POST" id="otpForm">
                                @csrf
                                <input type="hidden" name="otp" id="otpValue">
                                <div class="grid grid-cols-4 gap-2">
                                    <input class="form-input text-center otp-input" maxlength="1" placeholder="•" type="text"/>
                                    <input class="form-input text-center otp-input" maxlength="1" placeholder="•" type="text"/>
                                    <input class="form-input text-center otp-input" maxlength="1" placeholder="•" type="text"/>
                                    <input class="form-input text-center otp-input" maxlength="1" placeholder="•" type="text"/>
                                </div>
                                <div class="mt-10">
                                    <button class="btn bg-primary text-white w-full" type="submit">Confirm</button>
                                </div>
                            </form>

                            <div class="mt-6 text-center">
                                <p class="text-default-500 text-sm mb-4">Didn't receive the code?</p>
                                <form action="{{ route('resend.otp') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="text-primary font-medium text-sm hover:underline">
                                        Resend OTP
                                    </button>
                                </form>
                                <span class="text-default-400 mx-2">|</span>
                                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="text-default-600 font-medium text-sm hover:text-primary">
                                        Back to Login
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7 bg-card/60 mx-2 my-2 shadow-[0_14px_15px_-3px_#f1f5f9,0_4px_6px_-4px_#f1f5f9] dark:shadow-none rounded-lg">
                        <div class="pt-10 px-10 h-full">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <a href="{{ route('second', ['dashboards', 'index']) }}">
                                        <img alt="logo dark" class="h-6 block dark:hidden" src="/images/logo-dark.png"/>
                                        <img alt="" class="h-6 hidden dark:block" src="/images/logo-light.png"/>
                                    </a>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <img alt="" src="/images/boxed.png"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.otp-input');
        const form = document.getElementById('otpForm');
        const otpValue = document.getElementById('otpValue');

        function updateOTP() {
            let otp = '';
            inputs.forEach(input => otp += input.value);
            otpValue.value = otp;
        }

        inputs.forEach((input, idx) => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 1 && idx < inputs.length - 1) {
                    inputs[idx + 1].focus();
                }
                updateOTP();
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && idx > 0) {
                    inputs[idx - 1].focus();
                }
            });
            
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const data = e.clipboardData.getData('text').slice(0, 4);
                data.split('').forEach((char, i) => {
                    if (inputs[i]) inputs[i].value = char;
                });
                updateOTP();
                if (data.length === 4) inputs[3].focus();
            });
        });

        form.addEventListener('submit', function(e) {
            updateOTP();
            if (otpValue.value.length !== 4) {
                e.preventDefault();
                alert('Please enter the complete 4-digit OTP.');
            }
        });
    });
</script>
@endsection
