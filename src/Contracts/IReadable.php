<?php

namespace FruiVita\LineReader\Contracts;

interface IReadable
{
    /**
     * Read, line by line, the given file.
     *
     * Useful, in particular, to make it possible to read large files without
     * load them entirely into memory, which could throw exceptions
     * due to memory overflow.
     *
     * @param string $file_path full path of the file to be read
     *
     * @throws \FruiVita\LineReader\Exceptions\FileNotReadableException
     *
     * @return \Generator
     */
    public function readLines(string $file_path);

    /**
     * Reads the given file in a paginated way.
     *
     * @param string $file_path full path of the file to be read
     * @param int    $per_page
     * @param int    $page
     * @param string $page_name
     *
     * @throws \FruiVita\LineReader\Exceptions\FileNotReadableException
     * @throws \InvalidArgumentException                                $per_page < 1 || $page < 1
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function readPaginatedLines(string $file_path, int $per_page, int $page, string $page_name = 'page');
}
