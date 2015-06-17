<?php

namespace conquer\i18n\models;

class I18nMessage extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%i18n_message}}';
    }
    
    public static function translate($message, $category, $language)
    {
        
    }
}