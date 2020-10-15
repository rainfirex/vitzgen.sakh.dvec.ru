<?php namespace App\Modules\db {

    use PDO;

    /**
     * Class MicrosoftDB
     * @package App\Modules\db
     */
    final class MicrosoftDB extends DB
    {
        function select(string $query) : array
        {
            self::$QUERY = $query;
            if (empty(self::$PORT))
                $dns = 'sqlsrv:Server='.self::$HOST.';Database='.self::$DB;
            else
                $dns = 'sqlsrv:Server='.self::$HOST.':'.self::$PORT.';Database='.self::$DB;;

            try {
                $pdo = new PDO($dns, self::$USER , self::$PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            catch (\PDOException $exception) {
                $this->isError= true;
                $this->errorMessage = $exception->getMessage();
            }
            return [];
        }

        /**
         * Форматирование даты в соответствие с требованиями системы
         * @param string $date
         * @return string
         */
        static function dateFormatter(string $date): string
        {
            // TODO: Implement dateFormatter() method.
            $date = preg_replace('/[-]+/', '.', $date); //С 2019-12-20 в 2019.12.20
            $date = preg_replace('/[.][\d]+$/', '', $date); //2019.12
            return str_replace( '.','', $date);
        }
    }
}
