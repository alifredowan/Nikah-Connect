@extends('layouts.app')

@section('title', 'Guardian (Wali) Settings - Nikah Connect')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 font-heading">Guardian (Wali) Linkage</h1>
                <p class="text-xs text-slate-500 mt-1">Configure guardian involvement to safeguard your marriage search with Islamic wisdom (FR-4.3, 6.2).</p>
            </div>
            <span class="text-xl">🛡️</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Form -->
            <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <h2 class="text-base font-bold text-slate-900 font-heading mb-4">Link or Invite a Guardian</h2>
                <form action="{{ route('wali.link.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Guardian's Full Name</label>
                        <input type="text" name="wali_name" required placeholder="e.g. Dr. Tariq Al-Mansoor"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Relationship to You</label>
                            <select name="relationship_type" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                                <option value="father">Father</option>
                                <option value="brother">Brother</option>
                                <option value="uncle">Uncle (Paternal / Maternal)</option>
                                <option value="grandfather">Grandfather</option>
                                <option value="other_mahram">Other Mahram</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Permission Level (FR-6.2)</label>
                            <select name="permission_level" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                                <option value="approve_required">Approval Required (Chat locked until Wali confirms)</option>
                                <option value="view_only">View-Only (Observer in chats & requests)</option>
                                <option value="full_proxy">Full Proxy (Manages profile communications)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Guardian's Email Address</label>
                            <input type="email" name="wali_email" placeholder="wali.email@domain.com"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Guardian's Phone / WhatsApp</label>
                            <input type="tel" name="wali_phone" placeholder="+1 555 123 4567"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs shadow-xs transition mt-2">
                        Save Guardian Link
                    </button>
                </form>
            </div>

            <!-- Right Info / Current Links -->
            <div class="space-y-6">
                <div class="bg-purple-50 rounded-2xl border border-purple-200 p-5 text-xs text-purple-900 leading-relaxed">
                    <h3 class="font-bold text-sm text-purple-950 mb-2 font-heading">Why Involve a Wali?</h3>
                    <p class="mb-2">In Islam, the guardian acts as an advocate and protector, providing wisdom, vetting prospective suitors, and ensuring respect throughout.</p>
                    <p>When "Approval Required" is selected, suitors cannot message you until your guardian has reviewed their profile and approved the introduction.</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Linked Guardians</h3>
                    @forelse($existingLinks as $link)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 mb-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900">{{ $link->wali_name ?? ($link->wali?->name ?? 'Guardian') }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">{{ $link->status }}</span>
                            </div>
                            <p class="text-slate-500 mt-1 capitalize">{{ $link->relationship_type }} • {{ str_replace('_', ' ', $link->permission_level) }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">No guardian currently linked.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
