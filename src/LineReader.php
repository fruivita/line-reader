<?php

namespace FruiVita\LineReader;

use FruiVita\LineReader\Contracts\IReadable;
use FruiVita\LineReader\Exceptions\FileNotReadableException;
use Illuminate\Pagination\LengthAwarePaginator;

class LineReader implements IReadable
{
    /**
     * File to be read.
     *
     * @var \SplFileObject
     */
    protected \SplFileObject $file;

    /**
     * {@inheritdoc}
     */
    public function readLines(string $file_path)
    {
        throw_if(! $this->isReadable($file_path), FileNotReadableException::class);

        $this->setWorkingFile($file_path);

        return $this->read();
    }

    /**
     * {@inheritdoc}
     */
    public function readPaginatedLines(string $file_path, int $per_page, int $page, string $page_name = 'page')
    {
        throw_if(! $this->isReadable($file_path), FileNotReadableException::class);
        throw_if(($per_page < 1 || $page < 1), \InvalidArgumentException::class);

        $this->setWorkingFile($file_path);

        return new LengthAwarePaginator(
            items: $this->readPage($per_page, $page),
            total: $this->totalLines(),
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
     * @return void
     */
    private function setWorkingFile(string $file_path)
    {
        $this->file = new \SplFileObject($file_path);

        $this->file->setFlags(\SplFileObject::DROP_NEW_LINE);
    }

    /**
     * Effectively reads the file and returns the current line.
     *
     * @return \Generator
     */
    private function read()
    {
        while (! $this->file->eof()) {
            yield $this->file->current();
            $this->file->next();
        }
    }

    /**
     * Total number of lines in the file.
     *
     * @return int
     */
    private function totalLines()
    {
        // Seek for end of file
        $this->file->seek(PHP_INT_MAX);

        return $this->file->key() + 1;
    }

    /**
     * Read a certain page from file and return as collection.
     *
     * The index of the item in the collection is the position of the line in
     * the file.
     *
     * @param int $per_page
     * @param int $page
     *
     * @return \Illuminate\Support\Collection
     */
    private function readPage(int $per_page, int $page)
    {
        $offset = ($page - 1) * $per_page;

        $collection = collect();

        $iterator = new \LimitIterator($this->read(), $offset, $per_page);

        foreach ($iterator as $key => $line) {
            $collection->put($key + 1, $line);
        }

        return $collection;
    }
}
