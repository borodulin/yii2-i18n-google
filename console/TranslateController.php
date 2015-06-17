<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\console;

use conquer\i18n\Module;
/**
 * 
 * @author Andrey Borodulin
 *
 */
class TranslateController extends \yii\console\Controller
{
    public function actionIndex()
    {
        /* @var $module Module */
        $module = Module::getInstance();
        $module->cache->flush();
    }
}