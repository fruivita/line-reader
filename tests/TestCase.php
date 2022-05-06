<?php

namespace FruiVita\LineReader\Tests;

use FruiVita\LineReader\LineReaderServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app): array
    {
        return [
            LineReaderServiceProvider::class,
        ];
    }
}
