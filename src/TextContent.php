<?php

namespace HelgeSverre\ReceiptParser;

use Stringable as StringableInterface;

class TextContent implements StringableInterface
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
