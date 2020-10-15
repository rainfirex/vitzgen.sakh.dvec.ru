<?php
namespace App\Modules\connector;


class OracleConnector extends DbConnector
{
    public function __construct(?string $serviceName, string $host, ?string $port, ?string $sid, string $db, string $user, string $password)
    {
        parent::__construct($serviceName, $host, $port, $sid, $db, $user, $password);
    }
}
