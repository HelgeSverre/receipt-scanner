<?php

namespace HelgeSverre\ReceiptScanner\TextLoader;

use HelgeSverre\ReceiptScanner\Contracts\TextLoader;
use HelgeSverre\ReceiptScanner\TextContent;
use HelgeSverre\ReceiptScanner\TextUtils;
use Illuminate\Support\Facades\Http;

class Web implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent(
            content: TextUtils::cleanHtml(Http::get($data)->throw()->body()),
        );
    }
}
