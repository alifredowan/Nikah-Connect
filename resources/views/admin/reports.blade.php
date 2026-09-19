@extends('layouts.app')

@section('title', 'User Reports Moderation - Admin')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-700">&larr; Back to Admin Dashboard</a>
            <h1 class="text-2xl font-extrabold text-slate-900 font-heading mt-1">User Reports & Moderation Queue (FR-7.2)</h1>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="p-4">Reported Candidate</th>
                        <th class="p-4">Reporter</th>
                        <th class="p-4">Reason / Violation</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Moderation Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $r)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4">
                                <strong class="text-slate-900 block font-bold text-sm">{{ $r->reported->name }}</strong>
                                <span class="text-slate-400">Account status: {{ $r->reported->is_active ? 'Active' : 'Suspended' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-slate-700">{{ $r->reporter->name }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-bold text-rose-700 block capitalize">{{ str_replace('_', ' ', $r->reason) }}</span>
                                <span class="text-slate-500 line-clamp-1 italic">{{ $r->details ?? 'No additional note' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] {{ $r->status === 'actioned' ? 'bg-emerald-100 text-emerald-800' : ($r->status === 'dismissed' ? 'bg-slate-100 text-slate-600' : 'bg-rose-100 text-rose-800') }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                @if($r->status === 'pending')
                                    <div class="inline-flex gap-1.5">
                                        <form action="{{ route('admin.reports.action', $r->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="warn">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-amber-100 text-amber-900 font-bold hover:bg-amber-200 text-[11px]">
                                                Warn
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reports.action', $r->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="suspend">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-600 text-white font-bold hover:bg-rose-700 text-[11px]">
                                                Suspend
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reports.action', $r->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="dismiss">
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-slate-100 text-slate-600 font-medium hover:bg-slate-200 text-[11px]">
                                                Dismiss
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">Action: {{ $r->action_taken }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">No reported content in the queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4 border-t border-slate-100">
                {{ $reports->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
