<?php

namespace HelgeSverre\ReceiptParser\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HelgeSverre\ReceiptParser\ReceiptParser
 */
class ReceiptParser extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \HelgeSverre\ReceiptParser\ReceiptParser::class;
    }
}
