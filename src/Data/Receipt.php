<?php

namespace HelgeSverre\ReceiptScanner\Data;

use Carbon\Carbon;
use HelgeSverre\ReceiptScanner\NumberParser;
use Illuminate\Support\Arr;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

/**
 * @property-read LineItem[] $lineItems
 */
class Receipt
{
    public function __construct(
        public ?string $orderRef,
        public ?Carbon $date,
        public ?float $taxAmount,
        public ?float $totalAmount,
        public ?CurrencyAlpha3 $currency,
        public ?Merchant $merchant,
        public ?array $lineItems,
    ) {
    }

    public static function fromJson(array $json): self
    {
        $asString = fn ($value): ?string => is_array($value) ? implode(', ', $value) : $value;

        return new self(
            orderRef: $asString(Arr::get($json, 'orderRef')),
            date: rescue(fn () => Carbon::parse(Arr::get($json, 'date')), report: false),
            taxAmount: NumberParser::parse(Arr::get($json, 'taxAmount', 0)) ?? 0,
            totalAmount: NumberParser::parse(Arr::get($json, 'totalAmount', 0)) ?? 0,
            currency: CurrencyAlpha3::tryFrom(Arr::get($json, 'currency', '')),
            merchant: new Merchant(
                name: $asString(Arr::get($json, 'merchant.name')),
                vatId: $asString(Arr::get($json, 'merchant.vatId')),
                address: $asString(Arr::get($json, 'merchant.address')),

                city: $asString(Arr::get($json, 'merchant.city')),
                zip: $asString(Arr::get($json, 'merchant.zip')),
                country: $asString(Arr::get($json, 'merchant.country')),
                website: $asString(Arr::get($json, 'merchant.website')),
                email: $asString(Arr::get($json, 'merchant.email')),
                phone: $asString(Arr::get($json, 'merchant.phone')),
            ),
            lineItems: collect(Arr::get($json, 'lineItems', []))
                ->map(fn ($item) => new LineItem(
                    text: Arr::get($item, 'text'),
                    sku: Arr::get($item, 'sku'),
                    qty: NumberParser::parse(Arr::get($item, 'qty')) ?? 1,
                    price: NumberParser::parse(Arr::get($item, 'price')) ?? 0,
                ))
                ->toArray(),
        );
    }

    public function toArray(): array
    {
        return [
            'orderRef' => $this->orderRef,
            'date' => $this->date?->toDateTimeString(),
            'taxAmount' => $this->taxAmount,
            'totalAmount' => $this->totalAmount,
            'currency' => $this->currency?->value,
            'merchant' => $this->merchant?->toArray(),
            'lineItems' => array_map(
                fn (LineItem $item) => $item->toArray(),
                $this->lineItems ?? []
            ),
        ];
    }
}
