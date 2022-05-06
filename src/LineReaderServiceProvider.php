<?php

namespace FruiVita\LineReader;

use Illuminate\Support\ServiceProvider;

/**
 * @see https://laravel.com/docs/packages
 * @see https://laravel.com/docs/packages#service-providers
 * @see https://laravel.com/docs/providers
 */
class LineReaderServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->bind('line-reader', function ($app) {
            return new LineReader();
        });
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../lang' => lang_path('lang/vendor/line-reader'),
            ], 'lang');
        }
    }
}
