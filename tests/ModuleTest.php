<?php

namespace conquer\i18n\tests;

use conquer\i18n\MessageSource;
use conquer\i18n\Module;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\console\Application;

/**
 * conquer\i18n\Module translates messages missing in other message sources ("app", "yii") with the "*" source.
 */
class ModuleTest extends TestCase
{
    protected function setUp(): void
    {
        new Application([
            'id' => 'test',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'sourceLanguage' => 'en-US',
            'language' => 'ru-RU',
            'bootstrap' => ['translate'],
            'modules' => ['translate' => Module::class],
            'components' => [
                'db' => ['class' => 'yii\db\Connection', 'dsn' => 'sqlite::memory:'],
                'i18n' => [
                    'translations' => [
                        '*' => [
                            'class' => MessageSource::class,
                            'translator' => [
                                'class' => FakeTranslator::class,
                                'handler' => function ($text) {
                                    return str_replace(['Congratulations!', 'is invalid'], ['Поздравляем!', 'неверно'], $text);
                                },
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        foreach ([
            'CREATE TABLE i18n_message (message_id INTEGER PRIMARY KEY AUTOINCREMENT, category VARCHAR(64) NOT NULL, message TEXT NOT NULL, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL)',
            'CREATE TABLE i18n_translator (translator_id INTEGER PRIMARY KEY AUTOINCREMENT, class_name VARCHAR(80) NOT NULL UNIQUE, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL)',
            'CREATE TABLE i18n_translation (message_id INTEGER NOT NULL, language VARCHAR(16) NOT NULL, translator_id INTEGER, translation TEXT, status SMALLINT NOT NULL DEFAULT 0, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL, error_message TEXT, PRIMARY KEY (message_id, language))',
        ] as $sql) {
            Yii::$app->db->createCommand($sql)->execute();
        }
    }

    protected function tearDown(): void
    {
        Yii::$app = null;
    }

    public function testMissingAppMessagesAreTranslated()
    {
        $this->assertSame('Поздравляем!', Yii::t('app', 'Congratulations!'));
        $this->assertSame('Value 5 неверно', Yii::t('app', 'Value {value} is invalid', ['value' => 5]));
    }
}
