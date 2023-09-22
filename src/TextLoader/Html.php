<?php

namespace HelgeSverre\ReceiptScanner\TextLoader;

use HelgeSverre\ReceiptScanner\Contracts\TextLoader;
use HelgeSverre\ReceiptScanner\TextContent;
use HelgeSverre\ReceiptScanner\TextUtils;

class Html implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent(
            TextUtils::cleanHtml($data)
        );
    }
}
