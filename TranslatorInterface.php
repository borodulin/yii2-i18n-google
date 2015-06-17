<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n;

/**
 *
 * @author Andrey Borodulin
 */
interface TranslatorInterface
{
    /**
     * Translate Text
     * @param string $text
     * @param string $sourceLang
     * @param string $targetLang
     * @param string $format html|text
     */
    public function translate($text, $sourceLang, $targetLang, $format);

    /**
     * Discover Supported Languages
     */
    public function languages();
    
    /**
     * Detect Language
     * @param string $text
     */
    public function detect($text);
}