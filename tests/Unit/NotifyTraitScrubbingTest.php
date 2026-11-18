<?php

namespace Tests\Unit;

use App\Traits\NotifyTrait;
use PHPUnit\Framework\TestCase;

class NotifyTraitScrubbingTest extends TestCase
{
    protected object $notifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notifier = new class {
            use NotifyTrait {
                scrubTelegramText as public;
                formatShortcodes as public;
            }
        };
    }

    public function test_scrub_telegram_approvals()
    {
        $approvals = [
            'Approved via Telegram Inline Action',
            'Approved via Telegram Bot',
            'Approved via Telegram command',
            'Approved via Telegram',
            'Approved by authorized administrator',
            'Approved by authorized administrators',
            'Approved by authorized admin',
            'Approved by authorized admins',
            'Approved by System Administrator',
            'Approved by Administrator',
            'Approved by Admin',
            'Approved by the administrator',
            'Approved by an administrator',
            'Approved by system admin',
            'Approved by authorized administrator.',
            'approved by authorized administrator',
            'Approved',
        ];

        foreach ($approvals as $phrase) {
            $result = $this->notifier->scrubTelegramText($phrase);
            $this->assertEquals('Approved', $result, "Failed asserting that '$phrase' scrubs to 'Approved'");
        }
    }

    public function test_scrub_telegram_rejections()
    {
        $refundedRejections = [
            'Rejected and refunded via Telegram Inline Action',
            'Rejected and refunded via Telegram',
            'Transaction declined and refunded by Operations',
            'declined and refunded by Operations',
            'Rejected and refunded by authorized administrator',
            'Rejected and refunded by administrator',
            'Rejected and refunded by admin',
            'Declined and refunded by authorized administrator',
            'Declined and refunded',
        ];

        foreach ($refundedRejections as $phrase) {
            $result = $this->notifier->scrubTelegramText($phrase);
            $this->assertEquals('Declined and refunded', $result, "Failed asserting that '$phrase' scrubs to 'Declined and refunded'");
        }

        $simpleRejections = [
            'Rejected via Telegram Inline Action',
            'Rejected via Telegram Bot',
            'Rejected via Telegram command',
            'Rejected via Telegram',
            'Administrative rejection via Telegram',
            'Administrative review: Transaction rejected',
            'Transaction declined by Operations',
            'declined by Operations',
            'Rejected by authorized administrator',
            'Declined by authorized administrator',
            'Rejected by administrator',
            'Rejected by admin',
            'Declined by admin',
            'Declined',
            'Rejected',
        ];

        foreach ($simpleRejections as $phrase) {
            $result = $this->notifier->scrubTelegramText($phrase);
            $this->assertEquals('Declined', $result, "Failed asserting that '$phrase' scrubs to 'Declined'");
        }
    }

    public function test_no_administrator_or_telegram_leaks()
    {
        $samples = [
            'Payment approved by authorized administrator for member.',
            'Withdrawal rejected via Telegram command by admin.',
            'Notification sent via Telegram to system administrator.',
            'Under administrative review.',
            'Administrative decision pending.',
            'Action taken by system admin.',
            'Please contact the administrators for details.',
            'Please contact the admin regarding this account.',
        ];

        foreach ($samples as $sample) {
            $result = $this->notifier->scrubTelegramText($sample);
            $this->assertStringNotContainsStringIgnoringCase('administrator', $result, "Leaked 'administrator' in: $result");
            $this->assertStringNotContainsStringIgnoringCase('admin', $result, "Leaked 'admin' in: $result");
            $this->assertStringNotContainsStringIgnoringCase('telegram', $result, "Leaked 'telegram' in: $result");
        }
    }
}

