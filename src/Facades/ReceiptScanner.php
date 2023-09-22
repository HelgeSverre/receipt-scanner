<?php

namespace HelgeSverre\ReceiptScanner\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HelgeSverre\ReceiptScanner\ReceiptScanner
 */
class ReceiptScanner extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \HelgeSverre\ReceiptScanner\ReceiptScanner::class;
    }
}
