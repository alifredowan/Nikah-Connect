<?php

namespace Database\Seeders;

use App\Models\DiscountCode;
use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class PlatformSettingSeeder extends Seeder
{
    public function run(): void
    {
        // General platform policy settings (FR-7.3)
        PlatformSetting::set('platform_name', 'Nikah Connect', 'string', 'general', 'Platform working brand name');
        PlatformSetting::set('require_wali_by_default', true, 'boolean', 'moderation', 'Whether Wali guardian linkage is prompted by default for female profiles');
        PlatformSetting::set('max_photos_per_profile', 6, 'integer', 'general', 'Maximum photos a user can upload (FR-2.2)');
        PlatformSetting::set('free_tier_daily_profile_views', 10, 'integer', 'subscription', 'Daily profile views quota for free tier (FR-3.4)');
        PlatformSetting::set('free_tier_daily_interests', 5, 'integer', 'subscription', 'Daily interest requests quota for free tier (FR-5.1)');
        PlatformSetting::set('message_contact_filter_enabled', true, 'boolean', 'moderation', 'Automatically scan and flag external contact numbers/emails in chat (FR-4.4)');

        // Pre-configure promotional discount codes (Section 3.5)
        DiscountCode::updateOrCreate(['code' => 'HALAL20'], [
            'discount_percentage' => 20,
            'max_uses' => 500,
            'times_used' => 0,
            'is_active' => true,
            'expires_at' => now()->addYear(),
        ]);

        DiscountCode::updateOrCreate(['code' => 'BARAKAH50'], [
            'discount_percentage' => 50,
            'max_uses' => 100,
            'times_used' => 0,
            'is_active' => true,
            'expires_at' => now()->addMonths(6),
        ]);
    }
}
