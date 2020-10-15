<?php

namespace App\Modules;


class Vitz
{
    /**Экземпляр*/
    private static $INSTANCE;

    private $iteratorVitz;

    private $iteratorAbonents;

    // Сформированные абонент найденные в vitz
    private $subscribers = [];

    private $unknowns = [];

    // Массив для выгрузки почты
    private $mailArray = [];

    // Имя файла для VitzMail
    private $mailFilename;

    // Общая сумма с коммисией [33]
    private $commonSumWithCommission = 0;

    // Общая сумма без коммисии [34]
    private $commonSumWithoutCommission = 0;

    // Сумма коммиссии [35]
    private $sumCommission = 0;

    // Дата []
    private $valueDate;

    // Номер []
    private $valueNum;

    // Последняя строка
    private $lastStringVitz;

    private $isError = false;

    private $error;

    private $typeReport;
    /**
     * Инициализация
     * @param string $filename
     * @param string $typeReport
     * @return Vitz
     */
    static function Init(string $filename, string $typeReport){
        if((!self::$INSTANCE instanceof self))
            self::$INSTANCE = new self($filename, $typeReport);
        return self::$INSTANCE;
    }

    const FILE_SUBSCRIBES = 'abonents.txt';

    private function __construct(string $filename, string $typeReport)
    {
        if (file_exists(ReaderCSV::DIRECTORY.$filename) && file_exists(ReaderCSV::DIRECTORY.self::FILE_SUBSCRIBES)) {
            $this->iteratorVitz = (new ReaderCSV($filename))->read(';')->getData();
            $this->iteratorAbonents = (new ReaderCSV(self::FILE_SUBSCRIBES))->read(';')->getData();
        }

        $this->typeReport = $typeReport;
    }

    /**
     * Получить текст ошибки
     * @return mixed
     */
    public function getError() {
        return $this->error;
    }

    /**
     * @return array
     */
    public function getMailArray(): array
    {
        return $this->mailArray;
    }

    /**
     * @return mixed
     */
    public function getMailFilename()
    {
        return $this->mailFilename;
    }




    /**
     * Сформировать строку ИТОГО
     * @return string
     */
    public function endString():string {

        $comSumWC = self::v_string($this->commonSumWithCommission);
        $comSWoutC = self::v_string($this->commonSumWithoutCommission);
        $sumC = self::v_string($this->sumCommission);

        $format  = '=%d;%s;%s;%s;%s;%s;';
        return sprintf (
            $format, count($this->subscribers),
            $comSumWC,
            $comSWoutC,
            $sumC,
            $this->valueNum,
            '01-01-2020'
//            $this->valueDate
        );
    }

    public function subscribers() : array {
        return $this->subscribers;
    }

    public function unknowns(): array {
        return $this->unknowns;
    }

    public function old() {
        foreach ($this->iteratorVitz as $iterator) {

            if ($this->typeReport === 'sber') {
                if (!array_key_exists('5', $iterator)) continue;

                if(!$this->findSubscribers($iterator)) {
                    $this->unknowns[] = [
                        $iterator[0], $iterator[1], $iterator[2],
                        $iterator[3], $iterator[4], $iterator[5],
                        $iterator[6], $iterator[7], $iterator[8],
                        $iterator[9], $iterator[10], $iterator[11],
                        $iterator[12], $iterator[13], $iterator[14],
                        $iterator[15], $iterator[16], $iterator[17],
                        $iterator[18], $iterator[19], $iterator[20],
                        $iterator[21], $iterator[22], $iterator[23],
                        $iterator[24], $iterator[25], $iterator[26],
                        $iterator[27], $iterator[28], $iterator[29],
                        $iterator[30], $iterator[31], $iterator[32],
                        $iterator[33], $iterator[34], $iterator[35]
                    ];
                }

            }

            if ($this->typeReport === 'mail') {
                continue;
            }

        }

        return $this;
    }

    /**
     * Сопоставить абонентов iteratorAbonents к iteratorVitz
     * @return Vitz
     */
    public function equal(): self {

        if (count($this->iteratorAbonents) <= 0 && count($this->iteratorVitz) <= 0) return $this;

        switch ($this->typeReport) {
            case 'sber':
                $this->equalSber();
            break;
            case 'mail':
                $this->equalMail();
                break;
        }
        return $this;
    }

    /**
     * Сбербанк
     */
    private function equalSber(): void {
        // извлекает последнее значение массива, заполняем переменные valueNum и valueDate из файла vitz.txt
        $this->getLastStringVitzFile();

        // 56 нулей
        $arrayZero = [];
        // Создаем пустую строку 56ть раз
        for ($i =0; $i <= 55; $i++) $arrayZero[] = '';

        try {
            // Перебор сопаставленых абонентов
            for ($i = 0; $i < count($this->iteratorAbonents); $i++) {
                $ls_vitz = $this->iteratorAbonents[$i][0]; // Лицевой vitz
                $ls_energo = $this->iteratorAbonents[$i][1]; // Лицевой енерго

                foreach ($this->iteratorVitz as $iterator) {
                    if (!array_key_exists('5', $iterator)) continue;

                    if ($ls_vitz === $iterator[5]) {

                        // Подсчет сумм
                        $this->commonSumWithCommission = self::floatResult(self::v_float($this->commonSumWithCommission), self::v_float($iterator[33]));
                        $this->commonSumWithoutCommission = self::floatResult(self::v_float($this->commonSumWithoutCommission), self::v_float($iterator[34]));
                        $this->sumCommission = self::floatResult(self::v_float($this->sumCommission), self::v_float($iterator[35]));

                        // Абонент
                        $subscriber = [
                            $iterator[0], $iterator[1], $iterator[2], $iterator[3], $iterator[4], $ls_energo, $iterator[6],
                            $iterator[7], $iterator[8] = '21', $iterator[33], $iterator[10] = '27', $iterator[10] = '0.00'];

                        $end = [
                            $iterator[32], $iterator[33], $iterator[34], $iterator[35],
                        ];

                        // Создать массив объеденив
                        // след. массивы
                        // Добавить основ. данные
                        // Добавить 56 нулей
                        // Добавить конечные данные
                        $newArray = array_merge($subscriber, $arrayZero, $end);
                        $this->subscribers[] = $newArray;
                    }
                }
            }
        }
        catch (\Exception $ex) {
            $this->isError = true;
            $this->error = sprintf('Exception: %s line %s', $ex->getMessage(),  $ex->getLine());
        }
    }

    /**
     * Почта
     */
    private function equalMail(): void {

        try {

            $headers = [];
            $data = [];

            for ($j = 0; $j < count($this->iteratorVitz); $j++) {
                // Шапка
                if ($j < 12) {

                    $headers[] =  $this->iteratorVitz[$j];

                }

                // Данные
                if ($j>=12) {

                    $data[] = $this->iteratorVitz[$j];

                }
            }



            // Формирование готового массива
            // Добавление шапки
            // #580,00 - сумма
            // #2      - число записей
            $this->mailArray[] = str_replace('.', ',', trim($headers[1][0]));
            $this->mailArray[] = trim($headers[5][0]);

            // Формирование даты из файла
            $datetime = str_replace('#', '', $headers[8][0]);
            $datetime = str_replace('/','', $datetime);
            $date = explode(' ', $datetime);

            // Формирование имени файла для пользователя
            $omniusCod = '11_';
            $staticName = '_Kassa_UTKO.csv';
            $this->mailFilename = $omniusCod.$date[0].$staticName;
            ///////////////////////////////////////////////////////

            for ($i = 0; $i < count($this->iteratorAbonents); $i++) {
                $ls_vitz = $this->iteratorAbonents[$i][0]; // Лицевой mail
                $ls_energo = $this->iteratorAbonents[$i][1]; // Лицевой енерго

                foreach ($data as $datum) {

                    // Если не массив, то досвидос!
                    if (empty($datum[2])) continue;

                    // Лиц. счет
                    $licData = $datum[2];
                    $sumData = $datum[3];

                    // Сопоставление
                    if ($licData === $ls_vitz) {

                        // Замена лицевого
                        //$datum[2] = $ls_energo;

                        $this->mailArray[] = $ls_energo.';'.$sumData;
                    }
                }

            }
        }
        catch (\Exception $ex) {
            $this->isError = true;
            $this->error = sprintf('Exception: %s line %s', $ex->getMessage(),  $ex->getLine());
        }
    }

    /**
     *  Извлекает последнее значение массива,
     *  заполняем переменные valueNum и valueDate из файла vitz.txt
     */
    private function getLastStringVitzFile() {
        while (true) {
            // извлекает последнее значение массива и возвращает его
            $this->lastStringVitz = array_pop($this->iteratorVitz);
            // Если 5й ключ сущ., то остановка цикла
            if (array_key_exists(5,$this->lastStringVitz)) {

//                // Удаление пустых массивов
//                foreach ($this->lastStringVitz as $k => $v) {
//                    if (empty($v)) {
//                        unset($this->lastStringVitz[$k]);
//                    }
//                }
//                // Номер и дата из файла vitz.txt
//                $this->valueDate = array_pop($this->lastStringVitz);
//                $this->valueNum = array_pop($this->lastStringVitz);

                $this->valueNum = $this->lastStringVitz[4];
                $this->valueDate = $this->lastStringVitz[5];
                break;
            }


        }

    }

    /**
     * Поиск в сформированных абонентах
     * @param $iterator
     * @return bool
     */
    private function findSubscribers($iterator): bool {
        foreach ($this->subscribers as $subscriber) {
            if (
                $iterator[0] === $subscriber[0] &&
                $iterator[1] === $subscriber[1] &&
                $iterator[2] === $subscriber[2] &&
                $iterator[3] === $subscriber[3] &&
                $iterator[4] === $subscriber[4] &&
                $iterator[6] === $subscriber[6] &&
                $iterator[7] === $subscriber[7]
            ) {
                return  true;
            }
        }

        return  false;
    }

    /**
     * @param string $value
     * @return float
     */
    public static function v_float(string $value) : float {
        if (!empty($value))
            return floatval(str_replace(',','.', $value));
        else{
            return 0.0;
        }
    }

    /**
     * @param float $v1
     * @param float $v2
     * @return string
     */
    public static function floatResult(float $v1, float $v2): string {
        return sprintf('%.2f',  $v1 + $v2);
    }


    public static function v_string(float $value) : string {
        return str_replace('.',',', $value);
    }
}
