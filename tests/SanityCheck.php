<?php

use HelgeSverre\ReceiptScanner\Data\Receipt;
use HelgeSverre\ReceiptScanner\Enums\Model;
use HelgeSverre\ReceiptScanner\Facades\ReceiptScanner;
use HelgeSverre\ReceiptScanner\Prompt;
use HelgeSverre\ReceiptScanner\TextLoader\TextractOcr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;
use OpenAI\Responses\Completions\CreateResponse as CompletionResponse;

it('validates parsing of receipt data into dto', function () {
    OpenAI::fake([
        ChatResponse::fake([
            'model' => 'gpt-3.5-turbo',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.json'),
                        'function_call' => null,
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
        ]),
    ]);

    $text = file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.txt');
    $result = ReceiptScanner::scan($text, model: Model::TURBO);

    expect($result)->toBeInstanceOf(Receipt::class)
        ->and($result->totalAmount)->toBe(568.00)
        ->and($result->orderRef)->toBe('61e4fb2646c424c5cbc9bc88')
        ->and($result->date->format('Y-m-d'))->toBe('2023-07-21')
        ->and($result->taxAmount)->toBe(74.08)
        ->and($result->currency->value)->toBe('NOK')
        ->and($result->merchant->name)->toBe('Minde Pizzeria')
        ->and($result->merchant->vatId)->toBe('921670362MVA')
        ->and($result->merchant->address)->toBe('Conrad Mohrs veg 5, 5068 Bergen, NOR');

    $expectedResult = json_decode(file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.json'), true);

    // Asserting line items
    foreach ($result->lineItems as $index => $lineItem) {
        expect($lineItem->text)->toBe($expectedResult['lineItems'][$index]['text'], "was '{$lineItem->text}' instead")
            ->and((float) $lineItem->qty)->toBe((float) $expectedResult['lineItems'][$index]['qty'], "was '{$lineItem->qty}' instead")
            ->and($lineItem->price)->toBe($expectedResult['lineItems'][$index]['price'], "was '{$lineItem->price}' instead")
            ->and($lineItem->sku)->toBe($expectedResult['lineItems'][$index]['sku'], "was '{$lineItem->sku}' instead");
    }
});

it('confirms real world usability with TURBO 16K model', function () {

    $text = file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.txt');
    $result = ReceiptScanner::scan($text, model: Model::TURBO_16K);

    expect($result)->toBeInstanceOf(Receipt::class)
        ->and($result->totalAmount)->toBe(568.00)
        ->and($result->orderRef)->toBe('61e4fb2646c424c5cbc9bc88')
        ->and($result->date->format('Y-m-d'))->toBe('2023-07-21')
        ->and($result->taxAmount)->toBe(74.08)
        ->and($result->currency->value)->toBe('NOK')
        ->and($result->merchant->name)->toBe('Minde Pizzeria')
        ->and($result->merchant->vatId)->toBe('921670362MVA')
        ->and($result->merchant->address)->toBe('Conrad Mohrs veg 5, 5068 Bergen, NOR');
    $expectedResult = json_decode(file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.json'), true);

    // Asserting line items
    foreach ($result->lineItems as $index => $lineItem) {
        expect($lineItem->text)->toBe($expectedResult['lineItems'][$index]['text'])
            ->and((float) $lineItem->qty)->toBe((float) $expectedResult['lineItems'][$index]['qty'])
            ->and($lineItem->price)->toBe($expectedResult['lineItems'][$index]['price'])
            ->and($lineItem->sku)->toBe($expectedResult['lineItems'][$index]['sku']);
    }
});

it('validates returning parsed receipt as array', function () {
    OpenAI::fake([
        CompletionResponse::fake([
            'model' => 'gpt-3.5-turbo',
            'choices' => [
                [
                    'text' => file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.json'),
                ],
            ],
        ]),
    ]);

    $text = file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.txt');
    $result = ReceiptScanner::scan($text, model: Model::TURBO_INSTRUCT, asArray: true);

    expect($result)->toBeArray()
        ->and($result['totalAmount'])->toBe(568.00)
        ->and($result['orderRef'])->toBe('61e4fb2646c424c5cbc9bc88')
        ->and($result['date'])->toBe('2023-07-21')
        ->and($result['taxAmount'])->toBe(74.08)
        ->and($result['currency'])->toBe('NOK')
        ->and($result['merchant']['name'])->toBe('Minde Pizzeria')
        ->and($result['merchant']['vatId'])->toBe('921670362MVA')
        ->and($result['merchant']['address'])->toBe('Conrad Mohrs veg 5, 5068 Bergen, NOR');
});

it('confirms real world usability with default model', function () {

    $text = file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.txt');
    $result = ReceiptScanner::scan($text);

    expect($result)->toBeInstanceOf(Receipt::class)
        ->and($result->totalAmount)->toBe(568.00)
        ->and($result->orderRef)->toBe('61e4fb2646c424c5cbc9bc88')
        ->and($result->date->format('Y-m-d'))->toBe('2023-07-21')
        ->and($result->taxAmount)->toBe(74.08)
        ->and($result->currency->value)->toBe('NOK')
        ->and($result->merchant->name)->toBe('Minde Pizzeria')
        ->and($result->merchant->vatId)->toBe('921670362MVA')
        ->and($result->merchant->address)->toBe('Conrad Mohrs veg 5, 5068 Bergen, NOR');

    $expectedResult = json_decode(file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.json'), true);

    // Asserting line items
    foreach ($result->lineItems as $index => $lineItem) {
        expect(Str::contains($expectedResult['lineItems'][$index]['text'], $lineItem->text))->toBeTrue()
            ->and((float) $lineItem->qty)->toBe((float) $expectedResult['lineItems'][$index]['qty'])
            ->and($lineItem->price)->toBe($expectedResult['lineItems'][$index]['price'])
            ->and($lineItem->sku)->toBe($expectedResult['lineItems'][$index]['sku']);
    }
});

it('validates ocr functionality with image input', function () {
    $image = file_get_contents(__DIR__.'/samples/grocery-receipt-norwegian-spar.jpg');
    /** @var TextractOcr $ocr */
    $ocr = resolve(TextractOcr::class);
    $text = $ocr->load($image);
    $result = ReceiptScanner::scan($text);
    expect($result)->toBeInstanceOf(Receipt::class)
        ->and($result->totalAmount)->toBe(852.00)
        ->and($result->orderRef)->toBe('66907')
        ->and($result->date->format('Y-m-d'))->toBe('2022-10-30')
        ->and($result->taxAmount)->toBe(109.73)
        ->and($result->currency->value)->toBe('NOK')
        ->and(Str::contains($result->merchant->name, 'SPAR'))->toBeTrue();
});

it('loads prompts and injects context into blade files', function () {
    $prompt = Prompt::load('receipt', ['context' => 'hello world']);
    expect($prompt)->toBeString()
        ->and(Str::contains($prompt, 'hello world'))->toBeTrue();
});

it('throws exception for missing configuration', function () {
    Config::set('receipt-parser.textract_disk', null);
    /** @var TextractOcr $ocr */
    $ocr = resolve(TextractOcr::class);

    // Create a mock UploadedFile instance for a PDF
    $pdfFile = UploadedFile::fake()->create('document.pdf');

    // Expect an exception to be thrown
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('is not set, it is required for OCR-ing PDFs');
    $this->expectExceptionMessage('Configuration option');
    $ocr->load($pdfFile);
});

it('throws exception when storage operation fails', function () {
    Storage::shouldReceive('disk->put')->andReturn(false);
    /** @var TextractOcr $ocr */
    $ocr = resolve(TextractOcr::class);

    // Create a mock UploadedFile instance
    $file = UploadedFile::fake()->create('document.pdf');

    // Expect an exception to be thrown
    $this->expectException(Exception::class);
    $ocr->load($file);
});
