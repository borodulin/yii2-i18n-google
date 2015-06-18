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
     * @return string
     */
    public function translate($text, $sourceLang, $targetLang, $format = 'text');

    /**
     * Discover Supported Languages
     * @return array
     */
    public function languages();
    
    /**
     * Detect Language
     * @param string $text
     * @return string
     */
    public function detect($text);
    
    /**
     * @return string 
     */
    public function getError();
    
}