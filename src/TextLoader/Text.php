<?php

namespace HelgeSverre\ReceiptScanner\TextLoader;

use HelgeSverre\ReceiptScanner\Contracts\TextLoader;
use HelgeSverre\ReceiptScanner\TextContent;

class Text implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent($data);
    }
}
