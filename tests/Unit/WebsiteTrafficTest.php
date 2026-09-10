<?php

namespace Tests\Unit;

use App\Support\WebsiteTraffic;
use PHPUnit\Framework\TestCase;

class WebsiteTrafficTest extends TestCase
{
    public function test_empty_user_agent_is_bot(): void
    {
        $this->assertTrue(WebsiteTraffic::isBot(null));
        $this->assertTrue(WebsiteTraffic::isBot(''));
    }

    public function test_browser_user_agent_is_human(): void
    {
        $this->assertFalse(WebsiteTraffic::isBot(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ));
    }

    public function test_known_crawlers_are_bots(): void
    {
        $this->assertTrue(WebsiteTraffic::isBot('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'));
        $this->assertTrue(WebsiteTraffic::isBot('GPTBot'));
        $this->assertTrue(WebsiteTraffic::isBot('curl/8.0.1'));
    }

    public function test_direct_when_no_referrer(): void
    {
        $this->assertSame('direct', WebsiteTraffic::classifySource(null, null, null, 'example.com'));
    }

    public function test_utm_source_wins(): void
    {
        $this->assertSame('instagram', WebsiteTraffic::classifySource('instagram', 'google', 'https://google.com', 'mysalon.com'));
    }

    public function test_same_host_referrer_is_direct(): void
    {
        $this->assertSame('direct', WebsiteTraffic::classifySource(null, null, 'https://mysalon.com/about', 'mysalon.com'));
    }

    public function test_google_referrer_is_google(): void
    {
        $this->assertSame('google', WebsiteTraffic::classifySource(null, null, 'https://www.google.com/search?q=salon', 'mysalon.com'));
    }

    public function test_whatsapp_in_app_browser_is_human(): void
    {
        $this->assertFalse(WebsiteTraffic::isBot(
            'Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36 WhatsApp/2.24.20.0'
        ));
    }

    public function test_whatsapp_preview_crawler_is_bot(): void
    {
        $this->assertTrue(WebsiteTraffic::isBot('WhatsApp/2.24.20.0'));
    }

    public function test_click_labels(): void
    {
        $this->assertSame('Book now', WebsiteTraffic::clickLabel('book'));
        $this->assertSame('Call', WebsiteTraffic::clickLabel('call'));
        $this->assertSame('WhatsApp', WebsiteTraffic::clickLabel('whatsapp'));
        $this->assertSame('Package', WebsiteTraffic::clickLabel('package'));
        $this->assertSame('Service', WebsiteTraffic::clickLabel('service'));
    }

    public function test_click_name_is_sanitized(): void
    {
        $this->assertSame('book', WebsiteTraffic::normalizeClickName(' Book! '));
        $this->assertSame('', WebsiteTraffic::normalizeClickName('!!!'));
    }
}
