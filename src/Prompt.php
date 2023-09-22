<?php

namespace HelgeSverre\ReceiptParser;

use Illuminate\Support\Facades\View;

class Prompt
{
    public static function load(string $filename, array $data = []): string
    {
        return View::make("receipt-parser::{$filename}", $data)->render();
    }
}
