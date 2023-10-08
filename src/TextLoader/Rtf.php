<?php

namespace HelgeSverre\ReceiptScanner\TextLoader;

use HelgeSverre\ReceiptScanner\Contracts\TextLoader;
use HelgeSverre\ReceiptScanner\TextContent;
use Jstewmc\Rtf\Document;

class Rtf implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        $document = new Document($data);
        $text = $document->getRoot()->toText();

        return new TextContent($text);
    }
}
