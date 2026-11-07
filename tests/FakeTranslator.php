<?php

namespace conquer\i18n\tests;

use conquer\i18n\TranslatorInterface;
use yii\base\BaseObject;

/**
 * Translator for tests: "[lang] text", or the result of $handler.
 */
class FakeTranslator extends BaseObject implements TranslatorInterface
{
    /**
     * @var array calls: [text, source, target, format]
     */
    public $calls = [];

    /**
     * @var callable|null function ($text, $source, $target, $format) returning string|null
     */
    public $handler;

    public $error = 'Fake error';

    public function translate($text, $sourceLang, $targetLang, $format = 'text')
    {
        $this->calls[] = [$text, $sourceLang, $targetLang, $format];
        if ($this->handler !== null) {
            return call_user_func($this->handler, $text, $sourceLang, $targetLang, $format);
        }
        return "[$targetLang] $text";
    }

    public function languages()
    {
        return ['en', 'ru'];
    }

    public function detect($text)
    {
        return 'en';
    }

    public function getError()
    {
        return $this->error;
    }
}
