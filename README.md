# Yii2 I18N Google & Yandex translation services integration

[![Latest Version](https://img.shields.io/packagist/v/conquer/i18n.svg)](https://packagist.org/packages/conquer/i18n)
[![Total Downloads](https://img.shields.io/packagist/dt/conquer/i18n.svg)](https://packagist.org/packages/conquer/i18n)
[![License](https://img.shields.io/packagist/l/conquer/i18n.svg)](LICENSE)

## Description

This extension makes it possible to automatically translate messages using external services.

### Google

With [Google Translate](https://cloud.google.com/translate/docs), you can dynamically translate text between thousands of language pairs.
The Google Translate API lets websites and programs integrate with Google Translate programmatically.
**Google Translate API is available as a paid service**. See the [Pricing](https://cloud.google.com/translate/v2/pricing.html)
and [FAQ](https://cloud.google.com/translate/v2/faq.html) pages for details.

### Yandex

> **Note:** the Yandex.Translate API v1.5 used by `YandexTranslator` has been discontinued by Yandex, so this translator no longer works. Use `GoogleTranslator` instead.

[The API](https://tech.yandex.com/translate) provides access to the Yandex online machine translation service. It supports more than 40 languages and can translate separate words or complete texts. The API makes it possible to embed Yandex.Translate in a mobile app or web service for end users. Or translate large quantities of text, such as technical documentation.


## Requirements

- PHP 7.4 or 8.x
- Yii 2.0.13 or newer (2.0.x)

## Installation

Install the extension via [Composer](https://getcomposer.org):

```bash
composer require conquer/i18n:^1.1
```

To create database tables run migration command
```
$ yii migrate --migrationPath=@conquer/i18n/migrations
```

## Usage

You need to configure Module and i18n component

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
                        'class' => 'conquer\i18n\translators\GoogleTranslator',
                        'apiKey' => 'obtain API key in the Google Cloud console',
                    ],
                ],
            ],
        ],
    ],
];
```
Use \Yii::t function to translate messages
```php
<h1><?= \Yii::t('app', 'Congratulations!') ?></h1>
```
**"*"** Translation category is used by default for all non-translated messages.

You can configure a separate category for the translation of messages by external translator.
```php
    'components' => [
        'i18n' => [
            'class' => 'yii\i18n\I18N',
            'translations' => [
                'yandex' => [
                    'class' => 'conquer\i18n\MessageSource',
                    'translator' => [
                        'class'=>'conquer\i18n\translators\YandexTranslator',
                        'apiKey' => '[yandex form](https://tech.yandex.com/keys/get/?service=trnsl)',
                    ],
                ],
            ],
        ],
    ],
```
Use \Yii::t function to translate messages in "yandex" category
```php
<h1><?= \Yii::t('yandex', 'Congratulations!') ?></h1>
```

## Notes

- A message whose machine translation failed (or is pending) is not sent to the translation API again on
  every request; fix or fill it in the `i18n_translation` table.
- `yii translate` (console) flushes cached translations.
- The bundled CRUD controllers (`controllers/I18nMessageController`, `controllers/I18nTranslationController`)
  are templates for your application: they are not routed by the module, require the `admin` RBAC role
  (`$allowedRoles`) and use `yiisoft/yii2-bootstrap` views.

## License

**conquer/i18n** is released under the MIT License. See the bundled [`LICENSE`](LICENSE) for details.
