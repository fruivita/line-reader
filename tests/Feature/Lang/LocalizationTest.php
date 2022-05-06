<?php

/**
 * @see https://pestphp.com/docs/
 */

use FruiVita\LineReader\Exceptions\FileNotReadableException;
use Illuminate\Support\Facades\App;

// Happy path
test('exception with default message in English', function () {
    $exception = new FileNotReadableException();

    expect($exception->getMessage())->toBe('The file entered could not be read');
});

test('exception with default message in Portuguese changing the locale', function () {
    App::setLocale('pt-br');

    $exception = new FileNotReadableException();

    expect($exception->getMessage())->toBe('O arquivo informado não pôde ser lido');
});
