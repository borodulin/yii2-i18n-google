<?php

namespace conquer\i18n\models;

class I18nTranslatedMessage extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%i18m_translated_message}}';
    }
}