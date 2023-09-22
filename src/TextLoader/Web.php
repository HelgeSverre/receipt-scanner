<?php

namespace HelgeSverre\ReceiptParser\TextLoader;

use HelgeSverre\ReceiptParser\Contracts\TextLoader;
use HelgeSverre\ReceiptParser\TextContent;
use HelgeSverre\ReceiptParser\TextUtils;
use Illuminate\Support\Facades\Http;

class Web implements TextLoader
{
    public function load(mixed $data, array $meta = []): ?TextContent
    {
        return new TextContent(
            content: TextUtils::cleanHtml(Http::get($data)->throw()->body()),
        );
    }
}
