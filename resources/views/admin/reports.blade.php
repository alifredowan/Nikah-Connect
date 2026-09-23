@extends('layouts.app')

@section('title', 'User Reports Moderation - Admin')

@section('content')
<div class="py-10 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400">&larr; Back to Admin Dashboard</a>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white font-heading mt-1">User Reports & Moderation Queue (FR-7.2)</h1>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase font-bold text-[10px] border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-4">Reported Candidate</th>
                        <th class="p-4">Reporter</th>
                        <th class="p-4">Reason / Violation</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Moderation Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($reports as $r)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4">
                                <strong class="text-slate-900 dark:text-white block font-bold text-sm">{{ $r->reported->name }}</strong>
                                <span class="text-slate-400 dark:text-slate-500">Account status: {{ $r->reported->is_active ? 'Active' : 'Suspended' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $r->reporter->name }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-bold text-rose-700 dark:text-rose-400 block capitalize">{{ str_replace('_', ' ', $r->reason) }}</span>
                                <span class="text-slate-500 dark:text-slate-400 line-clamp-1 italic">{{ $r->details ?? 'No additional note' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] {{ $r->status === 'actioned' ? 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60' : ($r->status === 'dismissed' ? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' : 'bg-rose-100 dark:bg-rose-950/70 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60') }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                @if($r->status === 'pending')
                                    <div class="inline-flex gap-1.5">
                                        <form action="{{ route('admin.reports.action', $r->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="warn">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-amber-100 dark:bg-amber-950/70 text-amber-900 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 font-bold hover:bg-amber-200 dark:hover:bg-amber-900/60 text-[11px] transition">
                                                Warn
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reports.action', $r->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="suspend">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-600 text-white font-bold hover:bg-rose-700 text-[11px] transition">
                                                Suspend
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reports.action', $r->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="dismiss">
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 font-medium text-[11px] transition">
                                                Dismiss
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 text-[11px]">Action: {{ $r->action_taken }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500">No reported content in the queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $reports->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
