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
class I18nTranslation extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_DONE = 10;
    const STATUS_ERROR = 20;
    
    
    public static function tableName()
    {
        return '{{%i18n_translation}}';
    }
    
    public function behaviors()
    {
        return [
            ['class' => \yii\behaviors\TimestampBehavior::className()],
        ];
    }
    
    public function getMessage()
    {
        return $this->hasOne(I18nMessage::className(), ['message_id'=>'message_id']);
    }

    public function getTranslator()
    {
        return $this->hasOne(I18nTranslator::className(), ['translator_id'=>'translator_id']);
    }
    
}