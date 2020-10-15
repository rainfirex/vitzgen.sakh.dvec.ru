<?php namespace App\Modules;

class DocumentExcel
{

    private static $INSTANCE;

    private $document;

    private $sheet;

    private $cell;

    private $range;

    public static function Init(){
        if((!self::$INSTANCE instanceof self))
            self::$INSTANCE = new self();
        return self::$INSTANCE;
    }

    protected function __construct()
    {
        // подкл. библиотеки
        require_once (app_path().'/Libs/PHPExcel/PHPExcel.php');
        require_once (app_path().'/Libs/PHPExcel/PHPExcel/Reader/Excel5.php');

        $this->document = new \PHPExcel();
    }

    /**
     * Создать файл
     * @param string $filename
     * @return $this
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function CreateFile(string $filename) {
        // Создать пустой файл xls
        $objWriter = \PHPExcel_IOFactory::createWriter($this->document, 'Excel5');
        $objWriter->save($filename);
        return $this;
    }

    /**
     * Название вкладки
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title) {
        $this->sheet->setTitle($title);
        return $this;
    }

    /**
     * Выбрать активный лист
     * @param int $index
     * @return $this
     * @throws \PHPExcel_Exception
     */
    public function setActiveSheet(int $index) {
        $this->sheet = $this->document->setActiveSheetIndex($index);
        return $this;
    }

    /**
     * Задать цвет
     * @param $column
     * @param $line
     * @param $colorRGB
     * @return $this
     */
    public function style($column, $line, $colorRGB) {
        $this->sheet->getStyleByColumnAndRow($column, $line)
            ->getFill()
            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB($colorRGB); //cf1d1d
        return $this;
    }

    /**
     * Установить размер шрифта
     * @param string $cell
     * @param int $size
     * @return $this
     */
    public function setFontSize(int $size, string $cell = null) {
        $c = (empty($cell)) ? $this->cell : $cell;
        $this->sheet->getStyle($c)->getFont()->setSize($size);
        return $this;
    }

    /**
     * Рамка
     * @param string $type
     * @param string|null $range
     * @return $this
     */
    public function setBorder(string $type = 'THIN', string $range = null) {
        switch (strtoupper($type)) {
            case 'THICK':
                $borderStyle = \PHPExcel_Style_Border::BORDER_THICK; //BORDER_THICK - утолщенная
                break;
            case 'THIN':
                $borderStyle = \PHPExcel_Style_Border::BORDER_THIN; //BORDER_THIN - тонкая
                break;
            default:
                $borderStyle = \PHPExcel_Style_Border::BORDER_THIN;
        }

        $border_style= array('borders' => array('allborders' => array('style' =>
            $borderStyle,'color' => array('argb' => '000000'),)));

        $r = (empty($range)) ? $this->range : $range;

        $this->sheet->getStyle($r)->applyFromArray($border_style);

        return $this;
    }

    /**
     * автоматическая ширина
     * @param $column
     */
    public function setAutoSize($column) {
        if (is_string($column)) {
            $this->sheet->getColumnDimension($column)->setAutoSize(true);
        }
        if (is_array($column)) {
            foreach ($column as $c) {
                $this->sheet->getColumnDimension($c)->setAutoSize(true);
            }
        }
    }

    public function setBackground(string $cell) {
        $this->sheet->getStyle($cell)->getFill()
            ->getStartColor()->setRGB('EEEEEE');
        return $this;
    }

    /**
     * Записать данные
     * @param int $column
     * @param int $line
     * @param string $title
     * @return $this
     */
    public function writeText(int $column, int $line, string $title) {
//        $columnPosition = 0; // Начальная координата x
//        $startLine = 1;      // Начальная координата y
        $this->sheet->setCellValueByColumnAndRow($column, $line, $title);
        return $this;
    }

    /**
     * Записать данные
     * @param string $cell
     * @param string $title
     * @return $this
     */
    public function writeTextCell(string $cell, string $title) {
        $this->cell = $cell;
        $this->sheet->setCellValue($cell, $title);
        return $this;
    }

    /**
     * Выравнивание
     * @param $column
     * @param $line
     * @param string $align
     * @return $this
     */
    public function align(int $column, int $line, string $align) {
        $alignment ='';
        switch ($align) {
            case 'left':
                $alignment = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
                break;
            case 'right':
                $alignment = \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
                break;
            case 'center':
                $alignment = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                break;
        }
        $this->sheet->getStyleByColumnAndRow($column, $line)->getAlignment()->setHorizontal($alignment);
        return $this;
    }

    /**
     * Выравнивание
     * @param string $cell
     * @param string $align
     * @return $this
     */
    public function alignCell(string $align, string $cell = null) {
        $alignment ='';
        switch ($align) {
            case 'left':
                $alignment = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
                break;
            case 'right':
                $alignment = \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
                break;
            case 'center':
                $alignment = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                break;
        }

        $c = (empty($cell)) ? $this->cell : $cell;

        $this->sheet->getStyle($c)->getAlignment()->setHorizontal($alignment);
        return $this;
    }

    /**
     *  Объединяем ячейки
     * @param int $column1
     * @param int $line1
     * @param int $column2
     * @param int $line2
     * @return $this
     * @throws \PHPExcel_Exception
     */
    public function merge(int $column1, int $line1, int $column2, int $line2) {
        $this->sheet->mergeCellsByColumnAndRow($column1, $line1, $column2, $line2);
        return $this;
    }

    /**
     * Объединяем ячейки
     * @param string $range
     * @return $this
     */
    public function mergeCells(string $range) {
        $this->range = $range;
        $this->sheet->mergeCells($range);
        return $this;
    }

    /**
     * Сохранить документ
     * @param string $filename
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function save(string $filename) {
        $objWriter = \PHPExcel_IOFactory::createWriter($this->document, 'Excel5');
        $objWriter->save($filename);
    }

    public function test () {
        // Перекидываем указатель на следующую строку
        // $startLine++;
        // foreach ($test as $item) {
        // $columnPosition = 0;
        //      for ($i=0; $i < count($item); $i++) {
        //      $sheet->setCellValueByColumnAndRow($columnPosition, $startLine, $item[$i]);
        //      $columnPosition++;
        // }
        // $startLine++;
        //}
    }
}
