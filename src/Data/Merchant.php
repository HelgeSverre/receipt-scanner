<?php

namespace HelgeSverre\ReceiptParser\Data;

class Merchant
{
    public function __construct(
        public ?string $name,
        public ?string $vatId,

        public ?string $address,
        public ?string $city,
        public ?string $zip,
        public ?string $country,

        public ?string $website,
        public ?string $email,
        public ?string $phone,
    ) {
    }
}
