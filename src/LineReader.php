<?php

namespace FruiVita\LineReader;

use FruiVita\LineReader\Contracts\IReadable;
use FruiVita\LineReader\Exceptions\FileNotReadableException;

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
    public function readPaginatedLines(string $file_path, int $per_page, int $page)
    {
        throw_if(! $this->isReadable($file_path), FileNotReadableException::class);
        throw_if(($per_page < 1 || $page < 1), \InvalidArgumentException::class);

        $this->setWorkingFile($file_path);

        $offset = ($page - 1) * $per_page;

        $collection = collect();

        for ($i = $offset; $i < $offset + $per_page; ++$i) {
            $this->file->seek($i);

            if ($this->file->eof()) {
                break;
            }

            $collection->put($i + 1, $this->file->current());
        }

        // Seek for end of file
        $this->file->seek(PHP_INT_MAX);

        return $collection;
        // ->paginate(
        //     $this->file->key() + 1,
        //     $per_page,
        //     $page
        // );

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
}
