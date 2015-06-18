<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\models;

/**
 *
 * @author Andrey Borodulin
 */
class I18nTranslator extends \yii\db\ActiveRecord
{
    
    private static $_translators;
    
    public static function tableName()
    {
        return '{{%i18n_translator}}';
    }
    
    public function behaviors()
    {
        return [
            ['class' => \yii\behaviors\TimestampBehavior::className()],
        ];
    }
    /**
     * 
     * @param string $className
     * @return \conquer\i18n\models\I18nTranslator
     */
    public static function getTranslator($className)
    {
        if(empty(self::$_translators))
            self::$_translators = static::find()->indexBy('class_name')->all();
        if(!isset(self::$_translators[$className])){
            self::$_translators[$className] = new static([
                'class_name' => $className,
            ]);
            self::$_translators[$className]->save(false);
        }
        return self::$_translators[$className];
    }
    /**
     * 
     * @param string $category
     * @param string $message
     * @param string $language
     * @return \conquer\i18n\models\I18nTranslation
     */
    public function createTranslation($category, $message,  $language)
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
        
        $i18nTranslation= I18nTranslation::findOne([
            'message_id' => $i18nMessage->message_id,
            'language' => $language,
        ]);
        
        if(empty($i18nTranslation)){
            $i18nTranslation = new I18nTranslation([
                    'message_id' => $i18nMessage->message_id,
                    'language' => $language,
                    'translator_id' => $this->translator_id,
            ]);
            
        }else{
            $i18nTranslation->translator_id = $this->translator_id;
        }
        $i18nTranslation->save(false);
        return $i18nTranslation;
    }
    
}