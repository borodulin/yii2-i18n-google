<?php

namespace conquer\i18n;

use yii\di\Instance;
use conquer\i18n\models\I18nMessage;
use yii\i18n\MissingTranslationEvent;
use yii\caching\Cache;
use conquer\i18n\models\I18nTranslator;
use conquer\i18n\models\I18nTranslation;

class MessageSource extends \yii\i18n\MessageSource
{
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
     * 
     * @var TranslatorInterface
     */
    public $translator;
    /**
     * 
     * @var boolean
     */
    public $async = false;
    
    /**
     * Initializes the DbMessageSource component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * Configured [[cache]] component would also be initialized.
     * @throws InvalidConfigException if [[db]] is invalid or [[cache]] is invalid.
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
     * If translation for specific locale code such as `en-US` isn't found it
     * tries more generic `en`.
     *
     * @param string $category the message category
     * @param string $language the target language
     * @return array the loaded messages. The keys are original messages, and the values
     * are translated messages.
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
     * If the message is not found, a [[EVENT_MISSING_TRANSLATION|missingTranslation]] event will be triggered.
     * If there is an event handler, it may provide a [[MissingTranslationEvent::$translatedMessage|fallback translation]].
     * If no fallback translation is provided this method will return `false`.
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
        } else {
            $i18nMessage = I18nMessage::getMessage($category, $message);
            if ($this->async) {
                $i18nMessage->getTranslation($language);
            } else {                
                $i18nTranslator = I18nTranslator::getTranslator(get_class($this->translator));
                $i18nTranslation = $i18nMessage->getTranslation($language, $i18nTranslator);
                $i18nTranslation->translator_id = $i18nTranslator->translator_id;
                $translation = $this->translator->translate($message, $this->sourceLanguage, $language);
                if ($translation) {
                    $i18nTranslation->translation = $translation;
                    $i18nTranslation->status = I18nTranslation::STATUS_DONE;
                    $i18nTranslation->save(false);
                    return $this->_messages[$key][$message] = $translation;
                } else {
                    $i18nTranslation->status = I18nTranslation::STATUS_ERROR;
                    $i18nTranslation->error_message = $this->translator->getError();
                    $i18nTranslation->save(false);
                }
            }
        }
        return $this->_messages[$key][$message] = false;
    }
}