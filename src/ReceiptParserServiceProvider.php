<?php

namespace HelgeSverre\ReceiptParser;

use Aws\Textract\TextractClient;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ReceiptParserServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('receipt-parser')
            ->hasConfigFile();

    }

    public function packageBooted()
    {

        $this->loadViewsFrom($this->package->basePath('/../prompts'), 'receipt-parser');

        $this->app->bind(TextractClient::class, function () {
            return new TextractClient([
                'region' => config('receipt-parser.textract_region'),
                'version' => config('receipt-parser.textract_version'),
                'credentials' => [
                    'key' => config('receipt-parser.textract_key'),
                    'secret' => config('receipt-parser.textract_secret'),
                ],
            ]);
        });
    }
}
