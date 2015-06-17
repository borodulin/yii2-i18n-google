<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\translators;

use yii\base\Object;
use conquer\i18n\TranslatorInterface;
use conquer\helpers\Curl;

/**
 * 
 * @author Andrey Borodulin
 */
class YandexTranslator extends Object implements TranslatorInterface
{
    public $apiKey;

    
    public function translate($text, $sourceLang, $targetLang, $format)
    {
        $curl = new Curl('https://translate.yandex.net/api/v1.5/tr.json/translate?'.http_build_query([
            'key' => $this->apiKey,
            'text' => $text,
            'format' => ($format === 'text')? 'plain' : $format,
            'lang' => "$sourceLang-$targetLang",
        ]));
        if($curl->execute()){
            
        }
    }
    
    public function languages()
    {
        $curl = new Curl('https://translate.yandex.net/api/v1.5/tr.json/getLangs?'.http_build_query([
            'key' => $this->apiKey,
        ]));
        if($curl->execute()){
            
        }
    }
    
    public function detect($text)
    {
        $curl = new Curl('https://translate.yandex.net/api/v1.5/tr.json/detect?'.http_build_query([
            'key' => $this->apiKey,
            'text' => $text,
        ]));
        if($curl->execute()){
        
        }
    }
}