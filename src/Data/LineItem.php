<?php

namespace HelgeSverre\ReceiptParser\Data;

class LineItem
{
    public function __construct(
        public ?string $text,
        public ?string $sku,
        public ?float $qty,
        public ?float $price,
    ) {
    }
}
