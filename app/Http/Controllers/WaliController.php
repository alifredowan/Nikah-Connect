<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\Interest;
use App\Models\User;
use App\Models\WaliLink;
use App\Services\WaliWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WaliController extends Controller
{
    public function __construct(
        protected WaliWorkflowService $waliService
    ) {}

    public function dashboard(): View
    {
        $user = Auth::user();

        // If current user is a Wali
        $wards = WaliLink::with(['seeker.profile.primaryPhoto'])
            ->where('wali_user_id', $user->id)
            ->orWhere('wali_email', $user->email)
            ->get();

        $wardUserIds = $wards->pluck('seeker_user_id')->toArray();

        $pendingInterests = Interest::with(['sender.profile', 'recipient'])
            ->whereIn('recipient_id', $wardUserIds)
            ->where('wali_approval_status', 'pending')
            ->latest()
            ->get();

        $chaperonedConversations = Conversation::whereHas('participants', function ($q) use ($user) {
            $q->where('conversation_participants.user_id', $user->id)
                ->where('conversation_participants.role', 'wali_chaperone');
        })->with(['participants', 'latestMessage'])->latest()->get();

        return view('wali.dashboard', compact('wards', 'pendingInterests', 'chaperonedConversations'));
    }

    public function linkForm(): View
    {
        $user = Auth::user();
        $existingLinks = WaliLink::with('wali')->where('seeker_user_id', $user->id)->get();

        return view('wali.link', compact('user', 'existingLinks'));
    }

    public function linkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'relationship_type' => ['required', 'string', 'in:father,brother,uncle,grandfather,other_mahram'],
            'permission_level' => ['required', 'in:view_only,approve_required,full_proxy'],
            'wali_name' => ['required', 'string', 'max:100'],
            'wali_email' => ['nullable', 'email'],
            'wali_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $seeker = Auth::user();

        $this->waliService->linkWali(
            $seeker,
            $request->relationship_type,
            $request->permission_level,
            $request->wali_name,
            $request->wali_email,
            $request->wali_phone
        );

        $seeker->profile?->update(['wali_required' => true]);

        AuditLog::record($seeker->id, 'wali_linked', 'WaliLink', null, [
            'relationship' => $request->relationship_type,
            'permission' => $request->permission_level,
        ]);

        return back()->with('success', 'Guardian (Wali) link configured! Communication and interest requests will follow your chosen guardian permission settings (FR-4.3).');
    }

    public function approveInterest(int $interestId): RedirectResponse
    {
        $wali = Auth::user();
        $interest = Interest::findOrFail($interestId);

        $success = $this->waliService->approveByWali($interest, $wali);

        if (! $success) {
            return back()->with('error', 'You are not authorized to approve this interest request.');
        }

        AuditLog::record($wali->id, 'wali_approved_interest', 'Interest', $interest->id);

        return back()->with('success', 'Interest request approved by Guardian! The conversation is now unlocked for halal discussion.');
    }

    public function rejectInterest(int $interestId): RedirectResponse
    {
        $wali = Auth::user();
        $interest = Interest::findOrFail($interestId);

        $this->waliService->rejectByWali($interest, $wali);

        AuditLog::record($wali->id, 'wali_rejected_interest', 'Interest', $interest->id);

        return back()->with('info', 'Interest request declined by Guardian.');
    }
}
