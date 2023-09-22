<?php

namespace HelgeSverre\ReceiptScanner;

use Illuminate\Support\Facades\View;

class Prompt
{
    public static function load(string $filename, array $data = []): string
    {
        return View::make("receipt-scanner::{$filename}", $data)->render();
    }
}
