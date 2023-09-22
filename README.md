# AI Receipt and Invoice Parser for Laravel

<p align="center"><img src=".github/header.png"></p>

![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/receipt-parser.svg?style=flat-square)
![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/helgesverre/receipt-parser/run-tests.yml?branch=main&label=tests&style=flat-square)
![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/helgesverre/receipt-parser/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)
![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/receipt-parser.svg?style=flat-square)

Leverage OpenAI's capabilities to easily parse structured receipt data from images, PDFs, and emails within your Laravel application.

## TODO:

- [ ] Improve readme
- [ ] Publishable prompts
- [ ] Verify the config works in a real project
- [ ] Verify the config works in a real project
- [ ] Write a few examples with 
  - [ ] PDF Receipts (text and non text) 
  - [ ] HTML Emails 
  - [ ] Image OCR 
  - [ ] Plain text 

## Installation

Install the package via composer:

```bash
composer require helgesverre/receipt-parser
```

Publish the config file:

```bash
php artisan vendor:publish --tag="receipt-parser-config"
```

This will publish the following configuration:

```php
return [
    'use_forgiving_number_parser' => env('USE_FORGIVING_NUMBER_PARSER', true),
    "textract_disk" => env("TEXTRACT_DISK")
];
```

## OCR Configuration with AWS Textract

To use AWS Textract for extracting text from images and multi-page PDFs, configure your `services.php` as follows:

```php
'textract' => [
    'key' => env("TEXTRACT_KEY"),
    'secret' => env("TEXTRACT_SECRET"),
    'region' => env("TEXTRACT_REGION"),
    'version' => env('TEXTRACT_VERSION', '2018-06-27'),
],
```

Add a new "disk" in your `filesystems.php` for storing files intended for OCR by Textract:

```php
'textract' => [
    'driver' => 's3',
    'key' => env('TEXTRACT_KEY'),
    'secret' => env('TEXTRACT_SECRET'),
    'region' => env('TEXTRACT_REGION'),
    'bucket' => env('TEXTRACT_BUCKET'),
],
```

Ensure the `textract_disk` setting in `config/receipt-parser.php` corresponds to your chosen disk name by setting the `TEXTRACT_DISK` env value:

`config/receipt-parser.php`
```php
return [
    "textract_disk" => env("TEXTRACT_DISK")
];
```

`.env`
```dotenv
TEXTRACT_DISK="uploads"
```

**Note**: The AWS S3 Bucket must be in the same region as your Textract IAM account and have the necessary access permissions.

## Publishing Prompts

Optionally, publish the prompt files:

```bash
php artisan vendor:publish --tag="receipt-parser-views"
```

## Usage

Below is an example of how to use the `ReceiptParser`:

```php
use HelgeSverre\ReceiptParser\Facades\ReceiptParser;
use HelgeSverre\ReceiptParser\Enums\Model;

$receiptParser = new HelgeSverre\ReceiptParser();

$text = <<<RECEIPT
// Your receipt data here
RECEIPT;

$receiptParser->scan($text)
```

Modify the model being used by passing a `model` parameter:

```php
ReceiptParser::scan("receipt here", model: Model::TURBO_INSTRUCT)
```

Available models:

| Enum Value     | String Representation  | Type        |
|----------------|------------------------|-------------|
| TURBO_INSTRUCT | gpt-3.5-turbo-instruct | Completion  |
| TURBO_16K      | gpt-3.5-turbo-16k      | Chat        |
| TURBO          | gpt-3.5-turbo          | Chat        |
| GPT4           | gpt-4                  | Chat        |
| GPT4_32K       | gpt-4-32               | Chat        |

## Alternative PDF Handling

If AWS Textract doesn't suit your needs, consider using [spatie/pdf-to-text](https://github.com/spatie/pdf-to-text):

```shell
 composer require spatie/pdf-to-text
```

Usage example:

```php
use HelgeSverre\ReceiptParser;
use Spatie\PdfToText\Pdf;

$receipt = ReceiptParser::scan(
    Pdf::getText('receipt.pdf')
)

dd($receipt);
```

**Note**: `poppler-utils` is required, and this approach works best with text-based PDFs. More details and limitations are available [here](https://github.com/spatie/pdf-to-text?tab=readme-ov-file#requirements).

## License

This package is licensed under the MIT License. For more details, refer to the [License File](LICENSE.md).
