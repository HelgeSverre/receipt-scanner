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

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'vatId' => $this->vatId,
            'address' => $this->address,
            'city' => $this->city,
            'zip' => $this->zip,
            'country' => $this->country,
            'website' => $this->website,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}
