@extends('layouts.app')

@section('title', 'Platform Audit Logs - Admin')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-700">&larr; Back to Admin Dashboard</a>
            <h1 class="text-2xl font-extrabold text-slate-900 font-heading mt-1">Platform Immutable Audit Logs (FR-7.5)</h1>
            <p class="text-xs text-slate-500">Tamper-evident logs of security events, administrative decisions, and moderation actions.</p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="p-4">Timestamp (UTC)</th>
                        <th class="p-4">Actor</th>
                        <th class="p-4">Action Event</th>
                        <th class="p-4">Target Entity</th>
                        <th class="p-4">Client IP</th>
                        <th class="p-4">Metadata Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70">
                            <td class="p-4 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="p-4 font-sans font-bold text-slate-900">{{ $log->user?->name ?? 'Guest / System' }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-bold">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="p-4 font-sans">{{ $log->target_type }} #{{ $log->target_id ?? '-' }}</td>
                            <td class="p-4 text-slate-400">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            <td class="p-4 max-w-sm truncate text-slate-500 font-mono text-[10px]">
                                {{ $log->details ? json_encode($log->details) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 font-sans">No audit events logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
