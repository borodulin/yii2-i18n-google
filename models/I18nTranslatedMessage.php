<?php

namespace conquer\i18n\models;

class I18nTranslatedMessage extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_DONE = 10;
    const STATUS_ERROR = 20;
    
    
    public static function tableName()
    {
        return '{{%i18m_translated_message}}';
    }
    
    public function behaviors()
    {
        return [
                ['class' => \yii\behaviors\TimestampBehavior],
        ];
    }
    
}