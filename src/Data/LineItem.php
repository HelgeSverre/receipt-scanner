<?php

namespace HelgeSverre\ReceiptScanner\Data;

class LineItem
{
    public function __construct(
        public ?string $text,
        public ?string $sku,
        public ?float $qty,
        public ?float $price,
    ) {
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'sku' => $this->sku,
            'qty' => $this->qty,
            'price' => $this->price,
        ];
    }
}
