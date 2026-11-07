<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n;

use conquer\i18n\models\I18nMessage;
use conquer\i18n\models\I18nTranslation;
use conquer\i18n\models\I18nTranslator;
use yii\caching\Cache;
use yii\di\Instance;

/**
 * Message source that stores messages in the database and translates missing ones with an external translator.
 *
 * With [[async]] off, a missing message is translated during the request. With [[async]] on, it is queued
 * and translated by the `yii translate/pending` console command.
 *
 * @author Andrey Borodulin
 */
class MessageSource extends \yii\i18n\MessageSource
{
    /**
     * Maximum length of a category stored in the database
     */
    const MAX_CATEGORY_LENGTH = 64;

    /**
     * Maximum length of a language code stored in the database
     */
    const MAX_LANGUAGE_LENGTH = 16;

    private $_messages;

    /**
     * @var Cache|array|string the cache object or the application component ID of the cache object.
     * The messages data will be cached using this cache object. Note, this property has meaning only
     * in case [[cachingDuration]] set to non-zero value.
     */
    public $cache = 'cache';
    /**
     * @var integer the time in seconds that the messages can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * @see enableCaching
     */
    public $cachingDuration = 0;
    /**
     * @var boolean whether to enable caching translated messages
     */
    public $enableCaching = false;
    /**
     * @var TranslatorInterface|array|string
     */
    public $translator;
    /**
     * @var boolean whether missing messages are queued for `yii translate/pending` instead of
     * being translated during the request
     */
    public $async = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->translator = Instance::ensure($this->translator, 'conquer\i18n\TranslatorInterface');
        if ($this->enableCaching) {
            $this->cache = Instance::ensure($this->cache, Cache::className());
        }
    }

    /**
     * Loads the message translation for the specified language and category.
     *
     * @param string $category the message category
     * @param string $language the target language
     * @return array the loaded messages. The keys are original messages, and the values
     * are translated messages (null for pending or failed translations).
     */
    protected function loadMessages($category, $language)
    {
        if ($this->enableCaching) {
            $key = [
                __CLASS__,
                $category,
                $language,
            ];
            $messages = $this->cache->get($key);
            if ($messages === false) {
                $messages = I18nMessage::loadMessagesFromDb($category, $language);
                $this->cache->set($key, $messages, $this->cachingDuration);
            }

            return $messages;
        } else {
            return I18nMessage::loadMessagesFromDb($category, $language);
        }
    }

    /**
     * Translates the specified message.
     * @param string $category the category that the message belongs to.
     * @param string $message the message to be translated.
     * @param string $language the target language.
     * @return string|boolean the translated message or false if translation wasn't found.
     */
    protected function translateMessage($category, $message, $language)
    {
        $key = $language . '/' . $category;
        if (!isset($this->_messages[$key])) {
            $this->_messages[$key] = $this->loadMessages($category, $language);
        }
        if (isset($this->_messages[$key][$message])) {
            return $this->_messages[$key][$message];
        }
        if (is_array($this->_messages[$key]) && array_key_exists($message, $this->_messages[$key])) {
            // translation is pending or has failed: do not call the external API on every request
            return false;
        }
        if (mb_strlen($category) > static::MAX_CATEGORY_LENGTH || mb_strlen($language) > static::MAX_LANGUAGE_LENGTH) {
            // does not fit the database columns
            return $this->_messages[$key][$message] = false;
        }

        $i18nMessage = I18nMessage::getMessage($category, $message);
        $i18nTranslation = $i18nMessage->getTranslation($language);
        if ($this->async) {
            if ($i18nTranslation->getIsNewRecord()) {
                $i18nTranslation->status = I18nTranslation::STATUS_NEW;
                $i18nTranslation->save(false);
            }
            return $this->_messages[$key][$message] = false;
        }
        $translation = $this->translateRecord($i18nTranslation, $message);
        return $this->_messages[$key][$message] = $translation === null ? false : $translation;
    }

    /**
     * Translates a stored translation record with the translator and saves the result.
     * @param I18nTranslation $i18nTranslation
     * @param string $message the source message
     * @return string|null the translation, null on error (saved in `error_message`)
     */
    public function translateRecord(I18nTranslation $i18nTranslation, $message)
    {
        $i18nTranslator = I18nTranslator::getTranslator(get_class($this->translator));
        $i18nTranslation->translator_id = $i18nTranslator->translator_id;
        $sourceLang = explode('-', $this->sourceLanguage)[0];
        $targetLang = explode('-', $i18nTranslation->language)[0];
        $error = null;
        $translation = $this->machineTranslate($message, $sourceLang, $targetLang, $error);
        if ($translation !== null) {
            $i18nTranslation->translation = $translation;
            $i18nTranslation->status = I18nTranslation::STATUS_DONE;
            $i18nTranslation->error_message = null;
        } else {
            $i18nTranslation->status = I18nTranslation::STATUS_ERROR;
            $i18nTranslation->error_message = $error;
        }
        $i18nTranslation->save(false);
        return $translation;
    }

    /**
     * Translates a message with the translator, keeping its placeholders.
     *
     * Placeholders such as `{attribute}` or `{n, number}` are sent as untranslatable HTML
     * (`<span translate="no">`) and must come back unchanged. Messages with `plural`, `select`
     * or `selectordinal` are not machine-translated: their inner texts and syntax cannot be preserved.
     *
     * @param string $message
     * @param string $sourceLang
     * @param string $targetLang
     * @param string|null $error the error, when null is returned
     * @return string|null
     */
    public function machineTranslate($message, $sourceLang, $targetLang, &$error = null)
    {
        $error = null;
        if (preg_match('/\{\s*[\w.]+\s*,\s*(plural|select|selectordinal)\s*,/u', $message)) {
            $error = 'Messages with plural/select are not machine-translated: translate them manually.';
            return null;
        }
        if (!preg_match_all('/\{[^{}]*\}/u', $message, $matches)) {
            $translation = $this->translator->translate($message, $sourceLang, $targetLang, 'text');
            if (!is_string($translation) || $translation === '') {
                $error = $this->translator->getError() ?: 'Empty translation';
                return null;
            }
            return $translation;
        }

        $html = preg_replace_callback('/(\{[^{}]*\})|([^{}]+|[{}])/u', function ($m) {
            if ($m[1] !== '') {
                return '<span translate="no">' . htmlspecialchars($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</span>';
            }
            return htmlspecialchars($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }, $message);
        $translated = $this->translator->translate($html, $sourceLang, $targetLang, 'html');
        if (!is_string($translated) || $translated === '') {
            $error = $this->translator->getError() ?: 'Empty translation';
            return null;
        }
        $translation = preg_replace_callback('/<span\b[^>]*\btranslate\s*=\s*["\']?no["\']?[^>]*>(.*?)<\/span>/isu', function ($m) {
            return "\x00" . html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8') . "\x00";
        }, $translated);
        // remove any other markup and decode the text around the placeholders
        $parts = explode("\x00", $translation);
        foreach ($parts as $i => $part) {
            if ($i % 2 === 0) {
                $parts[$i] = html_entity_decode(strip_tags($part), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        $translation = implode('', $parts);

        $expected = $matches[0];
        preg_match_all('/\{[^{}]*\}/u', $translation, $found);
        sort($expected);
        $found = $found[0];
        sort($found);
        if ($expected !== $found) {
            $error = 'Placeholders were changed by the translator: ' . $translated;
            return null;
        }
        return $translation;
    }

    /**
     * Flushes cached translations
     */
    public function flushCache()
    {
        $this->_messages = null;
        if ($this->enableCaching) {
            $this->cache->flush();
        }
    }
}
