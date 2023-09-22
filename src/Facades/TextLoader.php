<?php

namespace HelgeSverre\ReceiptScanner\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HelgeSverre\ReceiptScanner\TextLoaderFactory
 */
class TextLoader extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \HelgeSverre\ReceiptScanner\TextLoaderFactory::class;
    }
}
