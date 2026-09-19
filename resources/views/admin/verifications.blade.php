@extends('layouts.app')

@section('title', 'KYC Verification Requests - Admin')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-700">&larr; Back to Admin Dashboard</a>
                <h1 class="text-2xl font-extrabold text-slate-900 font-heading mt-1">Identity & KYC Verification Queue (FR-1.4, 7.1)</h1>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="p-4">Candidate</th>
                        <th class="p-4">Document Type</th>
                        <th class="p-4">Submitted</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($verifications as $v)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4">
                                <strong class="text-slate-900 block font-bold text-sm">{{ $v->user->name }}</strong>
                                <span class="text-slate-400">{{ $v->user->email }}</span>
                            </td>
                            <td class="p-4 capitalize font-semibold text-slate-800">
                                {{ str_replace('_', ' ', $v->document_type) }}
                            </td>
                            <td class="p-4 text-slate-400">
                                {{ $v->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] {{ $v->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($v->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ ucfirst($v->status) }}
                                </span>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                @if($v->status === 'pending')
                                    <form action="{{ route('admin.verifications.approve', $v->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 shadow-2xs">
                                            ✓ Approve & Badge
                                        </button>
                                    </form>
                                    <button type="button" onclick="openRejectModal({{ $v->id }})" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 font-bold text-xs hover:bg-rose-100">
                                        Reject
                                    </button>
                                @else
                                    <span class="text-slate-400 text-xs">Reviewed by {{ $v->reviewer?->name ?? 'Admin' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">No verification requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4 border-t border-slate-100">
                {{ $verifications->links() }}
            </div>
        </div>

    </div>
</div>

<!-- Reject Modal -->
<div id="rejectVerificationModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-slate-900 mb-2 font-heading">Reject Verification Request</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1">Rejection Reason</label>
                <textarea name="rejection_reason" required rows="3" class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-rose-500 outline-none" placeholder="e.g. Image was blurry or mismatched name on legal ID..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('rejectVerificationModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(id) {
        document.getElementById('rejectForm').action = '/admin/verifications/' + id + '/reject';
        document.getElementById('rejectVerificationModal').classList.remove('hidden');
    }
</script>
@endsection
