<?php namespace App\Modules {

    /**
     * Время выполнение скрипта
     * Class StopwatchScript
     * @package App\Modules
     */
    class StopwatchScript
    {
        private static $start;

        /**
         * Отментка начала выполнение
         * @return float
         */
        public static function start(): float {
            self::$start = microtime(true);
            return self::$start;
        }

        public static function end(): float {
            return round(microtime(true) - self::$start, 4);
        }

        /**
         * Текстовое представление результата
         * @param string $text
         * @return string
         */
        public static function toString(?string $text = null): string {
            if (!empty(self::$start))
                return 'Время выполнение заняло: '.self::end().' секунд '.$text;
            else
                return 'Время выполнение не известно, необходимо установить начальное значение';
        }
    }
}
