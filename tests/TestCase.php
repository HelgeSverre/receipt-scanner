<?php

namespace HelgeSverre\ReceiptScanner\Tests;

use Dotenv\Dotenv;
use HelgeSverre\ReceiptScanner\ReceiptScannerServiceProvider;
use OpenAI\Laravel\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ReceiptScannerServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // Load .env.test into the environment.
        if (file_exists(dirname(__DIR__).'/.env')) {
            (Dotenv::createImmutable(dirname(__DIR__), '.env'))->load();
        }

        config()->set('database.default', 'testing');

        config()->set('openai.api_key', env('OPENAI_KEY'));

        config()->set('receipt-scanner.textract_region', env('TEXTRACT_REGION'));
        config()->set('receipt-scanner.textract_version', env('TEXTRACT_VERSION'));
        config()->set('receipt-scanner.textract_key', env('TEXTRACT_KEY'));
        config()->set('receipt-scanner.textract_secret', env('TEXTRACT_SECRET'));
    }
}
