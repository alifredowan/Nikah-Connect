@extends('layouts.app')

@section('title', 'Forgot Password - Nikah Connect')

@section('content')
<div class="py-16 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-8 transition-colors">
            
            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 flex items-center justify-center text-2xl mx-auto mb-3">
                    🛡️
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white font-heading">Forgot Password?</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    No worries. Enter your registered email address and we'll send you a secure link to reset your password.
                </p>
            </div>

            <!-- Session Status Alert -->
            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                    <span class="text-base">✓</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Developer Local Testing Quick Link -->
            @if (session('dev_reset_url'))
                <div class="mb-4 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800/80 text-xs text-amber-900 dark:text-amber-200">
                    <strong class="font-bold flex items-center gap-1 mb-1">
                        <span>⚡ Local Dev Shortcut:</span>
                    </strong>
                    <p class="text-[11px] text-slate-600 dark:text-slate-300 mb-2 leading-relaxed">
                        Because emails are logged in development mode, you can test directly:
                    </p>
                    <a href="{{ session('dev_reset_url') }}" class="inline-flex items-center gap-1 font-bold text-emerald-700 dark:text-emerald-400 hover:underline">
                        <span>Proceed to Reset Password</span> &rarr;
                    </a>
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-semibold text-rose-800 dark:text-rose-200">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                        Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                           placeholder="your.email@domain.com">
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-900/15 transition mt-2 flex items-center justify-center gap-2">
                    <span>Email Password Reset Link</span>
                </button>
            </form>

            <div class="text-center mt-6 pt-4 text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800">
                Remember your password? <a href="{{ route('login') }}" class="font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300">Sign in</a>
            </div>

        </div>
    </div>
</div>
@endsection
