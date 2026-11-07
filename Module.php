<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n;

use conquer\i18n\console\TranslateController;
use conquer\i18n\models\I18nMessage;
use yii\di\Instance;
use yii\base\InvalidParamException;
use yii\base\Event;

/**
 * 
 * @author Andrey Borodulin
 *
 */
class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{

    private $_messageSource;
    
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $this->_messageSource = \Yii::$app->i18n->getMessageSource('*');
        if($this->_messageSource instanceof MessageSource)
            Event::on(\yii\i18n\MessageSource::className(), MessageSource::EVENT_MISSING_TRANSLATION, [$this, 'handleTranslation']);
        
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
        $event->translatedMessage = $this->_messageSource->translate($event->category, $event->message, $event->language);
        $event->handled = isset($event->translatedMessage);
    }
    
}