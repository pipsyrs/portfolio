<?php

namespace Tests\Unit;

use App\Actions\Media\SanitizeRichText;
use Tests\TestCase;

class SanitizeRichTextTest extends TestCase
{
    private SanitizeRichText $sanitize;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sanitize = new SanitizeRichText;
    }

    public function test_it_strips_script_tags_and_their_contents(): void
    {
        $result = ($this->sanitize)('<p>Halo</p><script>alert("xss")</script>');

        $this->assertStringNotContainsString('script', $result);
        $this->assertStringNotContainsString('alert', $result);
        $this->assertStringContainsString('Halo', $result);
    }

    public function test_it_removes_event_handler_attributes(): void
    {
        $result = ($this->sanitize)('<p onclick="steal()" onmouseover="x()">Teks</p>');

        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringNotContainsString('onmouseover', $result);
        $this->assertStringContainsString('Teks', $result);
    }

    public function test_it_rejects_javascript_scheme_links(): void
    {
        $result = ($this->sanitize)('<a href="javascript:alert(1)">klik</a>');

        $this->assertStringNotContainsString('javascript:', $result);
        $this->assertStringContainsString('klik', $result);
    }

    public function test_it_rejects_data_uri_links(): void
    {
        $result = ($this->sanitize)('<a href="data:text/html;base64,PHNjcmlwdD4=">klik</a>');

        $this->assertStringNotContainsString('data:', $result);
    }

    public function test_it_keeps_safe_links_and_adds_noopener_for_new_tabs(): void
    {
        $result = ($this->sanitize)('<a href="https://contoh.com" target="_blank">situs</a>');

        $this->assertStringContainsString('https://contoh.com', $result);
        $this->assertStringContainsString('noopener', $result);
    }

    public function test_it_keeps_allowed_formatting_tags(): void
    {
        $result = ($this->sanitize)('<p><strong>tebal</strong> <em>miring</em></p><ul><li>satu</li></ul>');

        $this->assertStringContainsString('<strong>tebal</strong>', $result);
        $this->assertStringContainsString('<em>miring</em>', $result);
        $this->assertStringContainsString('<li>satu</li>', $result);
    }

    public function test_it_unwraps_disallowed_tags_but_keeps_their_text(): void
    {
        $result = ($this->sanitize)('<p>Sebelum <marquee>tengah</marquee> sesudah</p>');

        $this->assertStringNotContainsString('marquee', $result);
        $this->assertStringContainsString('tengah', $result);
    }

    public function test_it_removes_iframe_and_svg_payloads(): void
    {
        $result = ($this->sanitize)('<iframe src="https://jahat.test"></iframe><svg onload="alert(1)"></svg><p>aman</p>');

        $this->assertStringNotContainsString('iframe', $result);
        $this->assertStringNotContainsString('svg', $result);
        $this->assertStringNotContainsString('onload', $result);
        $this->assertStringContainsString('aman', $result);
    }

    public function test_it_returns_null_for_blank_input(): void
    {
        $this->assertNull(($this->sanitize)(null));
        $this->assertNull(($this->sanitize)(''));
    }
}
