<?php

namespace App\Modules;

use Illuminate\Http\UploadedFile;

class UploadFiles
{
    /**
     * Директория
     */
    public const DIRECTORY = 'uploads/';

    private static $FILENAME;

    /**Экземпляр*/
    private static $INSTANCE;

    static function Init(){
        if((!self::$INSTANCE instanceof self))
            self::$INSTANCE = new self();
        return self::$INSTANCE;
    }

    /**
     * Загрузить файлы
     * @param UploadedFile $file
     * @param array $whiteList
     * @return bool
     */
    public static function upload(UploadedFile $file, array $whiteList = []) {
        if (!file_exists(self::DIRECTORY)) {
            mkdir(self::DIRECTORY);
            chmod(self::DIRECTORY, 0777);
        }

        $filename = self::translit($file->getClientOriginalName());

        // Если предан массив разрешенных файлов, то при отсутствии сработает выход
        if (!empty($whiteList) && !in_array($filename, $whiteList)) {
            return false;
        }

        $path = $file->path();

        if (file_exists(self::DIRECTORY.$filename))
            unlink(self::DIRECTORY.$filename);

        if (is_uploaded_file($path)) {
            $result =  move_uploaded_file( $path,self::DIRECTORY.$filename);
            if (file_exists(self::DIRECTORY.$filename)) {
                chmod(self::DIRECTORY.$filename, 0777);
                self::$FILENAME = $filename;
            }
            return $result;
        } else {
           return false;
        }
    }

    /**
     * Транслит в англ.
     * @param string $filename
     * @return string
     */
    public static function translit(string $filename)
    {
        $alphas = [
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        ];
        return strtr($filename, $alphas);
    }

    /**
     * имя загруженного файла
     * @return mixed
     */
    public static function resultFileName() {
        return self::$FILENAME;
    }
}
