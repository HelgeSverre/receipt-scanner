<?php

namespace HelgeSverre\ReceiptScanner;

use HelgeSverre\ReceiptScanner\Contracts\TextLoader;
use HelgeSverre\ReceiptScanner\TextLoader\Html;
use HelgeSverre\ReceiptScanner\TextLoader\Pdf;
use HelgeSverre\ReceiptScanner\TextLoader\Text;
use HelgeSverre\ReceiptScanner\TextLoader\Textract;
use HelgeSverre\ReceiptScanner\TextLoader\TextractUsingS3Upload;
use HelgeSverre\ReceiptScanner\TextLoader\Web;
use HelgeSverre\ReceiptScanner\TextLoader\Word;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class TextLoaderFactory
{
    use Macroable;

    public function __construct(protected Container $container)
    {
    }

    public function create(string $type): TextLoader
    {
        return match ($type) {
            'html' => $this->container->make(Html::class),
            'pdf' => $this->container->make(Pdf::class),
            'text' => $this->container->make(Text::class),
            'textract_s3' => $this->container->make(TextractUsingS3Upload::class),
            'textract' => $this->container->make(Textract::class),
            'web' => $this->container->make(Web::class),
            'word' => $this->container->make(Word::class),
            default => throw new InvalidArgumentException("Invalid text loader type: $type"),
        };
    }

    // Convenience Methods
    public function html(mixed $data): ?TextContent
    {
        return $this->create('html')->load($data);
    }

    public function pdf(mixed $data): ?TextContent
    {
        return $this->create('pdf')->load($data);
    }

    public function text(mixed $data): ?TextContent
    {
        return $this->create('text')->load($data);
    }

    public function textractUsingS3Upload(mixed $data): ?TextContent
    {
        return $this->create('textract_s3')->load($data);
    }

    public function textract(mixed $data): ?TextContent
    {
        return $this->create('textract')->load($data);
    }

    public function web(mixed $data): ?TextContent
    {
        return $this->create('web')->load($data);
    }

    public function word(mixed $data): ?TextContent
    {
        return $this->create('word')->load($data);
    }
}
