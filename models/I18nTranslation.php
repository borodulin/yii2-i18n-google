<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\models;

/**
 * This is the model class for table "{{%i18n_translation}}".
 *
 * @property integer $message_id
 * @property string $language
 * @property integer $translator_id
 * @property string $translation
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $error_message
 *
 * @property I18nMessage $message
 * @property I18nTranslator $translator
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
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id', 'language'], 'required'],
            [['message_id', 'translator_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['translation', 'error_message'], 'string'],
            [['language'], 'string', 'max' => 16]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'message_id' => 'Message ID',
            'language' => 'Language',
            'translator_id' => 'Translator ID',
            'translation' => 'Translation',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'error_message' => 'Error Message',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage()
    {
        return $this->hasOne(I18nMessage::className(), ['message_id' => 'message_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslator()
    {
        return $this->hasOne(I18nTranslator::className(), ['translator_id' => 'translator_id']);
    }    
}