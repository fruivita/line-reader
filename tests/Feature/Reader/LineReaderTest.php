<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://laravel.com/docs/mocking
 */

use FruiVita\LineReader\Exceptions\FileNotReadableException;
use FruiVita\LineReader\Facades\LineReader;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function() {
    $this->total_lines = 100;
    $this->test_file = __DIR__ . DIRECTORY_SEPARATOR . 'test_file.txt';

    if (is_file($this->test_file)) {
        return;
    }

    $file = new \SplFileObject($this->test_file, 'w');

    foreach(range(1, $this->total_lines) as $line) {
        if ($line != $this->total_lines) {
            $file->fwrite("Line {$line}" . PHP_EOL);
        }
        else {
            $file->fwrite("Line {$line}");
        }
    }
});

afterAll(function(){
    unlink(__DIR__ . DIRECTORY_SEPARATOR . 'test_file.txt');
});

// Exception
test('throws exception if it cannot read the file informed in the readLines method', function () {
    expect(
        fn () => LineReader::readLines(__DIR__ . DIRECTORY_SEPARATOR . 'foo.txt')
    )->toThrow(FileNotReadableException::class);
});

test('throws exception if it cannot read the file informed in the readPaginatedLines method', function () {
    expect(
        fn () => LineReader::readPaginatedLines(__DIR__ . DIRECTORY_SEPARATOR . 'foo.txt', 1, 1)
    )->toThrow(FileNotReadableException::class);
});

test('throws exception if per_page is lower then 1 in the readPaginatedLines method', function ($value) {
    expect(
        fn () => LineReader::readPaginatedLines($this->test_file, $value, 1)
    )->toThrow(\InvalidArgumentException::class);
})->with([
    0,
    -1
]);

test('throws exception if page is lower then 1 in the readPaginatedLines method', function ($value) {
    expect(
        fn () => LineReader::readPaginatedLines($this->test_file, 1, $value)
    )->toThrow(\InvalidArgumentException::class);
})->with([
    0,
    -1
]);

// Happy path
test('read empty files', function () {
    $test_file = __DIR__ . DIRECTORY_SEPARATOR . 'empty_file.txt';
    (new \SplFileObject($test_file, 'w'))->fwrite('');

    $result = LineReader::readLines($test_file);

    expect(iterator_to_array($result))->toHaveCount(1)
    ->and($result->current())->toBeEmpty();

    unlink($test_file);
});

test('read all lines', function () {
    $result = LineReader::readLines($this->test_file);

    $line_count = 0;
    $line = '';

    foreach ($result as $key => $line) {
        if ($key === 0) {
            expect($line)->toBe('Line 1');
        }

        $line_count++;
    }

    expect($result)->toBeInstanceOf(\Generator::class)
    ->and($line)->toBe('Line 100')
    ->and($line_count)->toBe($this->total_lines);
});

test('read first page in readPaginatedLines method', function () {
    $per_page = 15;
    $result = LineReader::readPaginatedLines($this->test_file, $per_page, 1);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
    ->and($result)->toHaveCount($per_page)
    ->and($result->first())->toBe('Line 1')
    ->and($result->last())->toBe('Line 15');
});

test('read first page in readPaginatedLines method', function () {
    $per_page = 15;
    $result = LineReader::readPaginatedLines($this->test_file, $per_page, 1);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
    ->and($result)->toHaveCount($per_page)
    ->and($result->first())->toBe('Line 1')
    ->and($result->last())->toBe('Line 15');
});

test('read second page in readPaginatedLines method', function () {
    $per_page = 15;
    $result = LineReader::readPaginatedLines($this->test_file, $per_page, 2);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
    ->and($result)->toHaveCount($per_page)
    ->and($result->first())->toBe('Line 16')
    ->and($result->last())->toBe('Line 30');
});

test('read incomplete last page in readPaginatedLines method', function () {
    $per_page = 15;
    $result = LineReader::readPaginatedLines($this->test_file, $per_page, 7);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
    ->and($result)->toHaveCount(10)
    ->and($result->first())->toBe('Line 91')
    ->and($result->last())->toBe('Line 100');
});
