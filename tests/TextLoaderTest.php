<?php

use HelgeSverre\ReceiptScanner\Facades\TextLoader;
use HelgeSverre\ReceiptScanner\TextContent;

it('Can load Text', function () {
    $text = TextLoader::text()->load(file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.txt'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Helge Sverre Hessevik Liseth',
        'Conrad Mohrs veg 5',
        'Wolt',
    );
});

it('Can load PDFs', function () {
    $text = TextLoader::pdf()->load(file_get_contents(__DIR__.'/samples/laravel-certification-invoice.pdf'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'contact@laravelcert.com',
        'Helge Sverre Hessevik Liseth',
    );
});

it('Can OCR images', function () {
    $text = TextLoader::textract()->load(file_get_contents(__DIR__.'/samples/grocery-receipt-norwegian-spar.jpg'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'TOMATER',
        'HAKKEDE',
        'SKINKE',
        'SPAR DALE',
    );
});

it('Can OCR Pdfs', function () {
    $text = TextLoader::textract()->load(file_get_contents(__DIR__.'/samples/laravel-certification-invoice.pdf'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'contact@laravelcert.com',
        'Helge Sverre Hessevik Liseth',
    );
});

it('Can load Word Documents', function () {
    $text = TextLoader::word()->load(file_get_contents(__DIR__.'/samples/word-document.doc'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Mauris',
    );
});

it('Can load text from website', function () {
    $text = TextLoader::web()->load('https://sparksuite.github.io/simple-html-invoice-template/');

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Sparksuite',
        'Total: $385.00',
    );
});

it('Can load html files', function () {
    $text = TextLoader::html()->load(file_get_contents(__DIR__.'/samples/paddle-fake-subscription.html'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Thank you for your purchase!',
        'NOK 1,246.25',
    );
});
