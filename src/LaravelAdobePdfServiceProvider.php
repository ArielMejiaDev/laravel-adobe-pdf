<?php

namespace ArielMejiaDev\LaravelAdobePdf;

use ArielMejiaDev\LaravelAdobePdf\Client\AdobePdfClient;
use ArielMejiaDev\LaravelAdobePdf\Client\TokenManager;
use ArielMejiaDev\LaravelAdobePdf\Operations\OperationDispatcher;
use ArielMejiaDev\LaravelAdobePdf\Operations\OperationRegistry;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelAdobePdfServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-adobe-pdf')
            ->hasConfigFile()
            ->hasMigration('create_adobe_pdf_processes_table');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(TokenManager::class, function ($app) {
            $config = $app['config']->get('adobe-pdf');

            return new TokenManager(
                $config,
                $app['cache']->store($config['token']['store'] ?? null),
            );
        });

        $this->app->singleton(AdobePdfClient::class, function ($app) {
            return new AdobePdfClient(
                $app['config']->get('adobe-pdf'),
                $app->make(TokenManager::class),
            );
        });

        $this->app->singleton(OperationRegistry::class);

        $this->app->singleton(OperationDispatcher::class, function ($app) {
            return new OperationDispatcher($app['config']->get('adobe-pdf'));
        });

        $this->app->singleton(LaravelAdobePdf::class);
    }
}
