<?php

namespace Tests\Unit;

use App\Services\MessageModerationService;
use PHPUnit\Framework\TestCase;

class MessageModerationTest extends TestCase
{
    protected MessageModerationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MessageModerationService;
    }

    public function test_it_passes_clean_halal_messages(): void
    {
        $body = 'As-salamu alaykum. I appreciated reading your profile. How is your study of the Quran progressing?';
        $result = $this->service->scan($body);

        $this->assertTrue($result['is_safe']);
        $this->assertFalse($result['is_flagged']);
        $this->assertNull($result['flag_reason']);
        $this->assertSame($body, $result['sanitized_body']);
    }

    public function test_it_flags_and_hides_phone_numbers(): void
    {
        $body = 'Please call me at +1 555 234 5678 to discuss further.';
        $result = $this->service->scan($body);

        $this->assertFalse($result['is_safe']);
        $this->assertTrue($result['is_flagged']);
        $this->assertStringContainsString('Phone number', $result['flag_reason']);
        $this->assertStringContainsString('[Phone Number Hidden]', $result['sanitized_body']);
    }

    public function test_it_flags_and_hides_email_addresses(): void
    {
        $body = 'You can email my family at brother.zayd@gmail.com anytime.';
        $result = $this->service->scan($body);

        $this->assertFalse($result['is_safe']);
        $this->assertTrue($result['is_flagged']);
        $this->assertStringContainsString('Email address', $result['flag_reason']);
        $this->assertStringContainsString('[Contact Info Hidden]', $result['sanitized_body']);
    }

    public function test_it_flags_whatsapp_and_social_handles(): void
    {
        $body = 'Add me on whatsapp or my insta @brother_zayd';
        $result = $this->service->scan($body);

        $this->assertFalse($result['is_safe']);
        $this->assertTrue($result['is_flagged']);
        $this->assertStringContainsString('Social media', $result['flag_reason']);
    }
}
