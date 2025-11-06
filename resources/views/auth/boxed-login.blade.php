@extends('layouts.base', ['title' => 'Login'])

@section('css')

@endsection

@section('content')
    <div
        class="bg-cover bg-no-repeat bg-[url('/images/auth-bg.jpg')] dark:bg-[url('/images/auth-bg-dark.jpg')] min-h-screen flex justify-center items-center">
        <div class="relative">
            <div class="bg-card/70 rounded-lg w-2/3 mx-auto">
                <div class="grid lg:grid-cols-12 grid-cols-1 items-center gap-0">
                    <div class="lg:col-span-5">
                        <div class="text-center px-10 py-12">
                            <div class="text-center">
                                <h4 class="mb-3 text-xl font-semibold text-purple-500">Welcome Back !</h4>
                                <p class="text-base text-default-500">Sign in to continue to
                                    Tailwick.
                                </p>
                            </div>
                            <!-- form -->
                            <form action="{{ route('login') }}"  method="POST" class="text-left w-full mt-10">
                                @csrf
                                <div class="mb-4">
                                    <label class="block font-medium text-default-900 text-sm mb-2" for="email">Email ID</label>
                                    <input class="form-input" id="email"  required name="email" placeholder="Enter Email"
                                           type="email"/>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="mb-4">
                                    <a class="text-primary font-medium text-sm mb-2 float-end"
                                        href="{{ route('password.request') }}">
                                            Forgot Password?
                                    </a>
                                    <label class="block font-medium text-default-900 text-sm mb-2" for="password">Password</label>
                                    <input class="form-input" id="password" name="password"  required placeholder="Enter Password" type="password"/>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input class="form-checkbox" id="remember" name="remember" type="checkbox"/>
                                    <label class="text-default-900 text-sm font-medium" for="checkbox-1">Remember
                                        Me</label>
                                </div>
                                <div class="mt-10 text-center">
                                    <button class="btn bg-primary text-white w-full" type="submit">Sign
                                        In
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div
                        class="lg:col-span-7 bg-card/60 mx-2 my-2 shadow-[0_14px_15px_-3px_#f1f5f9,0_4px_6px_-4px_#f1f5f9] dark:shadow-none rounded-lg">
                        <div class="pt-10 px-10 h-full">
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

@endsection
