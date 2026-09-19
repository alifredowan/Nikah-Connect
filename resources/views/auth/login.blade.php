@extends('layouts.app')

@section('title', 'Sign In - Nikah Connect')

@section('content')
<div class="py-16 bg-slate-50">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl mx-auto mb-3">
                    🔑
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 font-heading">Welcome Back</h1>
                <p class="text-xs text-slate-500 mt-1">Sign in to review matches, interests, and chaperoned messages.</p>
            </div>

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                           placeholder="your.email@domain.com">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                    </div>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-600">
                        <input type="checkbox" name="remember" class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-900/15 transition mt-2">
                    Sign In
                </button>
            </form>

            <!-- Quick Demo One-Click Access -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <span class="block text-[11px] uppercase font-bold text-slate-400 tracking-wider text-center mb-3">
                    ⚡ Quick Demo Login (1-Click)
                </span>
                <div class="grid grid-cols-2 gap-2 text-xs font-medium">
                    <a href="{{ route('demo.login', 'groom') }}" class="p-2 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200 text-center transition">
                        🧔 Groom (Zayd)
                    </a>
                    <a href="{{ route('demo.login', 'bride') }}" class="p-2 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200 text-center transition">
                        🧕 Bride (Maryam)
                    </a>
                    <a href="{{ route('demo.login', 'wali') }}" class="p-2 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200 text-center transition">
                        🛡️ Wali (Father)
                    </a>
                    <a href="{{ route('demo.login', 'admin') }}" class="p-2 rounded-xl bg-slate-50 hover:bg-rose-50 hover:text-rose-800 border border-slate-200 text-center transition">
                        👑 Admin Console
                    </a>
                </div>
            </div>

            <div class="text-center mt-6 pt-4 text-xs text-slate-500">
                Looking to find a spouse? <a href="{{ route('register') }}" class="font-bold text-emerald-700 hover:text-emerald-800">Create a new profile</a>
            </div>
        </div>
    </div>
</div>
@endsection
