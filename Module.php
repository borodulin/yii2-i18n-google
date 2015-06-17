<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n;

use conquer\i18n\console\TranslateController;
use yii\i18n\MessageSource;
use yii\i18n\MissingTranslationEvent;
use yii\caching\Cache;
use conquer\i18n\models\I18nMessage;
use yii\di\Instance;

/**
 * 
 * @author Andrey Borodulin
 *
 */
class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{
    /**
     * @var TranslatorInterface[]  array list in format: 'translatorId' => TranslatorInterface
     */
    private $_translators = [];
    
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
     * Configured [[cache]] component would also be initialized.
     * @throws InvalidConfigException if [[cache]] is invalid.
     */
    public function init()
    {
        parent::init();
        
        if(empty($this->_translators))
            throw new InvalidParamException("At least one translator must be configured.");
        
        if ($this->enableCaching) {
            $this->cache = Instance::ensure($this->cache, Cache::className());
        }
    }
    
    
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->i18n->on(MessageSource::EVENT_MISSING_TRANSLATION, [$this, 'handleTranslation']);
        
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules([
                $this->id => $this->id . '/default/index',
                $this->id . '/<id:\w+>' => $this->id . '/default/view',
                $this->id . '/<controller:[\w\-]+>/<action:[\w\-]+>' => $this->id . '/<controller>/<action>',
            ], false);
        } elseif ($app instanceof \yii\console\Application) {
            $app->controllerMap[$this->id] = [
                'class' => TranslateController::className(),
            ];
        }
    }
    
    /**
     * 
     * @param MissingTranslationEvent $event
     */
    public function handleTranslation($event)
    {
        $event->translatedMessage = I18nMessage::translate($event->message, $event->category, $event->language);
        $event->handled = isset($event->translatedMessage);
    }
    
    /**
     * @param array $config list of translators
     */
    public function setTranslators(array $config)
    {
        foreach ($config as $id => $translator) {
            $this->_translators[$id] = \Yii::createObject($translator);
            if(!$this->_translators[$id] instanceof TranslatorInterface)
                throw new InvalidConfigException('Translator have to implement the TranslatorInterface');
        }
    }
    
    /**
     * @return TranslatorInterface[] list of translators.
     */
    public function getTranslators()
    {
        return $this->_translators;
    }
    
    /**
     * @param string $id translator id.
     * @return TranslatorInterface translator instance.
     * @throws InvalidParamException on non existing translator request.
     */
    public function getTranslator($id)
    {
        if (!array_key_exists($id, $this->_translators)) {
            throw new InvalidParamException("Unknown translator '{$id}'.");
        }
        
        return $this->_translators[$id];
    }
}