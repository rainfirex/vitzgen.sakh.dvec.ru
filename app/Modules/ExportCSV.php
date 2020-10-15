<?php namespace App\Modules {
    /**
     * Создание csv файл
     * Class ExportCSV
     * @package App\Modules
     */
    class ExportCSV
    {
        /**
         * Директория
         */
        public const DIRECTORY = 'reports/';

        /**
         * Ресурс
         * @var bool|resource|null
         */
        private $resource = null;

        private $filename;

        public function __construct(string $filename) {
            if (!file_exists(self::DIRECTORY)) {
                mkdir(self::DIRECTORY);
                chmod(self::DIRECTORY, 0777);
            }
            $this->resource = fopen(self::DIRECTORY.$filename, 'w');
            $this->filename = self::DIRECTORY.$filename;
        }

        /**
         * Сохранить
         * @param array $array
         */
        public function storage(array $array) {
            $column_delimiter = ';';
            if (is_array($array)) {
                foreach ($array as $fields) {
                    //Так вот чтобы проблемы с кодировкой не было, надо всего лишь добавить 3 байта в файл.
                    fputs($this->resource, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

                    fputcsv($this->resource, $fields, $column_delimiter);
                }
                fclose($this->resource);
            }
        }

        /**
         * Сохранить с колонками
         * @param array $array
         * @param array $columns
         */
        public function storageWithColumns(array $array, array $columns = []) {
            $column_delimiter = ';';
            $enclosure = '*';
            if (is_array($array)) {

                if (empty($columns))
                    $columns = array_keys($array[0]);

                fputcsv($this->resource, $columns, $column_delimiter, $enclosure);
                foreach ($array as $fields) {

                    array_filter($fields, function ($value, $key) {
                    }, ARRAY_FILTER_USE_BOTH);

                    //Так вот чтобы проблемы с кодировкой не было, надо всего лишь добавить 3 байта в файл.
                    fputs($this->resource, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

                    fputcsv($this->resource, $fields, $column_delimiter, $enclosure);
                }
                fclose($this->resource);
            }
        }

        /**
         * Сохранить построчно
         * @param array $array
         * @param array|null $columns
         * @param bool $isBom
         * @return $this
         */
        public function save(array $array, ?array $columns = [], $isBom = false) {
            $column_delimiter = ';';
            $isNumberColumn = false;

            if (empty($columns)) {
                if (is_array($array[0])) {
                    $columns = array_keys($array[0]);
                }
            }

            // Если колонки содержат цифры $isNumberColumn = true
            foreach ($columns as $column) {
                if (preg_match('/\d+/i',$column, $matches)) {
                    if($matches[0]) {
                        $isNumberColumn = true;
                        break;
                    }
                }
            }

            if (!$isNumberColumn)
                fputcsv($this->resource, $columns, $column_delimiter);

            foreach ($array as $value) {
                if (is_array($value))
                    $value = implode($column_delimiter, $value);

                if (!empty($value))
                    fwrite($this->resource, $value."\n");
            }

            //Так вот чтобы проблемы с кодировкой не было, надо всего лишь добавить 3 байта в файл.
            if ($isBom) {
                fputs($this->resource, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
            }

            fclose($this->resource);

            return $this;
        }

        /**
         * Переписать файл в новой кодировке
         * @param string $encoding
         * @param string $from_encoding
         */
        public function encoding(string $encoding = "UTF-8", string $from_encoding = "cp1251") {
            $data = file_get_contents($this->filename);
            $data = mb_convert_encoding($data, $encoding, $from_encoding);
            file_put_contents(trim($this->filename), trim( chr(0xEF) . chr(0xBB) . chr(0xBF).$data));
        }

        /**
         * Дописать строку
         * @param string $string
         * @return ExportCSV
         */
        public function writeLine(string $string):self {
            if (!empty($string)) {
                $this->resource = fopen($this->filename, 'a');
                //Так вот чтобы проблемы с кодировкой не было, надо всего лишь добавить 3 байта в файл.
//                fputs($this->resource, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
                fwrite($this->resource, $string."\n");
                fclose($this->resource);
            }
            return $this;
        }

        /**
         * Сохранить в JSON
         * @param array $array
         */
        public function storageJson(array $array) {
            $data = json_encode($array, JSON_UNESCAPED_UNICODE);
            $fileName = explode('.', $this->filename);
            file_put_contents($fileName[0].'.json', trim( chr(0xEF) . chr(0xBB) . chr(0xBF).$data));
        }

    }
}
