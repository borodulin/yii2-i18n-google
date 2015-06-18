<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\translators;

use yii\base\Object;
use conquer\i18n\TranslatorInterface;
use conquer\helpers\CurlTrait;
use yii\helpers\Json;

/**
 * 
 * @author Andrey Borodulin
 */
class YandexTranslator extends Object implements TranslatorInterface
{
    use CurlTrait;
    
    public $apiKey;

    
    public function translate($text, $sourceLang, $targetLang, $format = 'text')
    {
        $this->setUrl('https://translate.yandex.net/api/v1.5/tr.json/translate?'.http_build_query([
            'key' => $this->apiKey,
            'text' => $text,
            'format' => ($format === 'text')? 'plain' : $format,
            'lang' => "$sourceLang-$targetLang",
        ]));
        $this->curl_execute();
        if($this->isHttpOK()){
            $result = Json::decode($this->_content);
            return isset($result['text'][0]) ? $result['text'][0] : null;
        }
        return null;
    }
    
    public function languages()
    {
        $this->setUrl('https://translate.yandex.net/api/v1.5/tr.json/getLangs?'.http_build_query([
            'key' => $this->apiKey,
        ]));
        $this->curl_execute();
        if($this->isHttpOK()){
            
        }
    }
    
    public function detect($text)
    {
        $this->setUrl('https://translate.yandex.net/api/v1.5/tr.json/detect?'.http_build_query([
            'key' => $this->apiKey,
            'text' => $text,
        ]));
        $this->curl_execute();
        if($this->isHttpOK()){
            
        }
    }
    
    public function getError()
    {
        return $this->_errorMessage ? $this->_errorMessage : $this->_content;
    }
}