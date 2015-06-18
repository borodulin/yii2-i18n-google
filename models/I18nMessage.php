<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\models;

use conquer\i18n\Module;
use yii\helpers\ArrayHelper;

/**
 *
 * @author Andrey Borodulin
 */
class I18nMessage extends \yii\db\ActiveRecord
{
    
    private static $_messages;
    
    public static function tableName()
    {
        return '{{%i18n_message}}';
    }
    
    public function behaviors()
    {
        return [
            ['class' => \yii\behaviors\TimestampBehavior::className()],
        ];
    }
    
    /**
     * @param string $category
     * @param string $message
     * @return \conquer\i18n\models\I18nMessage
     */
    public static function getMessage($category, $message)
    {
        $i18nMessage = I18nMessage::findOne([
                'category' => $category,
                'message' => $message,
        ]);
    
        if(empty($i18nMessage)){
            $i18nMessage = new I18nMessage([
                    'category' => $category,
                    'message' => $message,
            ]);
            $i18nMessage->save(false);
        }
        
        return $i18nMessage;
    }
    /**
     * 
     * @param string $language
     * @param I18nTranslator $translator
     * @return \conquer\i18n\models\I18nTranslation
     */
    public function getTranslation($language)
    {
        $i18nTranslation= I18nTranslation::findOne([
                'message_id' => $this->message_id,
                'language' => $language,
        ]);
        
        if(empty($i18nTranslation)){
            $i18nTranslation = new I18nTranslation([
                    'message_id' => $this->message_id,
                    'language' => $language,
            ]);
        }
        return $i18nTranslation;
    }
    
    /**
     * Loads the messages from database.
     * 
     * @param string $category the message category.
     * @param string $language the target language.
     * @return array the messages loaded from database.
     */
    public static function loadMessagesFromDb($category, $language)
    {
        $messages = I18nTranslation::find()
            ->with('message')
            ->where(['language' => $language])
           // ->params(['category'=>$category])
            ->asArray()
            ->all();
        return ArrayHelper::map($messages, 'message.message', 'translation');
    }
    
}