<?php
/**
 * @link https://github.com/borodulin/yii2-i18n-google
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-i18n-google/blob/master/LICENSE
 */

namespace conquer\i18n\translators;

use conquer\helpers\CurlTrait;
use conquer\i18n\TranslatorInterface;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * Google Cloud Translation API (Basic, v2)
 * @link https://cloud.google.com/translate/docs/reference/rest/v2/translate
 * @author Andrey Borodulin
 */
class GoogleTranslator extends BaseObject implements TranslatorInterface
{
    use CurlTrait;

    /**
     * @var string API key; sent in the X-Goog-Api-Key header, not in the URL
     */
    public $apiKey;

    /**
     * @var string API base URL
     */
    public $endpoint = 'https://translation.googleapis.com/language/translate/v2';

    /**
     * @var int connection timeout, seconds
     */
    public $connectTimeout = 5;

    /**
     * @var int request timeout, seconds
     */
    public $timeout = 15;

    /**
     * @var bool
     * @deprecated not used since 1.3: responses are always parsed as JSON
     */
    public $prettyprint = false;

    /**
     * @var string|null error of the last request
     */
    private $_error;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->apiKey)) {
            throw new InvalidConfigException('Google Api key required');
        }
    }

    /**
     * @inheritdoc
     */
    public function translate($text, $sourceLang, $targetLang, $format = 'text')
    {
        $result = $this->request('', [
            'q' => $text,
            'source' => $sourceLang,
            'target' => $targetLang,
            'format' => $format,
        ]);
        if (isset($result['data']['translations'][0]['translatedText'])) {
            return $result['data']['translations'][0]['translatedText'];
        }
        if ($result !== null) {
            $this->_error = 'Unexpected response: ' . $this->_content;
        }
        return null;
    }

    /**
     * @inheritdoc
     * @return array language codes, null on error
     */
    public function languages()
    {
        $result = $this->request('/languages', []);
        if (isset($result['data']['languages'])) {
            return array_column($result['data']['languages'], 'language');
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function detect($text)
    {
        $result = $this->request('/detect', ['q' => $text]);
        if (isset($result['data']['detections'][0][0]['language'])) {
            return $result['data']['detections'][0][0]['language'];
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Sends a POST request to the API.
     * @param string $path path after the endpoint
     * @param array $params form parameters
     * @return array|null decoded response, null on error (see getError())
     */
    protected function request($path, array $params)
    {
        $this->_error = null;
        $this->resetOptions()
            ->setUrl($this->endpoint . $path)
            ->setOptions([
                CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
                CURLOPT_TIMEOUT => $this->timeout,
            ])
            ->addHttpHeader('X-Goog-Api-Key: ' . $this->apiKey)
            ->addHttpHeader('Content-Type: application/x-www-form-urlencoded; charset=utf-8')
            ->setPostData(http_build_query($params));
        $this->curl_execute();
        if ($this->_errorCode) {
            $this->_error = $this->_errorMessage;
            return null;
        }
        try {
            $result = Json::decode((string)$this->_content);
        } catch (\Exception $e) {
            $result = null;
        }
        if (!$this->isHttpOK() || !is_array($result)) {
            $this->_error = isset($result['error']['message'])
                ? $result['error']['message']
                : 'HTTP ' . (isset($this->_info['http_code']) ? $this->_info['http_code'] : '?') . ': ' . $this->_content;
            return null;
        }
        return $result;
    }
}
