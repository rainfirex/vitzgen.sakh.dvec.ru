<?php namespace App\Modules\db {

    use App\Modules\connector\DbConnector;

    abstract class DB
    {
        // MySQL
        // $conn = new \PDO("mysql:host=localhost;dbname=reports", 'reports', 'reports');

        /**Экземпляр*/
        protected static $INSTANCE;

        protected static $SERVICE_NAME;
        protected static $HOST;
        protected static $PORT;
        protected static $SID;
        protected static $DB;
        protected static $USER;
        protected static $PASS;

        protected $errorMessage;
        protected $isError = false;

        /**Запрос */
        protected static $QUERY;

        /**
         * Выборка
         * @param string $query
         * @return array
         */
        abstract function select(string $query): array;

        /**
         * Форматирование даты в соответствие с требованиями системы
         * @param string $date
         * @return string
         */
        abstract static function dateFormatter(string $date): string;

        protected function __construct(?string $serviceName, string $host, ?string $port, ?string $sid, string $db, string $user, string $password)
        {
            if (!empty($serviceName))
                self::$SERVICE_NAME = $serviceName;
            if (!empty($port))
                self::$PORT = $port;
            if (!empty($sid))
                self::$SID = $sid;

            self::$HOST = $host;
            self::$DB = $db;
            self::$USER = $user;
            self::$PASS = $password;
        }

        /**
         * Создать экземпляр
         * @param string $serviceName
         * @param string $host
         * @param string $port
         * @param string $sid
         * @param string $db
         * @param string $user
         * @param string $password
         * @return MicrosoftDB
         */
        final public static function init(?string $serviceName, string $host, ?string $port, ?string $sid, string $db, string $user, string $password) {
            $calledClassName = get_called_class();
            if (! isset (self::$INSTANCE[$calledClassName])) {
                self::$INSTANCE[$calledClassName] = new $calledClassName($serviceName, $host, $port,$sid, $db, $user, $password);
            }
            return self::$INSTANCE[$calledClassName];
        }

        /**
         * Создать экземпляр
         * @param DbConnector $con
         * @return MicrosoftDB
         */
        final public static function initOnConnector(DbConnector $con) {
            $calledClassName = get_called_class();
            if (! isset (self::$INSTANCE[$calledClassName])) {
                self::$INSTANCE[$calledClassName] = new $calledClassName($con->getServiceName(), $con->getHost(), $con->getPort(), $con->getSid(), $con->getDb(), $con->getUser(), $con->getPassword());
            }
            return self::$INSTANCE[$calledClassName];
        }

        public function isError(): string {
            return $this->isError;
        }

        public function errorMessage() : string {
            return $this->errorMessage;
        }

        public static function dumpQuery(): string {
            return self::$QUERY;
        }

        final private function __clone() {}
    }
}
