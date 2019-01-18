<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

use yii\db\Schema;
use yii\db\Migration;

/**
 * 
 * @author Andrey Borodulin
 *
 */
class m170610_152817_i18n extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {   
        $this->createTable('{{%i18n_message}}', [
                'message_id' => Schema::TYPE_PK,
                'category' => Schema::TYPE_STRING . '(32) NOT NULL',
                'message' => Schema::TYPE_TEXT . ' NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ]);
        
        $this->createTable('{{%i18n_translator}}', [
                'translator_id' => Schema::TYPE_PK,
                'class_name' => Schema::TYPE_STRING  . '(80) NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ]);
        
        $this->createTable('{{%i18n_translation}}', [
                'message_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'language' => Schema::TYPE_STRING . '(16) NOT NULL',
                'translator_id' => Schema::TYPE_INTEGER,
                'translation' => Schema::TYPE_TEXT,
                'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'error_message' => Schema::TYPE_TEXT,
                'PRIMARY KEY (message_id, language)',
        ]);
        $this->addforeignkey('fk_i18n_translation_message', '{{%i18n_translation}}', 'message_id', '{{%i18n_message}}', 'message_id', 'cascade', 'cascade');
        $this->addforeignkey('fk_i18n_translation_translator', '{{%i18n_translation}}', 'translator_id', '{{%i18n_translator}}', 'translator_id', 'cascade', 'cascade');
        $this->createIndex('ix_i18n_translation_status', '{{%i18n_translation}}', 'status');
        $this->createIndex('ix_i18n_translator_class_name', '{{%i18n_translator}}', 'class_name', true);
        
    }
    
    public function safeDown()
    {
        $this->dropTable('{{%i18n_translation}}');
        $this->dropTable('{{%i18n_translator}}');
        $this->dropTable('{{%i18n_message}}');
    }
}
