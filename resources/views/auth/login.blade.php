@extends('layouts.base', ['title' => 'Login'])

@section('css')
<style>
    @keyframes blob {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
    }
    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }
    @keyframes gradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
    .animation-delay-4000 {
        animation-delay: 4s;
    }
    .shimmer {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        background-size: 200% 100%;
        animation: shimmer 3s infinite;
    }
    .gradient-animate {
        background-size: 200% 200%;
        animation: gradient 5s ease infinite;
    }
    .float {
        animation: float 3s ease-in-out infinite;
    }
    .bg-gradient-animated {
        background: linear-gradient(-45deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #667eea 100%);
        background-size: 400% 400%;
        animation: gradient 15s ease infinite;
    }
</style>
@endsection

@section('content')
<div class="min-h-screen flex justify-center items-center relative overflow-hidden bg-gradient-animated">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
        
        <!-- Floating Particles -->
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-white/40 rounded-full float"></div>
        <div class="absolute top-3/4 right-1/4 w-3 h-3 bg-white/30 rounded-full float animation-delay-2000"></div>
        <div class="absolute bottom-1/4 left-3/4 w-2 h-2 bg-white/40 rounded-full float animation-delay-4000"></div>
    </div>

    <div class="relative w-full max-w-md mx-4 z-10">
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-3xl shadow-2xl p-10 border border-white/20 dark:border-gray-700/50 relative overflow-hidden">
            <!-- Shimmer Effect Overlay -->
            <div class="absolute inset-0 shimmer pointer-events-none"></div>
            
            <!-- Logo with Brand Name -->
            <div class="text-center mb-10 relative">
                <a href="{{ url('/') }}" class="inline-block">
                    <div class="inline-flex items-center justify-center mb-6">
                        <div class="relative">
                            <!-- Animated Rings -->
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 opacity-75 blur-lg animate-pulse"></div>
                            
                            <div class="relative flex items-center gap-4 px-6 py-3 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl gradient-animate">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-lg border border-white/30">
                                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <h1 class="text-2xl font-black text-white tracking-tight">KAN SAN</h1>
                                    <p class="text-xs text-white/80 font-medium">Lottery System</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-2 bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">Welcome Back!</h2>
                <p class="text-gray-600 dark:text-gray-400 font-medium">Thai Lottery Ticket Management</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-6 relative">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Email Address
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input id="email" type="email" 
                               class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl focus:border-transparent focus:ring-4 focus:ring-purple-500/30 focus:bg-white dark:focus:bg-gray-900 transition-all outline-none shadow-sm hover:shadow-md @error('email') border-red-500 @enderror" 
                               name="email" value="{{ old('email') }}" required autocomplete="email" autofocus 
                               placeholder="Enter your email">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Password
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input id="password" type="password" 
                               class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl focus:border-transparent focus:ring-4 focus:ring-purple-500/30 focus:bg-white dark:focus:bg-gray-900 transition-all outline-none shadow-sm hover:shadow-md @error('password') border-red-500 @enderror" 
                               name="password" required autocomplete="current-password" 
                               placeholder="Enter your password">
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center group cursor-pointer">
                        <input id="remember" type="checkbox" name="remember" 
                               class="w-5 h-5 text-purple-600 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:ring-2 cursor-pointer" 
                               {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="ml-3 text-sm text-gray-700 dark:text-gray-300 font-semibold cursor-pointer group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                            Remember me
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-bold text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition-colors">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="relative w-full bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 hover:from-blue-700 hover:via-purple-700 hover:to-pink-700 text-white font-black py-5 px-6 rounded-2xl shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-3 overflow-hidden group gradient-animate">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 shimmer"></div>
                    <span class="relative text-lg">Sign In</span>
                    <svg class="relative w-6 h-6 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    &copy; {{ date('Y') }} Kan San. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
