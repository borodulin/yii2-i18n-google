<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\models;

use conquer\i18n\Module;
use yii\helpers\ArrayHelper;
use yii\db\Query;

/**
 * This is the model class for table "{{%i18n_message}}".
 *
 * @property integer $message_id
 * @property string $category
 * @property string $message
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property I18nTranslation[] $i18nTranslations
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
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'message'], 'required'],
            [['message'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['category'], 'string', 'max' => 32]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'message_id' => 'Message ID',
            'category' => 'Category',
            'message' => 'Message',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
        $mainQuery = new Query();
        $mainQuery->select(['t1.message', 't2.translation'])
            ->from([I18nMessage::tableName().' t1', I18nTranslation::tableName().' t2'])
            ->where('t1.message_id = t2.message_id AND t1.category = :category AND t2.language = :language')
            ->params([':category' => $category, ':language' => $language]);
        
        $messages = $mainQuery->createCommand()->queryAll();
        
        return ArrayHelper::map($messages, 'message', 'translation');
    }
    
}