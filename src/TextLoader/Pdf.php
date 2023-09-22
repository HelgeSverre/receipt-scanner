<?php

namespace HelgeSverre\ReceiptScanner\TextLoader;

use HelgeSverre\ReceiptScanner\Contracts\TextLoader;
use HelgeSverre\ReceiptScanner\TextContent;
use Smalot\PdfParser\Parser;

class Pdf implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        $parser = new Parser();
        $parsed = $parser->parseContent($data);
        $text = $parsed->getText();

        return new TextContent($text);
    }
}
