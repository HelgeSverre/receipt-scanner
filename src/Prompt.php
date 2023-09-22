<?php

namespace HelgeSverre\ReceiptParser;

use Exception;

class Prompt
{
    public function __construct(protected string $content)
    {

    }

    public static function load(string $filename): self
    {
        $promptFilePath = __DIR__.'/Prompts/'.$filename.'.txt';

        if (! file_exists($promptFilePath)) {

            throw new Exception("Prompt file '{$filename}' does not exist in '{$promptFilePath}'.");
        }

        $content = file_get_contents($promptFilePath);
        if ($content === false) {
            throw new Exception("Failed to read the content of the prompt file {$filename}.");
        }

        return new self($content);
    }

    public function replace(string $placeholder, string $replacement): self
    {
        $this->content = str_replace(
            search: $placeholder,
            replace: $replacement,
            subject: $this->content
        );

        return $this;
    }

    public function toString(): string
    {
        return $this->content;
    }
}
