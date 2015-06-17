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
use conquer\helpers\Curl;
use conquer\helpers\conquer\helpers;
use conquer\helpers\conquer\helpers;

/**
 *
 * @author Andrey Borodulin
 */
class GoogleTranslator extends Object implements TranslatorInterface
{
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
        $curl = new Curl('https://www.googleapis.com/language/translate/v2?'.http_build_query([
            'key' => $this->apiKey,
            'q' => $text,
            'source' =>$sourceLang,
            'target' => $targetLang,
            'format' => $format,
            'prettyprint' => $this->prettyprint,
        ]));
        if($curl->execute()){
            
        }
    }
    

    public function languages()
    {
        $curl = new Curl('https://www.googleapis.com/language/translate/v2/languages?'.http_build_query([
            'key' => $this->apiKey,
        ]));
        if($curl->execute()){
            
        }
    }
    
        /**
         * Detect Language
         * @param string $text
        */
    public function detect($text)
    {
        $curl = new Curl('https://www.googleapis.com/language/translate/v2/detect?'.http_build_query([
                'key' => $this->apiKey,
                'q' => $text,
        ]));
        if($curl->execute()){
        
        }
    }
}
