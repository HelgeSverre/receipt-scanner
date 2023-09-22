<?php

namespace HelgeSverre\ReceiptParser\Tests;

use Dotenv\Dotenv;
use HelgeSverre\ReceiptParser\ReceiptParserServiceProvider;
use OpenAI\Laravel\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ReceiptParserServiceProvider::class,
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

        config()->set('receipt-parser.textract_region', env('TEXTRACT_REGION'));
        config()->set('receipt-parser.textract_version', env('TEXTRACT_VERSION'));
        config()->set('receipt-parser.textract_key', env('TEXTRACT_KEY'));
        config()->set('receipt-parser.textract_secret', env('TEXTRACT_SECRET'));
    }
}
