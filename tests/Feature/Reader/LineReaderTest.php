<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://www.php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
 */

use FruiVita\LineReader\Exceptions\FileNotReadableException;
use FruiVita\LineReader\Facades\LineReader;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->total_lines = 100;
    $this->test_file = __DIR__ . DIRECTORY_SEPARATOR . 'test_file.txt';

    if (is_file($this->test_file)) {
        return;
    }

    $file = new \SplFileObject($this->test_file, 'w');

    foreach (range(1, $this->total_lines) as $line) {
        if ($line != $this->total_lines) {
            $file->fwrite("Line {$line}" . PHP_EOL);
        } else {
            $file->fwrite("Line {$line}");
        }
    }
});

afterAll(function () {
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
    -1,
]);

test('throws exception if page is lower then 1 in the readPaginatedLines method', function ($value) {
    expect(
        fn () => LineReader::readPaginatedLines($this->test_file, 1, $value)
    )->toThrow(\InvalidArgumentException::class);
})->with([
    0,
    -1,
]);

// Happy path
test('read empty files with readLines', function () {
    $test_file = __DIR__ . DIRECTORY_SEPARATOR . 'empty_file.txt';
    (new \SplFileObject($test_file, 'w'))->fwrite('');

    $result = LineReader::readLines($test_file);

    expect(iterator_to_array($result))->toHaveCount(1)
    ->and($result->current())->toBeEmpty();

    unlink($test_file);
});

test('read empty files with readPaginatedLines', function () {
    $test_file = __DIR__ . DIRECTORY_SEPARATOR . 'empty_file.txt';
    (new \SplFileObject($test_file, 'w'))->fwrite('');

    $result = LineReader::readPaginatedLines($test_file, 15, 1);

    expect($result)->toHaveCount(1)
    ->and($result->first())->toBeEmpty();

    unlink($test_file);
});

test('read all lines with readLines', function () {
    $result = LineReader::readLines($this->test_file);

    $line_count = 0;
    $line = '';

    foreach ($result as $key => $line) {
        if ($key === 0) {
            expect($line)->toBe('Line 1');
        }

        ++$line_count;
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

test('nonexistent page is read without throwing exception', function () {
    $per_page = 15;
    $result = LineReader::readPaginatedLines($this->test_file, $per_page, 10);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
    ->and($result)->toHaveCount(0);
});

test('readPaginatedLines method uses a zero-based index representing the position of the line in the file', function () {
    $per_page = 15;
    $result = LineReader::readPaginatedLines($this->test_file, $per_page, 7);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
    ->and($result)->toHaveCount(10)
    ->and($result->get(90))->toBe('Line 91')
    ->and($result->get(99))->toBe('Line 100');
});

test('new blank lines above, in the middle and at the end of the file with readLines', function () {
    $test_file = __DIR__ . DIRECTORY_SEPARATOR . 'blank_lines_test.txt';
    $content = <<<CONTENT


Line 3


Line 6
Line 7


CONTENT;

    (new \SplFileObject($test_file, 'w'))->fwrite($content);

    $result = LineReader::readLines($test_file);

    expect($result)->toBeInstanceOf(\Generator::class)
    ->and(iterator_to_array($result))->toBe([
        0 => '',
        1 => '',
        2 => 'Line 3',
        3 => '',
        4 => '',
        5 => 'Line 6',
        6 => 'Line 7',
        7 => '',
        8 => '',
    ]);

    unlink($test_file);
});

test('new blank lines above, in the middle and at the end of the file with readPaginatedLines', function () {
    $test_file = __DIR__ . DIRECTORY_SEPARATOR . 'blank_lines_test.txt';
    $content = <<<CONTENT


Line 3


Line 6
Line 7


CONTENT;

    (new \SplFileObject($test_file, 'w'))->fwrite($content);

    $result = LineReader::readPaginatedLines($test_file, 4, 2);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
    ->and(iterator_to_array($result))->toBe([
        4 => '',
        5 => 'Line 6',
        6 => 'Line 7',
        7 => '',
    ]);

    unlink($test_file);
});
