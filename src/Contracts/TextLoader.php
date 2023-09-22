<?php

namespace HelgeSverre\ReceiptScanner\Contracts;

use HelgeSverre\ReceiptScanner\TextContent;

interface TextLoader
{
    public function load(mixed $data): ?TextContent;
}
