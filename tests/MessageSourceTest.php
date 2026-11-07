<?php

namespace conquer\i18n\tests;

use conquer\i18n\console\TranslateController;
use conquer\i18n\MessageSource;
use conquer\i18n\models\I18nMessage;
use conquer\i18n\models\I18nTranslation;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\console\Application;

class MessageSourceTest extends TestCase
{
    protected function setUp(): void
    {
        $this->app();
    }

    protected function tearDown(): void
    {
        Yii::$app = null;
    }

    private function app(array $source = [])
    {
        $db = isset(Yii::$app) ? Yii::$app->db : null;
        new Application([
            'id' => 'test',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'sourceLanguage' => 'en-US',
            'language' => 'ru-RU',
            'components' => [
                'db' => $db ?: ['class' => 'yii\db\Connection', 'dsn' => 'sqlite::memory:'],
                'cache' => 'yii\caching\ArrayCache',
                'i18n' => [
                    'translations' => [
                        '*' => array_merge([
                            'class' => MessageSource::class,
                            'translator' => ['class' => FakeTranslator::class],
                        ], $source),
                    ],
                ],
            ],
        ]);
        if ($db === null) {
            foreach ([
                'CREATE TABLE i18n_message (message_id INTEGER PRIMARY KEY AUTOINCREMENT, category VARCHAR(64) NOT NULL, message TEXT NOT NULL, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL)',
                'CREATE TABLE i18n_translator (translator_id INTEGER PRIMARY KEY AUTOINCREMENT, class_name VARCHAR(80) NOT NULL UNIQUE, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL)',
                'CREATE TABLE i18n_translation (message_id INTEGER NOT NULL, language VARCHAR(16) NOT NULL, translator_id INTEGER, translation TEXT, status SMALLINT NOT NULL DEFAULT 0, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL, error_message TEXT, PRIMARY KEY (message_id, language))',
            ] as $sql) {
                Yii::$app->db->createCommand($sql)->execute();
            }
        }
        // the registry of translators is static
        (function () {
            self::$_translators = null;
        })->bindTo(null, \conquer\i18n\models\I18nTranslator::class)();
    }

    /**
     * @return FakeTranslator
     */
    private function translator()
    {
        return Yii::$app->i18n->getMessageSource('*')->translator;
    }

    private function status($message, $language = 'ru-RU')
    {
        $m = I18nMessage::findOne(['message' => $message]);
        return $m ? I18nTranslation::findOne(['message_id' => $m->message_id, 'language' => $language]) : null;
    }

    public function testTranslatesAndStores()
    {
        $this->assertSame('[ru] Hello', Yii::t('site', 'Hello'));
        $this->assertSame([['Hello', 'en', 'ru', 'text']], $this->translator()->calls);
        $this->assertSame(I18nTranslation::STATUS_DONE, (int)$this->status('Hello')->status);

        // next request: from the database
        $this->app();
        $this->assertSame('[ru] Hello', Yii::t('site', 'Hello'));
        $this->assertSame([], $this->translator()->calls);
    }

    public function testFailedTranslationIsNotRetriedOnEveryRequest()
    {
        $this->translator()->handler = function () {
            return null;
        };
        $this->assertSame('Hello', Yii::t('site', 'Hello'));
        $translation = $this->status('Hello');
        $this->assertSame(I18nTranslation::STATUS_ERROR, (int)$translation->status);
        $this->assertSame('Fake error', $translation->error_message);

        $this->app();
        $this->assertSame('Hello', Yii::t('site', 'Hello'));
        $this->assertSame([], $this->translator()->calls);
    }

    public function testPlaceholdersAreProtected()
    {
        $this->translator()->handler = function ($text, $source, $target, $format) {
            // what Google does with format=html: translates the text, keeps translate="no" spans
            return str_replace(['cannot be blank', '&amp;'], ['не может быть пустым', '&amp;'], $text);
        };
        $this->assertSame('Name не может быть пустым.', Yii::t('site', '{attribute} cannot be blank.', ['attribute' => 'Name']));
        $call = $this->translator()->calls[0];
        $this->assertSame('html', $call[3]);
        $this->assertSame('<span translate="no">{attribute}</span> cannot be blank.', $call[0]);
        $this->assertSame('{attribute} не может быть пустым.', $this->status('{attribute} cannot be blank.')->translation);
    }

    public function testHtmlAndEntitiesAroundPlaceholders()
    {
        $this->translator()->handler = function ($text) {
            return str_replace('Hello', 'Привет', $text);
        };
        $this->assertSame('Привет, <b>Ann</b> & {friends}!', Yii::t('site', 'Hello, <b>{name}</b> & {friends}!', ['name' => 'Ann']));
    }

    public function testChangedPlaceholderIsAnError()
    {
        $this->translator()->handler = function ($text) {
            return str_replace('<span translate="no">{attribute}</span> cannot be blank', '{атрибут} не может быть пустым', $text);
        };
        // the source message is used
        $this->assertSame('Name cannot be blank.', Yii::t('site', '{attribute} cannot be blank.', ['attribute' => 'Name']));
        $translation = $this->status('{attribute} cannot be blank.');
        $this->assertSame(I18nTranslation::STATUS_ERROR, (int)$translation->status);
        $this->assertStringContainsString('Placeholders were changed', $translation->error_message);
    }

    public function testPluralIsNotSentToTranslator()
    {
        $message = '{n, plural, =1{# item} other{# items}}';
        $this->assertSame('2 items', Yii::t('site', $message, ['n' => 2]));
        $this->assertSame([], $this->translator()->calls);
        $this->assertStringContainsString('plural/select', $this->status($message)->error_message);
    }

    public function testLongCategoryDoesNotBreakThePage()
    {
        $category = str_repeat('c', 65);
        $this->assertSame('Hello', Yii::t($category, 'Hello'));
        $this->assertSame(0, (int)I18nMessage::find()->count());
    }

    public function testAsyncQueuesAndConsoleTranslates()
    {
        $this->app(['async' => true]);
        $this->assertSame('Hello', Yii::t('site', 'Hello'));
        $this->assertSame('Bye', Yii::t('site', 'Bye'));
        $this->assertSame([], $this->translator()->calls);
        // before 1.3 the queued translation was not saved
        $this->assertSame(I18nTranslation::STATUS_NEW, (int)$this->status('Hello')->status);

        $controller = new TranslateController('translate', Yii::$app);
        ob_start();
        $exitCode = $controller->runAction('pending');
        ob_end_clean();
        $this->assertSame(0, $exitCode);
        $this->assertSame(I18nTranslation::STATUS_DONE, (int)$this->status('Hello')->status);

        $this->app(['async' => true]);
        $this->assertSame('[ru] Hello', Yii::t('site', 'Hello'));
        $this->assertSame('[ru] Bye', Yii::t('site', 'Bye'));
    }

    public function testConsoleRetriesFailedTranslations()
    {
        $this->translator()->handler = function () {
            return null;
        };
        Yii::t('site', 'Hello');
        $this->assertSame(I18nTranslation::STATUS_ERROR, (int)$this->status('Hello')->status);

        $this->app();
        $controller = new TranslateController('translate', Yii::$app);
        ob_start();
        $controller->runAction('pending');
        $this->assertSame(I18nTranslation::STATUS_ERROR, (int)$this->status('Hello')->status, 'without --retry');
        $controller->runAction('pending', ['retry' => 1]);
        ob_end_clean();
        $translation = $this->status('Hello');
        $this->assertSame(I18nTranslation::STATUS_DONE, (int)$translation->status);
        $this->assertSame('[ru] Hello', $translation->translation);
        $this->assertNull($translation->error_message);
    }
}
