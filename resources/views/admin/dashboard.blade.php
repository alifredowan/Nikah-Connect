@extends('layouts.app')

@section('title', 'Admin Back Office - Nikah Connect')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 font-heading">Administrative & Moderation Console</h1>
                <p class="text-xs text-slate-500 mt-1">Platform governance, KYC verification queue, reports, and immutable audit logs (FR-7.1 - 7.5).</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.verifications') }}" class="px-3.5 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-xs transition">
                    🪪 KYC Queue ({{ $metrics['pending_verifications'] }})
                </a>
                <a href="{{ route('admin.reports') }}" class="px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-xs transition">
                    🚩 Reports ({{ $metrics['pending_reports'] }})
                </a>
                <a href="{{ route('admin.settings') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-xs transition">
                    ⚙️ Policy Settings
                </a>
            </div>
        </div>

        <!-- Metrics Grid (FR-7.4) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Users</span>
                <span class="text-2xl font-extrabold text-slate-900 font-heading">{{ $metrics['total_users'] }}</span>
                <span class="text-[10px] text-slate-500 block mt-1">{{ $metrics['seekers'] }} Seekers • {{ $metrics['walis'] }} Walis</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">KYC Verified</span>
                <span class="text-2xl font-extrabold text-blue-600 font-heading">{{ $metrics['verified_users'] }}</span>
                <span class="text-[10px] text-slate-500 block mt-1">{{ $metrics['pending_verifications'] }} pending review</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Active Chats</span>
                <span class="text-2xl font-extrabold text-emerald-600 font-heading">{{ $metrics['active_conversations'] }}</span>
                <span class="text-[10px] text-slate-500 block mt-1">Chaperoned sessions</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Active Reports</span>
                <span class="text-2xl font-extrabold text-rose-600 font-heading">{{ $metrics['pending_reports'] }}</span>
                <span class="text-[10px] text-slate-500 block mt-1">&lt; 24hr SLA target</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Paid Members</span>
                <span class="text-2xl font-extrabold text-amber-600 font-heading">{{ $metrics['paying_subscribers'] }}</span>
                <span class="text-[10px] text-slate-500 block mt-1">Premium / VIP</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Revenue</span>
                <span class="text-2xl font-extrabold text-slate-900 font-heading">${{ number_format($metrics['total_revenue'], 2) }}</span>
                <span class="text-[10px] text-slate-500 block mt-1">Stripe tokenized</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Pending KYC Verification Queue (FR-7.1) -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900 font-heading">Pending KYC Verifications</h2>
                    <a href="{{ route('admin.verifications') }}" class="text-xs text-blue-600 font-bold hover:underline">View All &rarr;</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentVerifications as $v)
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <strong class="text-slate-900 block font-bold">{{ $v->user->name }}</strong>
                                <span class="text-slate-500 capitalize">{{ str_replace('_', ' ', $v->document_type) }} • Submitted {{ $v->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.verifications.approve', $v->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-[11px] hover:bg-emerald-700">
                                        Approve
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6">No pending KYC verifications in the queue.</p>
                    @endforelse
                </div>
            </div>

            <!-- Pending User Reports Queue (FR-7.2) -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900 font-heading">User Reports & Moderation Queue</h2>
                    <a href="{{ route('admin.reports') }}" class="text-xs text-rose-600 font-bold hover:underline">View All &rarr;</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentReports as $r)
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <span class="font-bold text-slate-900">{{ $r->reported->name }}</span> reported by <span class="text-slate-600">{{ $r->reporter->name }}</span>
                                <span class="text-rose-700 block font-semibold capitalize">{{ str_replace('_', ' ', $r->reason) }}</span>
                            </div>
                            <a href="{{ route('admin.reports') }}" class="px-3 py-1.5 rounded-lg bg-slate-200 text-slate-800 font-bold text-[11px] hover:bg-slate-300">
                                Review
                            </a>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6">No user reports pending moderation.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Immutable Audit Log Summary (FR-7.5) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900 font-heading">Immutable Platform Audit Trail (FR-7.5)</h2>
                    <p class="text-xs text-slate-400">Chronological records of all administrative, moderation, and verification decisions.</p>
                </div>
                <a href="{{ route('admin.audit-logs') }}" class="text-xs text-emerald-700 font-bold hover:underline">Full Audit Log &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px]">
                        <tr>
                            <th class="p-3 rounded-l-xl">Timestamp</th>
                            <th class="p-3">User / Actor</th>
                            <th class="p-3">Action</th>
                            <th class="p-3">Target</th>
                            <th class="p-3">IP Address</th>
                            <th class="p-3 rounded-r-xl">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentAuditLogs as $log)
                            <tr class="hover:bg-slate-50/60">
                                <td class="p-3 font-mono text-slate-400">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                <td class="p-3 font-bold text-slate-900">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-md font-mono text-[11px] bg-slate-100 text-slate-800">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="p-3">{{ $log->target_type }} #{{ $log->target_id ?? '-' }}</td>
                                <td class="p-3 font-mono text-slate-400">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                <td class="p-3 truncate max-w-xs text-slate-500 font-mono text-[10px]">
                                    {{ $log->details ? json_encode($log->details) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
