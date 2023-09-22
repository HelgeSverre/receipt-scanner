<?php

namespace HelgeSverre\ReceiptScanner;

use Stringable;

class TextContent implements Stringable
{
    protected string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public static function make(string $content): self
    {
        return new self($content);
    }

    public static function fromHtml(string $html): self
    {
        return new self(TextUtils::cleanHtml($html));
    }

    public static function fromPdf(string $pdfData, Parser $parser): self
    {
        return new self($parser->parseContent($pdfData)->getText());
    }

    public static function fromText(string $text): self
    {
        return new self($text);
    }

    public static function fromTextractOcr(mixed $data, TextractService $textractService): self
    {
        // Similar implementation as TextractOcr::load()
    }

    public static function fromWeb(string $url): self
    {
        return new self(TextUtils::cleanHtml(Http::get($url)->throw()->body()));
    }

    public static function fromWord(string $wordData): self
    {
        // Similar implementation as Word::loadTextFromDocx() and Word::loadTextFromDoc()
    }

    public function normalized(): string
    {
        return TextUtils::normalizeWhitespace($this->content);
    }

    public function toString(): string
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
