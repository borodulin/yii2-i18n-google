<?php

namespace conquer\i18n\tests;

use conquer\i18n\translators\GoogleTranslator;
use PHPUnit\Framework\TestCase;

/**
 * GoogleTranslator against a local mock of the API (tests/_data/google.php).
 */
class GoogleTranslatorTest extends TestCase
{
    private static $process;
    private static $url = 'http://127.0.0.1:8781';

    public static function setUpBeforeClass(): void
    {
        self::$process = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:8781', __DIR__ . '/_data/google.php'],
            [['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']],
            $pipes
        );
        for ($i = 0; $i < 50 && !@fsockopen('127.0.0.1', 8781); $i++) {
            usleep(100000);
        }
    }

    public static function tearDownAfterClass(): void
    {
        proc_terminate(self::$process);
        proc_close(self::$process);
    }

    private function translator($key = 'test-key')
    {
        return new GoogleTranslator(['apiKey' => $key, 'endpoint' => self::$url . '/language/translate/v2']);
    }

    public function testTranslate()
    {
        $translator = $this->translator();
        // before 1.3 the response was never decoded: translate() always returned null
        $this->assertSame('[ru/text] Hello', $translator->translate('Hello', 'en', 'ru'));
        $this->assertNull($translator->getError());
        $this->assertSame('[de/html] <b>Hi</b>', $translator->translate('<b>Hi</b>', 'en', 'de', 'html'));
    }

    public function testRepeatedRequestsDoNotAccumulateHeaders()
    {
        $translator = $this->translator();
        for ($i = 0; $i < 3; $i++) {
            $this->assertSame('[ru/text] Hello', $translator->translate('Hello', 'en', 'ru'));
        }
        $this->assertCount(2, $translator->getOptions()[CURLOPT_HTTPHEADER]);
    }

    public function testApiError()
    {
        $translator = $this->translator('wrong-key');
        $this->assertNull($translator->translate('Hello', 'en', 'ru'));
        $this->assertSame('API key not valid. Please pass a valid API key.', $translator->getError());
    }

    public function testInvalidResponse()
    {
        $translator = $this->translator();
        $this->assertNull($translator->translate('garbage', 'en', 'ru'));
        $this->assertStringContainsString('not json', $translator->getError());
    }

    public function testConnectionError()
    {
        $translator = new GoogleTranslator(['apiKey' => 'test-key', 'endpoint' => 'http://127.0.0.1:1/v2', 'connectTimeout' => 1]);
        $this->assertNull($translator->translate('Hello', 'en', 'ru'));
        $this->assertNotEmpty($translator->getError());
    }

    public function testLanguagesAndDetect()
    {
        $translator = $this->translator();
        $this->assertSame(['en', 'ru'], $translator->languages());
        $this->assertSame('en', $translator->detect('Hello'));
    }
}
