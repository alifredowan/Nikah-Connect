<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Interest;
use App\Models\Message;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Models\WaliLink;
use Illuminate\Console\Command;

class PurgeDummyDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purge-dummy-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely wipe all legacy mock/dummy seekers, dummy photos, and dummy test accounts, preserving the Super Admin.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting purge of dummy test profiles and fake photos...');

        // Find all dummy users (emails ending with @nikahconnect.test or created during previous seed runs)
        $dummyUsers = User::where('email', 'like', '%@nikahconnect.test')
            ->orWhereIn('email', [
                'admin@nikahconnect.test',
                'moderator@nikahconnect.test',
                'wali.father@nikahconnect.test',
                'seeker.groom@nikahconnect.test',
                'seeker.bride@nikahconnect.test',
            ])
            ->get();

        $dummyUserIds = $dummyUsers->pluck('id')->toArray();

        if (empty($dummyUserIds)) {
            $this->info('No dummy test users found in database.');

            return self::SUCCESS;
        }

        $this->info('Purging records for '.count($dummyUserIds).' dummy accounts...');

        // 1. Delete Messages & Conversations
        Message::whereIn('sender_id', $dummyUserIds)->delete();
        ConversationParticipant::whereIn('user_id', $dummyUserIds)->delete();
        Conversation::whereDoesntHave('participants')->delete();

        // 2. Delete Interests
        Interest::whereIn('sender_id', $dummyUserIds)
            ->orWhereIn('recipient_id', $dummyUserIds)
            ->delete();

        // 3. Delete Wali Links
        WaliLink::whereIn('seeker_user_id', $dummyUserIds)
            ->orWhereIn('wali_user_id', $dummyUserIds)
            ->delete();

        // 4. Delete Verification Requests & Reports
        VerificationRequest::whereIn('user_id', $dummyUserIds)->delete();
        Report::whereIn('reporter_id', $dummyUserIds)
            ->orWhereIn('reported_id', $dummyUserIds)
            ->delete();

        // 5. Delete Photos & Profiles
        $profiles = Profile::whereIn('user_id', $dummyUserIds)->get();
        foreach ($profiles as $p) {
            Photo::where('profile_id', $p->id)->delete();
            $p->delete();
        }

        // 6. Delete Subscriptions
        Subscription::whereIn('user_id', $dummyUserIds)->delete();

        // 7. Delete the dummy Users themselves (forceDelete to bypass SoftDeletes)
        User::whereIn('id', $dummyUserIds)->forceDelete();

        $this->info('Successfully purged all dummy candidates, fake photos, and mock interactions!');
        $this->info('Remaining real users count: '.User::count());

        return self::SUCCESS;
    }
}
