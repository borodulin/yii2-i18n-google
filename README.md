Yii2 I18N Google & Yandex translation services integration
=================

## Description

This extension makes it possible to automatically translate messages using external services.

##Google
With [Google Translate](https://cloud.google.com/translate/docs), you can dynamically translate text between thousands of language pairs.
The Google Translate API lets websites and programs integrate with Google Translate programmatically.
**Google Translate API is available as a paid service**. See the [Pricing](https://cloud.google.com/translate/v2/pricing.html)
and [FAQ](https://cloud.google.com/translate/v2/faq.html) pages for details.

##Yandex
[The API](https://tech.yandex.com/translate) provides access to the Yandex online machine translation service. It supports more than 40 languages and can translate separate words or complete texts. The API makes it possible to embed Yandex.Translate in a mobile app or web service for end users. Or translate large quantities of text, such as technical documentation.


# Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/). 

To install, either run

```
$ php composer.phar require conquer/i18n "*"
```
or add

```
"conquer/i18n": "*"
```

to the ```require``` section of your `composer.json` file.

# Usage

You need to configurate Module and i18n component

```php
$config = [
    'bootstrap' => ['translate'],
    'language' => 'ru',
    'modules' => [
        'translate' => 'conquer\i18n\Module'
    ],
    'components' => [
        'i18n' => [
            'class' => 'yii\i18n\I18N',
            'translations' => [
                '*' => [
                    'class' => 'conquer\i18n\MessageSource',
                    'translator' => [
                     //   'class'=>'conquer\i18n\translators\GoogleTranslator',
                     //   'apiKey' => 'obtain API key in the google developer console',
                        'class'=>'conquer\i18n\translators\YandexTranslator',
                        'apiKey' => '[yandex form](https://tech.yandex.com/keys/get/?service=trnsl)',
                    ],
                ],
            ],
        ],
    ],
];
```

# License

**conquer/i18n** is released under the MIT License. See the bundled `LICENSE.md` for details.
