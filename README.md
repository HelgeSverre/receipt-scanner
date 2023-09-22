# AI Receipt and Invoice Parser for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/receipt-parser.svg?style=flat-square)](https://packagist.org/packages/helgesverre/receipt-parser)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/helgesverre/receipt-parser/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/helgesverre/receipt-parser/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/helgesverre/receipt-parser/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/helgesverre/receipt-parser/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/receipt-parser.svg?style=flat-square)](https://packagist.org/packages/helgesverre/receipt-parser)

Use OpenAI to parse structured receipt data from Images, Pdfs and emails.

## Read this first.

This package is based on my 3 years working at Tjommi where i almost exclusively tried to figure out clever ways to
parse and scan receipt data out of people's email inbox, all the experimentation i have done with receipt parsing
at [Kassalapp](https://kassal.app) and 2 other projects that are in active development.

This approach works well for 90% of the use-cases I have had to deal with personally.

The data accuracy will be quite high for the most common fields on a reciept or invoice around 80% on the
total_amount, tax_amount and order_ref fields, and slightly lower at 70% accuracy for line item data, i would say that

based on my own anecdotal findings, this works for 90% of "conventional" receipts (e-commerce, simple line itmez), and
on about 60% of restaurant related receipts where there are sub-choices (ex: burger 20$, want fries? 5$, want extra dip?
2$) where the individual line items have sub-items that equals the total of the "main" line item, it will **USUALLY**
get the numbers correct, but depending on how the invoice/receipt is structured, you have to apply some extra logic to
check if the "subitems" adds up the the "main item", and if it does not each individual line item needs to be added
together, there are a bunch of edge-cases like this that you have to deal with after getting the "raw structured data",
that this package does **not** handle for you.

I made this package, because i found myself copy-pasting this kind of code between multiple fin-tech-y projects where i
need to parse receipts.

## Installation

You can install the package via composer:

```bash
composer require helgesverre/receipt-parser
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="receipt-parser-config"
```

This is the contents of the published config file:

```php
return [
 // When enabled, will try to parse numbers that use non-standard decimal and thousand separators into a float.
    'use_forgiving_number_parser' => env('USE_FORGIVING_NUMBER_PARSER', true),

    // The disk to use when uploading files to be used with textract
    "textract_disk" => env("TEXTRACT_DISK")
];
```

## Using Textract for OCR

To use AWS Textract for OCR-ing text from images (and multi-page PDFs), you have to add the following configuration to
your `services.php`

```php
'textract' => [
    'key' => env("TEXTRACT_KEY"),
    'secret' => env("TEXTRACT_SECRET"),
    'region' => env("TEXTRACT_REGION"),
    'version' => env('TEXTRACT_VERSION', '2018-06-27'),
],
```

You also need to add a new "disk" in your `filesystems.php` that should be used for storing files that are going to be
OCR-ed by textract.

```
'textract' => [
    'driver' => 's3',
    'key' => env('TEXTRACT_KEY'),
    'secret' => env('TEXTRACT_SECRET'),
    'region' => env('TEXTRACT_REGION'),
    'bucket' => env('TEXTRACT_BUCKET'),
],
```

If you chose to name your disk somethnig else, you have to change the `textract_disk` setting
in `config/receipt-parser.php` by setting the `TEXTRACT_DISK` env value.

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

*Note*: The textract "disk" (aka the AWS S3 Bucket) needs to be in the same same region as your textract IAM
account (`TEXTRACT_KEY`  and `TEXTRACT_SECRET`), and it needs access to the S3 bucket (Or just give
it `AmazonS3FullAccess`).

## Publishing prompts

Optionally, you can publish the prompt files using using

```bash
php artisan vendor:publish --tag="receipt-parser-views"
```

## Usage

```php
$receiptParser = new HelgeSverre\ReceiptParser();

$text = <<<RECEIPT
Kvittering
Ordredetaljer
Kunde Helge Sverre Hessevik Liseth
Bestillings ID 64e4fb2646c424c5cbc9bc88
Restaurant Minde Pizzeria
Bestillingstype Levering
Leveringstid 22.08.2023 20:46
Betalingsmetode
Visa: ****2192 568,00

Vare MVA % Antall Brutto enhetspris Pris
Mindes Spesialpizza Stor 325,00
Mindes Spesialpizza Stor 15% 1 275,00 275,00
Noe ekstra på din store pizza: Ekstra
bacon 15% 1 30,00 30,00
Ekstra tilbehør: Hvitløksdressing 15% 1 20,00 20,00
Hamburgermeny 185,00
Hamburgermeny 15% 1 160,00 160,00
Velg ønsket størrelse: 160g 15% 1 5,00 5,00
Velg drikke: Urge 15% 1 0,00 0,00
Ekstra tilbehør: Bernaisesaus 15% 1 20,00 20,00
Levering 15% 1 49,00 49,00
Serviceavgift 15% 1 9,00 9,00
Totalt i NOK (inkl. mva) 568,00

Nettopris MVA Totalt

MVA 15% 493,92 74,08 568,00

Informasjon om selger: Minde Maria Mohamed Imad Fahed Hamadeh
Firma ID: 921670362
MVA ID: 921670362MVA
Adresse: Conrad Mohrs veg 5, 5068 Bergen, NOR
Kvittering levert av Wolt Norway As til Kongens gate 4, 0153 Oslo, NOR med følgende organisasjonsnummer
920 464 254 på vegne av selgeren.
Denne kvitteringen er signert digitalt.
RECEIPT;

$receiptParser->scanText($text)

```

## Testing

```bash
composer test
```

## Notes

Using [spatie/pdf-to-text](https://github.com/spatie/pdf-to-text)

Install with composer

```shell
 composer require spatie/pdf-to-text
```

Use it like so:

```php

use HelgeSverre\ReceiptParser;
use Spatie\PdfToText\Pdf;

$receipt = ReceiptParser::scanText(
    Pdf::getText('receipt.pdf')
)

dd($receipt);

```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
