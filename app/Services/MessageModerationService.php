<?php

namespace App\Services;

class MessageModerationService
{
    /**
     * Common contact sharing patterns and prohibited external channels (FR-4.4).
     */
    protected array $socialPatterns = [
        '/(?:whatsapp|what\'?s\s*app|wa\.me|watsapp)/i',
        '/(?:insta(?:gram)?|ig|insta\s*id)[\s:]*[@]?[a-zA-Z0-9._]+/i',
        '/(?:snap(?:chat)?|sc)[\s:]*[@]?[a-zA-Z0-9._]+/i',
        '/(?:telegram|tg)[\s:]*[@]?[a-zA-Z0-9._]+/i',
        '/(?:facebook|fb\.com|fb)[\s:]*[@]?[a-zA-Z0-9._]+/i',
    ];

    /**
     * Scan message text for contact details or inappropriate content.
     *
     * @return array{is_safe: bool, is_flagged: bool, flag_reason: ?string, sanitized_body: string}
     */
    public function scan(string $body): array
    {
        $flagReason = null;
        $isFlagged = false;
        $sanitized = $body;

        // 1. Scan for email addresses
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $body)) {
            $isFlagged = true;
            $flagReason = 'Email address sharing detected';
            $sanitized = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[Contact Info Hidden]', $sanitized);
        }

        // 2. Scan for phone numbers (digits with spaces, dashes, parentheses, or +)
        // Matches sequences of 7 to 15 digits
        $cleanDigits = preg_replace('/[^\d]/', '', $body);
        if (strlen($cleanDigits) >= 9) {
            $phoneRegex = '/(?:\+?\d{1,4}[-.\s]?)?\(?\d{2,4}\)?[-.\s]?\d{3,4}[-.\s]?\d{3,4}/';
            if (preg_match($phoneRegex, $body)) {
                $isFlagged = true;
                $flagReason = $flagReason ? $flagReason.' & Phone number detected' : 'Phone number sharing detected';
                $sanitized = preg_replace($phoneRegex, '[Phone Number Hidden]', $sanitized);
            }
        }

        // 3. Scan for social handles & direct platforms
        foreach ($this->socialPatterns as $pattern) {
            if (preg_match($pattern, $body)) {
                $isFlagged = true;
                $flagReason = $flagReason ? $flagReason.' & Social handle detected' : 'Social media / external handle sharing detected';
                $sanitized = preg_replace($pattern, '[External Handle Hidden]', $sanitized);
            }
        }

        // 4. Inappropriate / explicit language checks (Islamic etiquette FR-6.5)
        $explicitPatterns = [
            '/\b(hookup|casual\s*sex|dating|meet\s*alone|hotel\s*room|private\s*place)\b/i',
        ];

        foreach ($explicitPatterns as $pattern) {
            if (preg_match($pattern, $body)) {
                $isFlagged = true;
                $flagReason = $flagReason ? $flagReason.' & Inappropriate content' : 'Inappropriate content policy violation';
                $sanitized = preg_replace($pattern, '[Content Blocked]', $sanitized);
            }
        }

        return [
            'is_safe' => ! $isFlagged,
            'is_flagged' => $isFlagged,
            'flag_reason' => $flagReason,
            'sanitized_body' => $sanitized,
        ];
    }
}
