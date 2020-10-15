<?php

namespace App\Modules\db;

use PDO;

final class OracleDB extends DB
{
    /**
     * Вернуть TNS строку
     * @return string
     */
    private static function getTns() {
        return "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = ".self::$HOST.")(PORT = ".self::$PORT.")) (CONNECT_DATA = (SERVICE_NAME = ".self::$SERVICE_NAME." ) (SID = ".self::$SID.")))";
    }

    /**
     * Выполнить запрос
     * @param string $query
     * @return array
     */
    public function select(string $query) : array {
        self::$QUERY = $query;
        try {
            $pdo = new PDO("oci:dbname=" . self::getTns().';charset=utf8', self::$USER, self::$PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $ex) {
            $this->isError= true;
            $this->errorMessage = $ex->getMessage();
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
        return preg_replace('/[.][\d]+$/', '', $date); //2019.12
    }
}
