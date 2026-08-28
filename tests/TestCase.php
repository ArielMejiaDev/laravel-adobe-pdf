<?php

namespace ArielMejiaDev\LaravelAdobePdf\Tests;

use ArielMejiaDev\LaravelAdobePdf\LaravelAdobePdfServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'ArielMejiaDev\\LaravelAdobePdf\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $migration = include __DIR__.'/../database/migrations/create_adobe_pdf_processes_table.php.stub';
        $migration->up();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelAdobePdfServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        config()->set('adobe-pdf.client_id', 'test-client-id');
        config()->set('adobe-pdf.client_secret', 'test-client-secret');
        config()->set('adobe-pdf.base_url', 'https://pdf-services.adobe.io');
        config()->set('adobe-pdf.storage.disk', 'local');
        config()->set('adobe-pdf.polling.backoff', 0);
    }
}
