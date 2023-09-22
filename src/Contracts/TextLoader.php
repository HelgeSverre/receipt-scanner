<?php

namespace HelgeSverre\ReceiptParser\Contracts;

use HelgeSverre\ReceiptParser\TextContent;

interface TextLoader
{
    public function load(mixed $data): ?TextContent;
}
