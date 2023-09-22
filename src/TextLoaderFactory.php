<?php

namespace HelgeSverre\ReceiptScanner;

use HelgeSverre\ReceiptScanner\Contracts\TextLoader;
use HelgeSverre\ReceiptScanner\TextLoader\Html;
use HelgeSverre\ReceiptScanner\TextLoader\Pdf;
use HelgeSverre\ReceiptScanner\TextLoader\Text;
use HelgeSverre\ReceiptScanner\TextLoader\TextractOcr;
use HelgeSverre\ReceiptScanner\TextLoader\Web;
use HelgeSverre\ReceiptScanner\TextLoader\Word;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class TextLoaderFactory
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create(string $type): TextLoader
    {
        return match ($type) {
            'html' => $this->container->make(Html::class),
            'pdf' => $this->container->make(Pdf::class),
            'text' => $this->container->make(Text::class),
            'textract' => $this->container->make(TextractOcr::class),
            'web' => $this->container->make(Web::class),
            'word' => $this->container->make(Word::class),
            default => throw new InvalidArgumentException("Invalid text loader type: $type"),
        };
    }

    // Convenience Methods
    public function html(): Html
    {
        return $this->create('html');
    }

    public function pdf(): Pdf
    {
        return $this->create('pdf');
    }

    public function text(): Text
    {
        return $this->create('text');
    }

    public function textract(): TextractOcr
    {
        return $this->create('textract');
    }

    public function web(): Web
    {
        return $this->create('web');
    }

    public function word(): Word
    {
        return $this->create('word');
    }
}
