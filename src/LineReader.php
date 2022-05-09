<?php

namespace FruiVita\LineReader;

use FruiVita\LineReader\Contracts\IReadable;
use FruiVita\LineReader\Exceptions\FileNotReadableException;
use Illuminate\Pagination\LengthAwarePaginator;
use SplFileObject;

class LineReader implements IReadable
{
    /**
     * {@inheritdoc}
     */
    public function readLines(string $file_path)
    {
        throw_if(! $this->isReadable($file_path), FileNotReadableException::class);

        $file = $this->setWorkingFile($file_path);

        return $this->read($file);
    }

    /**
     * {@inheritdoc}
     */
    public function readPaginatedLines(string $file_path, int $per_page, int $page, string $page_name = 'page')
    {
        throw_if(! $this->isReadable($file_path), FileNotReadableException::class);
        throw_if(($per_page < 1 || $page < 1), \InvalidArgumentException::class);

        $file = $this->setWorkingFile($file_path);

        return new LengthAwarePaginator(
            items: $this->readPage($file, $per_page, $page),
            total: $this->totalLines($file),
            perPage: $per_page,
            currentPage: $page,
            options: [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $page_name,
                'query' => LengthAwarePaginator::resolveQueryString(),
            ]
        );
    }

    /**
     * Checks if the file to be read exists and is readable.
     *
     * @param string $file_path full path
     *
     * @return bool
     */
    private function isReadable(string $file_path)
    {
        return (new \SplFileInfo($file_path))->isReadable();
    }

    /**
     * Defines the file to be read and set to drop newlines at the end of a
     * line.
     *
     * @param string $file_path full path
     *
     * @return \SplFileObject
     */
    private function setWorkingFile(string $file_path)
    {
        $file = new \SplFileObject($file_path);

        $file->setFlags(\SplFileObject::DROP_NEW_LINE);

        return $file;
    }

    /**
     * Effectively reads the file and returns the current line.
     *
     * @param \SplFileObject $file File to be read
     *
     * @return \Generator
     */
    private function read(SplFileObject $file)
    {
        while (! $file->eof()) {
            yield $file->current();
            $file->next();
        }
    }

    /**
     * Total number of lines in the file.
     *
     * @param \SplFileObject $file File to be read
     *
     * @return int
     */
    private function totalLines(SplFileObject $file)
    {
        // Seek for end of file
        $file->seek(PHP_INT_MAX);

        return $file->key() + 1;
    }

    /**
     * Read a certain page from file and return as collection.
     *
     * The index of the item in the collection is the position of the line in
     * the file using a zero-based index, that is:
     * - index 0 line 1
     * - index 1 line 2
     * - [...]
     * - index 99 line 100
     * - and so on
     *
     * @param \SplFileObject $file File to be read
     * @param int $per_page
     * @param int $page
     *
     * @return \Illuminate\Support\Collection
     */
    private function readPage(SplFileObject $file, int $per_page, int $page)
    {
        $offset = ($page - 1) * $per_page;

        $collection = collect();

        $iterator = new \LimitIterator($this->read($file), $offset, $per_page);

        foreach ($iterator as $key => $line) {
            $collection->put($key, $line);
        }

        return $collection;
    }
}
