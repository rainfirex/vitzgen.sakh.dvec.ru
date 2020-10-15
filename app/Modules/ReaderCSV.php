<?php

namespace App\Modules;


class ReaderCSV
{
    const DIRECTORY = 'uploads/';
    /**
     * Ресурс
     * @var bool|resource|null
     */
    private $resource;

    private $filename;

    /**
     * Массив с данными
     * @var array
     */
    private $data = [];

    /**
     *
     * ReaderCSV constructor.
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        if (!file_exists(self::DIRECTORY)) {
            mkdir(self::DIRECTORY);
            chmod(self::DIRECTORY, 0777);
        }

        $this->resource = fopen(self::DIRECTORY.$filename, 'r');
        $this->filename = self::DIRECTORY.$filename;
    }

    /**
     * Прочитать файл
     * @param string|null $delimiter
     * @return ReaderCSV
     */
    public function read(string $delimiter = null): self {
        if (file_exists($this->filename)) {
            while(!feof($this->resource)) {
                $string =  stream_get_line($this->resource, filesize($this->filename),"\r\n");

                if ($delimiter != null)
                    $this->data[] = explode($delimiter, $string);
                else
                    $this->data[] = $string;
            }
            fclose($this->resource);
        }
        return $this;
    }

    /**
     * Массив с данными
     * @return array
     */
    public function getData(): array {
        return $this->data;
    }
}
