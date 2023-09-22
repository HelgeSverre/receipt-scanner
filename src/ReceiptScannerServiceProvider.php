<?php

namespace HelgeSverre\ReceiptScanner;

use Aws\Textract\TextractClient;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ReceiptScannerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('receipt-scanner')
            ->hasConfigFile();

    }

    public function packageBooted()
    {

        $this->loadViewsFrom($this->package->basePath('/../resources/prompts'), 'receipt-scanner');

        $this->publishes([
            $this->package->basePath('/../resources/prompts') => base_path("resources/prompts/vendor/{$this->packageView($this->package->viewNamespace)}"),
        ], "{$this->packageView($this->package->viewNamespace)}-prompts");

        $this->app->singleton(TextLoaderFactory::class, fn ($app) => new TextLoaderFactory($app));

        $this->app->bind(TextractClient::class, function () {
            return new TextractClient([
                'region' => config('receipt-scanner.textract_region'),
                'version' => config('receipt-scanner.textract_version'),
                'credentials' => [
                    'key' => config('receipt-scanner.textract_key'),
                    'secret' => config('receipt-scanner.textract_secret'),
                ],
            ]);
        });
    }
}
