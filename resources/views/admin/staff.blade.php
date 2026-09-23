@extends('layouts.app')

@section('title', 'Staff & Access Control - Nikah Connect Admin')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen" x-data="{
    showCreateModal: false,
    showEditModal: false,
    editUser: { id: null, name: '', role: 'moderator', permissions: [] }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Top Header & Breadcrumb -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-700 transition">← Back to Dashboard</a>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 font-heading flex items-center gap-2">
                    <span>🛡️</span> Staff & Access Control
                </h1>
                <p class="text-xs text-slate-500 mt-1">Create Super Admins, assign Moderators, and configure granular permission access.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="showCreateModal = true"
                        class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-900/15 transition flex items-center gap-1.5">
                    <span>➕</span> Add New Staff Member
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-medium flex items-center justify-between">
                <span>✅ {{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-medium flex items-center justify-between">
                <span>⚠️ {{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Staff Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 font-heading">Staff Directory</h2>
                    <p class="text-xs text-slate-500">All registered system administrators and moderators.</p>
                </div>
                <span class="text-xs font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-full border border-slate-200">
                    Total: {{ $staffUsers->count() }} staff members
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-6">Staff Member</th>
                            <th class="py-3 px-6">Role</th>
                            <th class="py-3 px-6">Assigned Permissions</th>
                            <th class="py-3 px-6">Status</th>
                            <th class="py-3 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($staffUsers as $staff)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 text-sm">{{ $staff->name }}</div>
                                    <div class="text-slate-500 text-[11px]">{{ $staff->email }}</div>
                                    @if($staff->phone)
                                        <div class="text-slate-400 text-[10px]">{{ $staff->phone }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($staff->isSuperAdmin())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">
                                            👑 Super Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                            ⚖️ Moderator
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($staff->isSuperAdmin())
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            🌟 Full Platform Access (Unrestricted)
                                        </span>
                                    @else
                                        <div class="flex flex-wrap gap-1.5 max-w-md">
                                            @php
                                                $userPerms = $staff->permissions ?? [];
                                            @endphp
                                            @if(empty($userPerms))
                                                <span class="text-slate-400 italic text-[11px]">No active permissions assigned</span>
                                            @else
                                                @foreach($userPerms as $perm)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                        {{ $availablePermissions[$perm] ?? $perm }}
                                                    </span>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($staff->is_active)
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-[11px]">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-slate-400 font-bold text-[11px]">
                                            <span class="w-2 h-2 rounded-full bg-slate-300"></span> Suspended
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    @if(!$staff->isSuperAdmin() || Auth::id() === $staff->id)
                                        <button type="button"
                                                @click="editUser = {
                                                    id: {{ $staff->id }},
                                                    name: '{{ addslashes($staff->name) }}',
                                                    role: '{{ $staff->role }}',
                                                    permissions: {{ json_encode($staff->permissions ?? []) }}
                                                }; showEditModal = true;"
                                                class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-[11px] transition">
                                            ⚙️ Permissions
                                        </button>
                                    @endif

                                    @if($staff->id !== Auth::id())
                                        <form action="{{ route('admin.staff.toggle-status', $staff->id) }}" method="POST" class="inline-block"
                                              onsubmit="return confirm('Are you sure you want to {{ $staff->is_active ? 'deactivate' : 'activate' }} this staff member?');">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg font-bold text-[11px] transition {{ $staff->is_active ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }}">
                                                {{ $staff->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Create Staff Modal -->
    <div x-show="showCreateModal" style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCreateModal = false"
             class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 font-heading">Add New Staff Member</h3>
                    <p class="text-xs text-slate-500">Create a new Super Admin or Moderator with specific access privileges.</p>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">✕</button>
            </div>

            <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-4" x-data="{ createRole: 'moderator' }">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Staff Role</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label :class="createRole === 'moderator' ? 'border-blue-600 bg-blue-50/50 text-blue-900 ring-2 ring-blue-500' : 'border-slate-200 bg-slate-50 text-slate-600'"
                               class="p-3 rounded-xl border text-xs font-bold cursor-pointer flex items-center gap-2 transition">
                            <input type="radio" name="role" value="moderator" x-model="createRole" class="sr-only">
                            <span>⚖️ Moderator</span>
                        </label>
                        <label :class="createRole === 'super_admin' ? 'border-rose-600 bg-rose-50/50 text-rose-900 ring-2 ring-rose-500' : 'border-slate-200 bg-slate-50 text-slate-600'"
                               class="p-3 rounded-xl border text-xs font-bold cursor-pointer flex items-center gap-2 transition">
                            <input type="radio" name="role" value="super_admin" x-model="createRole" class="sr-only">
                            <span>👑 Super Admin</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Ahmad Tariq"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" name="email" required placeholder="staff@nikahconnect.com"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                        <input type="tel" name="phone" placeholder="+1 555 000 9999"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
                    <input type="password" name="password" required minlength="8" placeholder="Minimum 8 characters"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <!-- Moderator Permissions Checkboxes -->
                <div x-show="createRole === 'moderator'" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <span class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                        Moderator Permissions (Access Control)
                    </span>
                    <div class="space-y-2">
                        @foreach($availablePermissions as $key => $label)
                            <label class="flex items-start gap-2.5 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                       {{ in_array($key, ['manage_verifications', 'manage_reports']) ? 'checked' : '' }}
                                       class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <span class="font-bold text-slate-800 block">{{ $label }}</span>
                                    <span class="text-[10px] text-slate-500">Key: <code>{{ $key }}</code></span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div x-show="createRole === 'super_admin'" style="display: none;"
                     class="p-4 bg-rose-50 rounded-2xl border border-rose-200 text-xs text-rose-900">
                    <span class="font-bold block mb-1">👑 Full Super Admin Privileges</span>
                    Super Admins automatically receive unrestricted access to all settings, user management, policy updates, and platform controls.
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-900/15 transition">
                        Create Staff Member
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Staff Permissions Modal -->
    <div x-show="showEditModal" style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showEditModal = false"
             class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 font-heading">
                        Edit Access for <span x-text="editUser.name" class="text-emerald-700"></span>
                    </h3>
                    <p class="text-xs text-slate-500">Update role or assigned moderation privileges.</p>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">✕</button>
            </div>

            <form :action="'{{ url('/admin/staff') }}/' + editUser.id + '/permissions'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Role</label>
                    <select name="role" x-model="editUser.role"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        <option value="moderator">⚖️ Moderator</option>
                        <option value="super_admin">👑 Super Admin</option>
                    </select>
                </div>

                <!-- Moderator Permissions Checkboxes -->
                <div x-show="editUser.role === 'moderator'" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <span class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                        Moderator Permissions
                    </span>
                    <div class="space-y-2">
                        @foreach($availablePermissions as $key => $label)
                            <label class="flex items-start gap-2.5 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                       :checked="editUser.permissions && editUser.permissions.includes('{{ $key }}')"
                                       class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <span class="font-bold text-slate-800 block">{{ $label }}</span>
                                    <span class="text-[10px] text-slate-500">Key: <code>{{ $key }}</code></span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div x-show="editUser.role === 'super_admin'" style="display: none;"
                     class="p-4 bg-rose-50 rounded-2xl border border-rose-200 text-xs text-rose-900">
                    <span class="font-bold block mb-1">👑 Full Super Admin Access</span>
                    Super Admins automatically receive unrestricted access to all areas.
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-900/15 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
