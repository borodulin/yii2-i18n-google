<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

use yii\db\Migration;

/**
 * MySQL: converts the tables to utf8mb4 (they were created with the database default charset,
 * e.g. latin1, which cannot store most translations). All databases: category up to 64 characters.
 */
class m261107_120000_i18n_utf8mb4 extends Migration
{
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            foreach (['{{%i18n_message}}', '{{%i18n_translator}}', '{{%i18n_translation}}'] as $table) {
                $this->execute('ALTER TABLE ' . $this->db->quoteTableName($table)
                    . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            }
        }
        if ($this->db->driverName !== 'sqlite') {
            $this->alterColumn('{{%i18n_message}}', 'category', $this->string(64)->notNull());
        }
    }

    public function down()
    {
        if ($this->db->driverName !== 'sqlite') {
            $this->alterColumn('{{%i18n_message}}', 'category', $this->string(32)->notNull());
        }
        // the charset conversion is kept
    }
}
