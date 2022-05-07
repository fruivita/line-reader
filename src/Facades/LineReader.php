<?php

namespace FruiVita\LineReader\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Generator readLines(string $file_path)
 * @method static \Illuminate\Pagination\LengthAwarePaginator readPaginatedLines(string $file_path, int $per_page, int $page, string $page_name = 'page')
 *
 * @see \FruiVita\LineReader\LineReader
 * @see https://laravel.com/docs/facades
 */
class LineReader extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'line-reader';
    }
}
