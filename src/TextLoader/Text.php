<?php

namespace HelgeSverre\ReceiptParser\TextLoader;

use HelgeSverre\ReceiptParser\Contracts\TextLoader;
use HelgeSverre\ReceiptParser\TextContent;

class Text implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent($data);
    }
}
