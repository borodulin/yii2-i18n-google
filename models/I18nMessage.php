<?php

namespace conquer\i18n\models;

use conquer\i18n\Module;
use yii\helpers\ArrayHelper;
class I18nMessage extends \yii\db\ActiveRecord
{
    
    private $_messages;
    
    public static function tableName()
    {
        return '{{%i18n_message}}';
    }
    
    public function behaviors()
    {
        return [
            ['class' => \yii\behaviors\TimestampBehavior],
        ];
    }
    
    public static function translate($message, $category, $language)
    {
        $key = $language . '/' . $category;
        if (!isset($this->_messages[$key])) {
            $this->_messages[$key] = $this->loadMessages($category, $language);
        }
        if (isset($this->_messages[$key][$message])) {
            return $this->_messages[$key][$message];
        }

        $model = I18nMessage::findOne([
                'category' => $category,
                'message' => $message,
        ]);

        if(empty($model)){
            $model = new I18nMessage([
                'category' => $category,
                'message' => $message,
            ]);
            $model->save(false);
        }
        $translated = new I18nTranslatedMessage([
                'id' => $model->id,
                'language' => $language,
        ]);
        $translated->save(false);
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
       /* @var $module Module */
        $module = Module::getInstance();
        if ($module->enableCaching) {
            $key = [
                __CLASS__,
                $category,
                $language,
            ];
            $messages = $module->cache->get($key);
            if ($messages === false) {
                $messages = $this->loadMessagesFromDb($category, $language);
                $module->cache->set($key, $messages, $module->cachingDuration);
            }

            return $messages;
        } else {
            return $this->loadMessagesFromDb($category, $language);
        }
    }
    
    /**
     * Loads the messages from database.
     * 
     * @param string $category the message category.
     * @param string $language the target language.
     * @return array the messages loaded from database.
     */
    protected static function loadMessagesFromDb($category, $language)
    {
        $messages = I18nTranslatedMessage::find()
            ->select(['translation'])
            ->with('message')
            ->where(['category'=>$category, 'language' => $language])
          //  ->andWhere(['status'=>I18nTranslatedMessage::STATUS_DONE])
            ->asArray()
            ->all();
        return ArrayHelper::map($messages, 'message', 'translation');
    }
    
}