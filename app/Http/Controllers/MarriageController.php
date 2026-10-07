<?php

namespace App\Http\Controllers;

use App\Models\Marriage;
use App\Services\MarriageCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MarriageController extends Controller
{
    public function __construct(
        protected MarriageCompletionService $marriageService
    ) {}

    public function celebration(): View|RedirectResponse
    {
        $user = Auth::user();
        $marriage = $user->activeMarriage() ?? $user->pendingMarriage();

        if (! $marriage) {
            return redirect()->route('profile.show')
                ->with('info', 'You do not have an active or pending Nikah milestone yet.');
        }

        $spouse = $marriage->spouseOf($user);
        $isGroom = ($user->id === $marriage->groom_id);
        $isBride = ($user->id === $marriage->bride_id);

        return view('marriages.celebration', compact('marriage', 'user', 'spouse', 'isGroom', 'isBride'));
    }

    public function initiate(Request $request): RedirectResponse
    {
        $request->validate([
            'spouse_id' => ['required', 'exists:users,id'],
            'marriage_date' => ['nullable', 'date'],
            'confirmation_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        try {
            $marriage = $this->marriageService->initiate(
                initiator: $user,
                spouseId: (int) $request->spouse_id,
                marriageDate: $request->marriage_date,
                notes: $request->confirmation_notes
            );

            return redirect()->route('marriages.celebration')
                ->with('success', 'Alhamdulillah! Nikah completion request submitted. Your spouse and guardian can now confirm the milestone.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirm(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $marriage = Marriage::with(['groom', 'bride'])->findOrFail($id);

        try {
            $this->marriageService->confirm($marriage, $user);

            return redirect()->route('marriages.celebration')
                ->with('success', '🎉 Nikah Mubarak! May Allah shower His blessings, peace, and mercy upon your marriage.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function decline(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $marriage = Marriage::findOrFail($id);

        try {
            $this->marriageService->decline($marriage, $user);

            return redirect()->route('chat.index')
                ->with('info', 'Nikah confirmation request was declined.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function submitStory(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'story_title' => ['required', 'string', 'max:255'],
            'story_body' => ['required', 'string', 'max:5000'],
            'story_is_public' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $marriage = Marriage::findOrFail($id);

        try {
            $this->marriageService->submitStory(
                marriage: $marriage,
                user: $user,
                title: $request->story_title,
                body: $request->story_body,
                isPublic: $request->boolean('story_is_public')
            );

            return back()->with('success', 'Alhamdulillah! Your Barakah Story has been saved to inspire fellow seekers on the Sunnah of Nikah.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
