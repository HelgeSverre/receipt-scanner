<?php

use HelgeSverre\ReceiptParser\Data\Receipt;
use HelgeSverre\ReceiptParser\Enums\Model;
use HelgeSverre\ReceiptParser\Facades\ReceiptParser;
use HelgeSverre\ReceiptParser\TextLoader\TextractOcr;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

it('the receipt data is correctly parsed into a DTO', function () {

    OpenAI::fake([
        CreateResponse::fake([
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

    $result = ReceiptParser::scan($text, model: Model::TURBO);

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

        expect($lineItem->text)->toBe($expectedResult['lineItems'][$index]['name'])
            ->and((float) $lineItem->qty)->toBe((float) $expectedResult['lineItems'][$index]['qty'])
            ->and($lineItem->price)->toBe($expectedResult['lineItems'][$index]['price'])
            ->and($lineItem->sku)->toBe($expectedResult['lineItems'][$index]['sku']);
    }
});

it('We can actually use this for reals', function () {

    $text = file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.txt');

    $result = ReceiptParser::scan($text, model: Model::TURBO_16K);

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

        expect($lineItem->text)->toBe($expectedResult['lineItems'][$index]['name'])
            ->and((float) $lineItem->qty)->toBe((float) $expectedResult['lineItems'][$index]['qty'])
            ->and($lineItem->price)->toBe($expectedResult['lineItems'][$index]['price'])
            ->and($lineItem->sku)->toBe($expectedResult['lineItems'][$index]['sku']);
    }
});

it('We can actually use this for real even faster', function () {

    $text = file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.txt');

    $result = ReceiptParser::scan($text);

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

        dump($lineItem);

        expect(Str::contains($expectedResult['lineItems'][$index]['name'], $lineItem->text))->toBeTrue()
            ->and((float) $lineItem->qty)->toBe((float) $expectedResult['lineItems'][$index]['qty'])
            ->and($lineItem->price)->toBe($expectedResult['lineItems'][$index]['price'])
            ->and($lineItem->sku)->toBe($expectedResult['lineItems'][$index]['sku']);
    }
});

it('The ocr stuff works', function () {

    $image = file_get_contents(__DIR__.'/samples/grocery-receipt-norwegian-spar.jpg');

    /** @var TextractOcr $ocr */
    $ocr = resolve(TextractOcr::class);

    $text = $ocr->load($image);

    dump($text);

    $result = ReceiptParser::scan($text);

    expect($result)->toBeInstanceOf(Receipt::class)
        ->and($result->totalAmount)->toBe(852.00)
        ->and($result->orderRef)->toBe('66907')
        ->and($result->date->format('Y-m-d'))->toBe('2022-10-30')
        ->and($result->taxAmount)->toBe(109.73)
        ->and($result->currency->value)->toBe('NOK')
        ->and(Str::contains($result->merchant->name, 'SPAR'))->toBeTrue();

});
