<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\console;

use conquer\i18n\MessageSource;

/**
 * 
 * @author Andrey Borodulin
 *
 */
class TranslateController extends \yii\console\Controller
{
    /**
     * Flushes cached translations of conquer\i18n\MessageSource
     */
    public function actionIndex()
    {
        foreach (\Yii::$app->i18n->translations as $category => $config) {
            $source = \Yii::$app->i18n->getMessageSource($category === '*' ? 'any-category' : $category);
            if ($source instanceof MessageSource && $source->enableCaching) {
                $source->cache->flush();
                $this->stdout("Cache of '$category' flushed\n");
            }
        }
    }
}