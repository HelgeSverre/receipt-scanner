<?php

namespace HelgeSverre\ReceiptParser\TextLoader;

use HelgeSverre\ReceiptParser\Contracts\TextLoader;
use HelgeSverre\ReceiptParser\TextContent;
use HelgeSverre\ReceiptParser\TextUtils;

class Html implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent(
            TextUtils::cleanHtml($data)
        );
    }
}
