<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\translators;

use yii\base\Object;
use yii\base\InvalidConfigException;
use conquer\i18n\TranslatorInterface;
use conquer\helpers\CurlTrait;
use yii\helpers\Json;

/**
 *
 * @author Andrey Borodulin
 */
class GoogleTranslator extends Object implements TranslatorInterface
{
    use CurlTrait;
    
    public $apiKey;
    
    public $prettyprint = false;
    
    public function init()
    {
        parent::init();
        
        if(empty($this->apiKey))
            throw new InvalidConfigException('Google Api key required');
    }
    
    public function translate($text, $sourceLang, $targetLang, $format = 'text')
    {
        $this->setUrl('https://www.googleapis.com/language/translate/v2?'.http_build_query([
            'key' => $this->apiKey,
            'q' => $text,
            'source' =>$sourceLang,
            'target' => $targetLang,
            'format' => $format,
            'prettyprint' => $this->prettyprint,
        ]));
        $this->curl_execute();
        if($this->isHttpOK()){
            return isset($result['data']['translations'][0]['translatedText']) ? 
                $result['data']['translations'][0]['translatedText'] : null;
        }
        return null;
    }
    

    public function languages()
    {
        $this->setUrl('https://www.googleapis.com/language/translate/v2/languages?'.http_build_query([
            'key' => $this->apiKey,
        ]));
        if($this->execute()){
            
        }
    }
    
        /**
         * Detect Language
         * @param string $text
        */
    public function detect($text)
    {
        $this->setUrl('https://www.googleapis.com/language/translate/v2/detect?'.http_build_query([
                'key' => $this->apiKey,
                'q' => $text,
        ]));
        if($this->execute()){
        
        }
    }
    
    public function getError()
    {
        return $this->_errorMessage ? $this->_errorMessage : $this->_content;
    }
}
