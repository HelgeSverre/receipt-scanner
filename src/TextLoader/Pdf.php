<?php

namespace HelgeSverre\ReceiptParser\TextLoader;

use HelgeSverre\ReceiptParser\Contracts\TextLoader;
use HelgeSverre\ReceiptParser\TextContent;
use Smalot\PdfParser\Parser;

class Pdf implements TextLoader
{
    protected Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function load(mixed $data, array $meta = []): ?TextContent
    {
        return new TextContent(
            $this->parser->parseContent($data)->getText()
        );
    }
}
