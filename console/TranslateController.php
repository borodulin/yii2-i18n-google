<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\console;

use conquer\i18n\MessageSource;
use conquer\i18n\models\I18nTranslation;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Machine translation of queued messages and cache maintenance.
 *
 * @author Andrey Borodulin
 */
class TranslateController extends Controller
{
    /**
     * @var bool also translate again the messages whose translation failed
     */
    public $retry = false;

    /**
     * @var int maximum number of messages to translate, 0 for all
     */
    public $limit = 0;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), $actionID === 'pending' ? ['retry', 'limit'] : []);
    }

    /**
     * Flushes cached translations of conquer\i18n\MessageSource
     */
    public function actionIndex()
    {
        foreach ($this->messageSources() as $category => $source) {
            if ($source->enableCaching) {
                $source->flushCache();
                $this->stdout("Cache of '$category' flushed\n");
            }
        }
        return ExitCode::OK;
    }

    /**
     * Translates the queued messages (MessageSource::$async), with --retry also the failed ones
     */
    public function actionPending()
    {
        $statuses = [I18nTranslation::STATUS_NEW];
        if ($this->retry) {
            $statuses[] = I18nTranslation::STATUS_ERROR;
        }
        $query = I18nTranslation::find()->with('message')->where(['status' => $statuses])->orderBy(['message_id' => SORT_ASC]);
        if ($this->limit > 0) {
            $query->limit($this->limit);
        }
        $done = $failed = 0;
        $used = [];
        foreach ($query->each(100) as $i18nTranslation) {
            /* @var $i18nTranslation I18nTranslation */
            $i18nMessage = $i18nTranslation->message;
            if ($i18nMessage === null) {
                continue;
            }
            $source = $this->messageSourceFor($i18nMessage->category);
            if ($source === null) {
                $this->stderr("No conquer\\i18n\\MessageSource for category '{$i18nMessage->category}'\n");
                $failed++;
                continue;
            }
            $used[spl_object_id($source)] = $source;
            if ($source->translateRecord($i18nTranslation, $i18nMessage->message) !== null) {
                $done++;
            } else {
                $failed++;
                $this->stderr("[{$i18nTranslation->language}] {$i18nMessage->message}: {$i18nTranslation->error_message}\n");
            }
        }
        foreach ($used as $source) {
            $source->flushCache();
        }
        $this->stdout("Translated: $done, failed: $failed\n");
        return $failed ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }

    /**
     * @return MessageSource[] configured sources by category
     */
    protected function messageSources()
    {
        $sources = [];
        foreach (Yii::$app->i18n->translations as $category => $config) {
            $source = Yii::$app->i18n->getMessageSource($category === '*' ? 'any-category' : rtrim($category, '*'));
            if ($source instanceof MessageSource) {
                $sources[$category] = $source;
            }
        }
        return $sources;
    }

    /**
     * @param string $category
     * @return MessageSource|null the source translating the category: its own one,
     * or the "*" source used by conquer\i18n\Module for other message sources
     */
    protected function messageSourceFor($category)
    {
        try {
            $source = Yii::$app->i18n->getMessageSource($category);
        } catch (InvalidConfigException $e) {
            $source = null;
        }
        if (!$source instanceof MessageSource && isset(Yii::$app->i18n->translations['*'])) {
            $source = Yii::$app->i18n->getMessageSource('*');
        }
        return $source instanceof MessageSource ? $source : null;
    }
}
