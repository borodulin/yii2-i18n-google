# Yii2 I18N Google & Yandex translation services integration

[![Latest Version](https://img.shields.io/packagist/v/conquer/i18n.svg)](https://packagist.org/packages/conquer/i18n)
[![Total Downloads](https://img.shields.io/packagist/dt/conquer/i18n.svg)](https://packagist.org/packages/conquer/i18n)
[![CI](https://github.com/borodulin/yii2-i18n-google/actions/workflows/ci.yml/badge.svg)](https://github.com/borodulin/yii2-i18n-google/actions/workflows/ci.yml)
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
- ext-mbstring

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

### Database

Apply the migrations of the extension:

```bash
php yii migrate --migrationPath=@conquer/i18n/migrations
```

On MySQL the tables use utf8mb4. Tables created by earlier versions are converted by the
`m261107_120000_i18n_utf8mb4` migration (with the database default charset, e.g. latin1,
most translations could not be stored).

### Background translation

By default a missing message is translated during the request that needs it. With `'async' => true`
it is queued instead, and translated by the console command (run it from cron):

```bash
php yii translate/pending            # queued messages
php yii translate/pending --retry    # also the failed ones
php yii translate                    # flush cached translations
```

(`translate` is the ID of the module in the console application.)

### Placeholders

Placeholders such as `{attribute}` or `{n, number}` are sent to the translator as untranslatable
markup and must come back unchanged, otherwise the translation is marked as failed and the source
message is used. Messages with `plural`, `select` or `selectordinal` are not machine-translated:
fill them in the `i18n_translation` table.

## Notes

- A message whose machine translation failed is not sent to the translation API again on every request;
  run `php yii translate/pending --retry`, or fix it in the `i18n_translation` table.
  The error is stored in `i18n_translation.error_message`.
- The API key is sent in the `X-Goog-Api-Key` header of a POST request, not in the URL.
  `connectTimeout` (5 s) and `timeout` (15 s) of `GoogleTranslator` limit the time a page waits for the API.
- Categories longer than 64 characters are not stored or translated.
- The bundled CRUD controllers (`controllers/I18nMessageController`, `controllers/I18nTranslationController`)
  are templates for your application: they are not routed by the module, require the `admin` RBAC role
  (`$allowedRoles`) and use `yiisoft/yii2-bootstrap` views.

## Upgrading to 1.3

- Before 1.3 `GoogleTranslator` never returned a translation (the API response was not parsed), so every
  message was stored as failed. After upgrading run `php yii migrate --migrationPath=@conquer/i18n/migrations`
  and `php yii translate/pending --retry`.
- `TranslatorInterface::translate()` is called with `$format = 'html'` for messages with placeholders.

## License

**conquer/i18n** is released under the MIT License. See the bundled [`LICENSE`](LICENSE) for details.
